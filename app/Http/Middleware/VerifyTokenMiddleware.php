<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerifyTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            //Obteniendo el token de la peticion
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    "status" => false,
                    "message" => "No hay token en la peticion"
                ], 401);
            }

            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json([
                    "status" => false,
                    "message" => "El token no es valido o expiro"
                ], 401);
            }

            $request->auth = $user;

        } catch (JWTException $e) {
            return response()->json([
                "status" => false,
                "message" => "El token no es valido"
            ], 401);
        }
        return $next($request);
    }
}
