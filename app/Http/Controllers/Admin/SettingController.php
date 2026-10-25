<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Supabase;
use App\Services\SupabaseAdmin;
use App\Services\Whatsapp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

/** Pengaturan: nomor WhatsApp, kalimat sapaan WhatsApp, email penerima notifikasi. */
class SettingController extends Controller
{
    public function __construct(private SupabaseAdmin $db) {}

    public function edit(Supabase $sb)
    {
        $company = $this->db->first('company_info') ?? [];
        $set = $sb->settings();

        return view('admin.settings', [
            'whatsapp'     => $company['whatsapp'] ?? '6289644113257',
            'notify_email' => $set['notify_email'] ?? ($company['email'] ?? ''),
            'wa_greeting'  => $set['wa_greeting'] ?? 'saya ingin bertanya mengenai layanan konsultansi.',
            'mailer'       => config('mail.default'),
            'mailHost'     => config('mail.mailers.' . config('mail.default') . '.host'),
            'mailUser'     => config('mail.mailers.' . config('mail.default') . '.username'),
        ]);
    }

    public function update(Request $r)
    {
        $d = $r->validate([
            'whatsapp'     => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{8,}$/'],
            'notify_email' => 'required|email|max:150',
            'wa_greeting'  => 'nullable|string|max:300',
        ], [
            'whatsapp.regex'    => 'Nomor WhatsApp hanya boleh berisi angka, contoh 089644113257.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $number = Whatsapp::number($d['whatsapp']);

        $row = $this->db->first('company_info');
        $res = $row
            ? $this->db->update('company_info', ['id' => 'eq.' . $row['id']], ['whatsapp' => $number])
            : $this->db->insert('company_info', ['name' => 'PT. Purna Wahana Lestari Konsultan', 'whatsapp' => $number]);
        if (! $res['ok']) {
            return back()->withInput()->withErrors(['db' => 'Gagal menyimpan nomor WhatsApp: ' . $res['error']]);
        }

        $set = $this->db->upsert('site_settings', [
            ['key' => 'notify_email', 'value' => $d['notify_email']],
            ['key' => 'wa_greeting', 'value' => trim((string) ($d['wa_greeting'] ?? ''))],
        ], 'key');
        if (! $set['ok']) {
            return back()->withInput()->withErrors(['db' => 'Gagal menyimpan pengaturan (sudah menjalankan SQL 06?): ' . $set['error']]);
        }

        $this->db->flushPublic();
        $this->db->audit('update', 'site_settings', null, ['whatsapp' => $number, 'notify_email' => $d['notify_email']]);

        return back()->with('ok', 'Pengaturan disimpan. Nomor WhatsApp aktif: ' . $number);
    }

    /** Kirim email percobaan untuk memastikan Gmail SMTP sudah benar. */
    public function testMail(Request $r, Supabase $sb)
    {
        $to = $sb->setting('notify_email') ?: ($sb->company()['email'] ?? null);
        if (! $to) {
            return back()->withErrors(['db' => 'Isi dan simpan email penerima notifikasi dulu.']);
        }

        try {
            Mail::raw("Ini email percobaan dari panel admin PWL Konsultan.\nJika Anda menerima email ini, pengiriman notifikasi sudah berfungsi.", function ($m) use ($to) {
                $m->to($to)->subject('Email percobaan - PWL Konsultan');
            });
        } catch (Throwable $e) {
            return back()->withErrors(['db' => 'Email gagal dikirim: ' . $e->getMessage()]);
        }

        return back()->with('ok', 'Email percobaan terkirim ke ' . $to . '. Periksa kotak masuk (dan folder Spam).');
    }
}
