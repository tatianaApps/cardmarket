<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ValidateUserPermission
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
        if ($req->user->rol == 'administrator') { //para ver si administrador y te deje pasar al controller
            return $next($req);
        } else {
            Log::info("Validación del middleware completada");
            $response['msg'] = "No tienes permisos para realizar esta función";
        }
        return response()->json($response);
    }
}
