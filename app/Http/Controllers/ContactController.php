<?php

namespace App\Http\Controllers;

use App\Services\Supabase;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request, Supabase $sb)
    {
        // Honeypot: kolom tersembunyi yang hanya diisi bot
        if ($request->filled('website')) {
            return redirect()->route('kontak')->with('kirim', 'ok');
        }

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'body'  => ['required', 'string', 'max:3000'],
        ]);

        $r = $sb->insertMessage([
            'name'  => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'body'  => $data['body'],
        ]);

        return redirect()->route('kontak')->with('kirim', $r['ok'] ? 'ok' : 'gagal');
    }
}
