<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictKepalaRuangan
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role_key === 'kepala_ruangan') {
            if (
                $request->is('/') ||
                $request->is('home') ||
                $request->is('helpdesk') ||
                $request->is('laporan') ||
                $request->is('auth/logout')
            ) {
                return $next($request);
            }

            abort(403);
        }

        return $next($request);
    }
}
