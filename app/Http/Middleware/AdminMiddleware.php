<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');

        if (!$admin || !$admin->is_admin) {
            abort(403, 'Kamu tidak punya akses ke halaman admin.');
        }

        return $next($request);
    }
}