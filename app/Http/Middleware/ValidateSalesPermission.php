<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValidateSalesPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $req, Closure $next)
    {
        //Comprobar los permisos
        if ($req->user->rol == 'professional') { //para ver si es particular o profesional y te deje pasar al controller
            Log::info("Validación middleware perfil profesional completado");
            return $next($req);
        } else if ($req->user->rol == 'particular') {
            Log::info("Validación middleware perfil particular completado");
            return $next($req);
        } else {
            Log::error("Validación del middleware fallida, no tienes permisos");
            $response['msg'] = "No tienes permisos para realizar esta función";
        }
        return response()->json($response);
    }
}
