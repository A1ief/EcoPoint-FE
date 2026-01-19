<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {


        // ❌ belum login
        if (!session()->has('token')) {
            return redirect()
                ->route('showlogin')
                ->with('error', 'Silakan login terlebih dahulu');
        }

        $role = session('role');

        // ❌ role tidak diizinkan
        if (!$role || !in_array($role, $roles)) {
            return redirect()
                ->back()
                ->with('error', 'Anda tidak memiliki akses');
            // atau: abort(403);
            dd(session('role'), $roles);
        }

        return $next($request);
    }
}
