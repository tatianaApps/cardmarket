<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        //Comprobar los permisos    
        if($req->user->rol =='administrator' || $req->user->rol =='human_resources'){
            return $next($req);
            $response['msg'] = "Perfil validado";
        }else{
             $response['msg'] = "No tienes permisos para realizar esta función";
            
        }
        return response()->json($response);
    }
}
