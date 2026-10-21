<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            // Tolak akses jika bukan admin
            abort(403, 'Akses Ditolak. Halaman ini khusus Administrator WOYuk!');
        }

        return $next($request);
    }
}