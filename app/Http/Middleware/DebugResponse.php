<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DebugResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Si la respuesta es un array, convertirla a JSON
        if (is_array($response)) {
            return response()->json($response);
        }
        
        return $response;
    }
}
