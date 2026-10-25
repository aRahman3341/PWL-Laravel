<?php

namespace App\Http\Middleware;

use App\Services\SupabaseAdmin;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

/** Memastikan yang mengakses panel admin adalah pegawai yang sudah login dan aktif. */
class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $id = session('admin_id');
        $user = $id ? app(SupabaseAdmin::class)->first('profiles', ['id' => 'eq.' . $id]) : null;

        if (! $user || ! ($user['is_active'] ?? false)) {
            session()->forget('admin_id');

            return redirect()->route('admin.login');
        }

        $request->attributes->set('admin', $user);
        view()->share('admin', $user);
        // Jumlah permintaan download yang menunggu persetujuan (lencana di menu)
        view()->share('pendingDownloads', Cache::remember('admin_pending_downloads', 30, fn () => count(
            app(SupabaseAdmin::class)->get('download_requests', ['select' => 'id', 'status' => 'eq.pending'])
        )));
        // Jumlah pesan baru untuk lencana di menu (disimpan 60 detik agar cepat)
        view()->share('newMessages', Cache::remember('admin_new_messages', 60, fn () => count(
            app(SupabaseAdmin::class)->get('messages', ['select' => 'id', 'status' => 'eq.baru'])
        )));

        return $next($request);
    }
}
