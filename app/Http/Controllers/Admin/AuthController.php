<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupabaseAdmin;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private SupabaseAdmin $db) {}

    public function form()
    {
        return session('admin_id') ? redirect()->route('admin.dashboard') : view('admin.login');
    }

    public function login(Request $r)
    {
        $r->validate(['email' => 'required|email', 'password' => 'required']);

        $fail = fn () => back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');

        $auth = $this->db->signIn(strtolower($r->email), $r->password);
        if (! $auth || empty($auth['id'])) {
            return $fail();
        }

        $profile = $this->db->first('profiles', ['id' => 'eq.' . $auth['id']]);
        if (! $profile || ! ($profile['is_active'] ?? false)) {
            return $fail();
        }

        $r->session()->regenerate();
        session(['admin_id' => $profile['id']]);
        $r->attributes->set('admin', $profile);

        $this->db->update('profiles', ['id' => 'eq.' . $profile['id']], ['last_login_at' => now()->toIso8601String()]);
        $this->db->audit('login', 'profiles', $profile['id']);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $r)
    {
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
