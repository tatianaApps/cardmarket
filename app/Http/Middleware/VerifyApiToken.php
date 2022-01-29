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
    public function handle(Request $req, Closure $next)
    {
        if(isset($req->api_token)){
            //Buscar al usuario
            $apitoken = $req->api_token; 
            
            //Pasar usuario
            $user = User::where('api_token', $apitoken)->first();
            if($user){
                $response['msg'] = "Token correcto";
                $req->user = $user;
                return $next($req);
            }else{
                $response['msg'] = "Token incorrecto";
            }
        }else{
            $response['msg'] = "Token no introducido";
        }
        
        return response()->json($response);
    }
}
