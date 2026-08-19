<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerOrAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !in_array(auth()->user()->role?->slug, ['admin', 'manager'])) {
            abort(403, 'Unauthorized. Manager or admin access required.');
        }

        return $next($request);
    }
}
