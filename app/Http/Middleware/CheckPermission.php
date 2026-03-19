<?php

namespace App\Http\Middleware;

use App\Support\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $menu, string $action = 'read'): Response
    {
        $user = $request->user();
        if (!Permission::can($user, $menu, $action)) {
            abort(403);
        }

        return $next($request);
    }
}
