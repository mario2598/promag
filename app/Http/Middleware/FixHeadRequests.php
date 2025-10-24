<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FixHeadRequests
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->method() === 'HEAD') {
            $request->setMethod('GET');
        }
        
        return $next($request);
    }
}
