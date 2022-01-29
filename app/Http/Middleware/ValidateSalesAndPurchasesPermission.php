<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateSalesAndPurchasesPermission
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
        $response = ['status' => 1, "msg" => ""];

        //Comprobar los permisos para las ventas y compras
        if($req->user->rol =='particular' || $req->user->rol =='professional'){ //para ver si administrador y te deje pasar al controller
            return $next($req);
        }else{
             $response['msg'] = "No tienes permisos para realizar esta función";
            
        }
        return response()->json($response);
    }
}
