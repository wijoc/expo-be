<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JWTAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $roles = 'su|admin|user')
    {
        try {
            $payload = auth()->guard('api')->payload();

            if ($payload->get('type') === 'access_token') {
                $loggedInUser = auth()->guard('api')->userOrFail();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Token is Invalid'
                ], 401);
            }
        }
        catch (JWTException $error) {
            if ($error instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'success' => false,
                    'message' => 'Token is Invalid'
                ], 401);
            }else if ($error instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([
                    'success' => false,
                    'message' => 'Token is Expired'
                ], 401);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization Token not found'
                ], 401);
            }
        }

        if ($loggedInUser && in_array($loggedInUser->role, explode('|', $roles))) {
            return $next($request);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'You are unauthorized to access this resource'
            ], 401);
        }
    }
}
