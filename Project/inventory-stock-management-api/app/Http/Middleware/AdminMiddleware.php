<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        if ($request->user()->role !== 'administrator') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Administrator only.'
            ], 403);
        }

        return $next($request);
    }
}