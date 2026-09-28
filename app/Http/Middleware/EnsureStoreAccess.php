<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureStoreAccess
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->canManageStore(), 403, 'Área exclusiva para lojistas.');

        return $next($request);
    }
}
