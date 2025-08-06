<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JWTMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Tenta autenticar com JWT
            $user = JWTAuth::parseToken()->authenticate();
            
            if ($user) {
                auth('api')->setUser($user);
                return $next($request);
            }
        } catch (JWTException $e) {
            // Se falhar, tenta autenticação padrão
            if(auth('api')->check()) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'unauthenticated'], 401);
    }
}
