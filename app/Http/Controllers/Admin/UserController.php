<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupabaseAdmin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private SupabaseAdmin $db) {}

    /** management boleh mengelola semua role; super_admin hanya akun role admin. */
    private function roles(Request $r): array
    {
        return $r->attributes->get('admin')['role'] === 'management'
            ? ['management', 'super_admin', 'admin'] : ['admin'];
    }

    private function target(Request $r, string $id): array
    {
        $u = $this->db->first('profiles', ['id' => 'eq.' . $id]);
        abort_unless($u && in_array($u['role'], $this->roles($r), true), 403, 'Anda tidak boleh mengelola akun ini.');
        abort_if($u['id'] === $r->attributes->get('admin')['id'], 403, 'Akun sendiri tidak bisa diubah di sini.');

        return $u;
    }

    public function index(Request $r)
    {
        return view('admin.users', [
            'roles' => $this->roles($r),
            'users' => $this->db->get('profiles', ['select' => 'id,full_name,email,role,is_active,last_login_at', 'order' => 'created_at.asc']),
        ]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'full_name' => 'required|max:100', 'email' => 'required|email|max:150',
            'password'  => 'required|min:8|max:100', 'role' => ['required', Rule::in($this->roles($r))],
        ]);

        $res = $this->db->createAccount($d['email'], $d['full_name'], $d['password'], $d['role']);
        if (! $res['ok']) {
            return back()->withInput()->withErrors(['db' => 'Gagal membuat akun: ' . $res['error']]);
        }

        $this->db->audit('create', 'profiles', $res['id'], ['email' => strtolower($d['email']), 'role' => $d['role']]);

        return back()->with('ok', 'Akun dibuat.');
    }

    public function update(Request $r, string $id)
    {
        $this->target($r, $id);
        $d = $r->validate([
            'full_name' => 'required|max:100', 'role' => ['required', Rule::in($this->roles($r))],
            'password'  => 'nullable|min:8|max:100',
        ]);

        $this->db->update('profiles', ['id' => 'eq.' . $id], [
            'full_name' => $d['full_name'], 'role' => $d['role'], 'is_active' => $r->boolean('is_active'),
        ]);

        if (! empty($d['password'])) {
            $pw = $this->db->setPassword($id, $d['password']);
            if (! $pw['ok']) {
                return back()->withErrors(['db' => 'Password gagal diganti: ' . $pw['error']]);
            }
        }

        $this->db->audit('update', 'profiles', $id, ['role' => $d['role'], 'is_active' => $r->boolean('is_active'), 'password_changed' => ! empty($d['password'])]);

        return back()->with('ok', 'Akun diperbarui.');
    }

    public function destroy(Request $r, string $id)
    {
        $this->target($r, $id);
        $this->db->deleteAccount($id);
        $this->db->audit('delete', 'profiles', $id);

        return back()->with('ok', 'Akun dihapus.');
    }
}
