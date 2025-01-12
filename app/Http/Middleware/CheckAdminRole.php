<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->roles == 'admin') {
            return $next($request);
        }

        return response()->json(['message' => 'Você não tem permissão para acessar este recurso.'], 403);
    }
}

