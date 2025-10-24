<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConvertHeadToGet
{
    public function handle(Request $request, Closure $next)
    {
        // Si el método es HEAD, cambiarlo a GET
        if ($request->method() === 'HEAD') {
            $request->setMethod('GET');
        }
        
        return $next($request);
    }
}
