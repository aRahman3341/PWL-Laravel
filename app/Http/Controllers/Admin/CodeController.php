<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupabaseAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

class CodeController extends Controller
{
    public function __construct(private SupabaseAdmin $db) {}

    public function index()
    {
        $docs = $this->db->get('confidential_files', ['select' => 'id,title', 'order' => 'created_at.desc']);

        return view('admin.codes', [
            'docs' => $docs,
            'docTitles' => collect($docs)->pluck('title', 'id'),
            'rows' => array_map(function ($r) {
                try {
                    $r['code'] = ! empty($r['code_enc']) ? Crypt::decryptString($r['code_enc']) : null;
                } catch (\Throwable $e) {
                    $r['code'] = null;
                }
                unset($r['code_enc']);

                return $r;
            }, $this->db->get('access_codes', [
                'select' => 'id,label,file_id,code_enc,expires_at,max_uses,used_count,is_active', 'order' => 'created_at.desc',
            ])),
        ]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'label' => 'required|max:150', 'expires_at' => 'nullable|date|after:today', 'max_uses' => 'nullable|integer|min:1',
            'file_id' => 'nullable|uuid',
        ]);

        // Tanpa huruf/angka yang mudah tertukar (O/0, I/1)
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $part = function () use ($chars) {
            $s = '';
            for ($i = 0; $i < 4; $i++) {
                $s .= $chars[random_int(0, strlen($chars) - 1)];
            }

            return $s;
        };
        $code = $part() . '-' . $part() . '-' . $part();

        $res = $this->db->insert('access_codes', [
            'label'      => $d['label'],
            'code_hash'  => hash('sha256', $code),
            'code_enc'   => Crypt::encryptString($code),
            'expires_at' => ! empty($d['expires_at']) ? Carbon::parse($d['expires_at'])->endOfDay()->toIso8601String() : null,
            'max_uses'   => $d['max_uses'] ?? null,
            'file_id'    => $d['file_id'] ?? null,
            'created_by' => $r->attributes->get('admin')['id'],
        ]);

        if (! $res['ok']) {
            return back()->withErrors(['db' => 'Gagal membuat kode: ' . $res['error']]);
        }

        $this->db->audit('create', 'access_codes', $res['data'][0]['id'] ?? null, ['label' => $d['label']]);

        return back()->with('new_code', $code);
    }

    public function toggle(Request $r, string $id)
    {
        $this->db->update('access_codes', ['id' => 'eq.' . $id], ['is_active' => $r->boolean('is_active')]);
        $this->db->audit('update', 'access_codes', $id, ['is_active' => $r->boolean('is_active')]);

        return back()->with('ok', 'Status kode diubah.');
    }

    public function destroy(string $id)
    {
        $this->db->delete('access_codes', ['id' => 'eq.' . $id]);
        $this->db->audit('delete', 'access_codes', $id);

        return back()->with('ok', 'Kode dihapus.');
    }
}