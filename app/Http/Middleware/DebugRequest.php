<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugRequest
{
    public function handle(Request $request, Closure $next)
    {
        // Log del método HTTP y URL
        Log::info('Request Method: ' . $request->method());
        Log::info('Request URL: ' . $request->fullUrl());
        Log::info('Request Headers: ' . json_encode($request->headers->all()));
        
        return $next($request);
    }
}
