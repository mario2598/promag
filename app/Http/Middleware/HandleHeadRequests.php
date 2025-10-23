<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleHeadRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Si el método es HEAD, cambiarlo a GET
        if ($request->method() === 'HEAD') {
            $request->setMethod('GET');
        }
        
        return $next($request);
    }
}
