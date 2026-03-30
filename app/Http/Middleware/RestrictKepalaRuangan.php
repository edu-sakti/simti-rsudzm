<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictKepalaRuangan
{
    public function handle(Request $request, Closure $next): Response
    {
        // Nonaktifkan pembatasan kepala ruangan: semua user login boleh akses semua route.
        return $next($request);
    }
}
