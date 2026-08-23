<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust Cloudflare proxies in front of production so scheme/host
        // detection (and secure-cookie logic) works behind TLS termination.
        $middleware->trustProxies(at: '*');

        // Baseline security headers on every response (nosniff, frame
        // protection, referrer policy, and HSTS when serving HTTPS).
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'admin' => \App\Http\Middleware\Admin::class,
            'manager_or_admin' => \App\Http\Middleware\ManagerOrAdmin::class,
            'admin_manager_or_staff' => \App\Http\Middleware\AdminManagerOrStaff::class,
        ]);

        // Block the browser from caching authenticated pages so that
        // Back/Forward navigation can never reveal previous sessions.
        $middleware->web(append: [
            \App\Http\Middleware\PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
