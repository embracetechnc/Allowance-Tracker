<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ChildMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->role !== 'child') {
            return response()->json(['message' => 'Unauthorized. Child access required.'], 403);
        }

        return $next($request);
    }
}
