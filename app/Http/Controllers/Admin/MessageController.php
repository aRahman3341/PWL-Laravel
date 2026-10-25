<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupabaseAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    public const STATUS = ['baru' => 'Baru', 'dibaca' => 'Dibaca', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'spam' => 'Spam'];

    public function __construct(private SupabaseAdmin $db) {}

    public function index()
    {
        return view('admin.messages', [
            'rows'   => $this->db->get('messages', ['order' => 'created_at.desc', 'limit' => 200]),
            'status' => self::STATUS,
        ]);
    }

    public function update(Request $r, string $id)
    {
        $d = $r->validate([
            'status'     => ['required', Rule::in(array_keys(self::STATUS))],
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $this->db->update('messages', ['id' => 'eq.' . $id], [
            'status'     => $d['status'],
            'admin_note' => $d['admin_note'] ?? null,
            'handled_by' => $r->attributes->get('admin')['id'],
            'handled_at' => now()->toIso8601String(),
        ]);

        Cache::forget('admin_new_messages');

        return back()->with('ok', 'Pesan diperbarui.');
    }
}
