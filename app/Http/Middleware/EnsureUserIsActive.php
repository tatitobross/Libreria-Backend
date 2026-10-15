<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->status !== 'active') {
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'El usuario no está activo.',
            ], 403);
        }

        return $next($request);
    }
}
