<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!session()->has('is_login')) {
            return redirect()->route('login');
        }

        if (!in_array(session('role'), $roles)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
