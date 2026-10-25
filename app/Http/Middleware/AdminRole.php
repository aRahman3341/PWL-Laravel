<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Pemakaian: AdminRole::class . ':super_admin,management' */
class AdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $role = $request->attributes->get('admin')['role'] ?? '';
        abort_unless(in_array($role, $roles, true), 403, 'Anda tidak punya akses ke halaman ini.');

        return $next($request);
    }
}
