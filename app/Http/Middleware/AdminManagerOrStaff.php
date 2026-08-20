<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminManagerOrStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !in_array(auth()->user()->role?->slug, ['admin', 'manager', 'staff'])) {
            abort(403, 'Unauthorized. Admin, manager, or staff access required.');
        }

        return $next($request);
    }
}