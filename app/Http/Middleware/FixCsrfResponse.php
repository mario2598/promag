<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FixCsrfResponse
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
        $response = $next($request);
        
        // Si la respuesta no es válida, crear una respuesta válida
        if (!$response || !is_object($response)) {
            return response('', 200);
        }
        
        return $response;
    }
}
