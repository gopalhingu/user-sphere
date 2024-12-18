<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;

class HandleExpiredJWT
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Attempt to get the user from the JWT token
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            // Token is expired or invalid, return a custom response
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['error' => 'Token has expired. Please login again.'], 401);
            }

            if ($e instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
                return response()->json(['error' => 'Token is invalid. Please login again.'], 400);
            }

            return response()->json(['error' => 'Authorization token not found.'], 401);
        }

        return $next($request);
    }
}
