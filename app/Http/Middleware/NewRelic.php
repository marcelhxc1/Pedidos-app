<?php

namespace App\Http\Middleware;

use Closure;

class NewRelicMiddleware
{
    public function handle($request, Closure $next)
    {
        newrelic_name_transaction($request->route()->getName());
        
        return $next($request);
    }
}
