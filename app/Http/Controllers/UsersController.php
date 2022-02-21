<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersController extends Controller
{
    public function registerUser(Request $req)
    {

        $response = ["status" => 1, "msg" => ""];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'username' => 'required|max:50|unique:App\Models\User,username',
            'email' => 'required|email|unique:App\Models\User,email|max:50',
            'password' => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/',
            'rol' => 'required|in:particular,professional,administrator',
        ]);

        if ($validator->fails()) {
            //Preparar la respuesta 
            $response['status'] = 0;
            $response['msg'] = $validator->errors();
        } else {
            $data = $req->getContent();
            $data = json_decode($data);

            try {
                //Generar usuario
                $user = new User();

                $user->username = $data->username;
                $user->email = $data->email;
                $user->password = Hash::make($data->password);
                $user->rol = $data->rol;

                $user->save();
                $response['msg'] = "Usuario guardado con id " . $user->id;
            } catch (\Exception $e) {
                $response['status'] = 0;
                $response['msg'] = "Se ha producido un error: " . $e->getMessage();
            }
        }
        return response()->json($response);
    }

    public function login(Request $req)
    {

        $response = ["status" => 1, "msg" => ""];
        $data = $req->getContent();
        $data = json_decode($data);

        //Buscar el nombre de usuario
        $username = $data->username;

        //Encontrar al usuario con el nombre de usuario
        $user = User::where('username', '=', $data->username)->first();
        
        //Comprobar si existe el usuario
        if ($user) {
            if (Hash::check($data->password, $user->password)) { //Comprobar la contraseña
                //Si todo correcto generar el api token
                do {
                    $token = Hash::make($user->id . now());
                } while (User::where('api_token', $token)->first()); //Encontrar a un usuario con ese apitoken
                if ($token) {
                    $user->api_token = $token;
                    $user->save();
                    $response['msg'] = "Login correcto. Api token generado: " . $user->api_token;
                } else {
                    $response['status'] = 0;
                    $response['msg'] = "Token no generado";
                }
            } else {
                //Login mal
                $response['status'] = 0;
                $response['msg'] = "La contraseña no es correcta";
            }
        } else {
            $response['status'] = 0;
            $response['msg'] = "Usuario no encontrado";
        }
        return response()->json($response);
    }

    public function recoverPassword(Request $req)
    {

        //Obtener el email y validarlo 
        $response = ["status" => 1, "msg" => ""];
        $data = $req->getContent();
        $data = json_decode($data);

        //Buscar el email
        $email = $req->email;

        //Encontrar al usuario con ese email
        $user = User::where('email', '=', $data->email)->first();

        //Comprobar si existe el usuario
        if ($user) {

            $user->api_token = null;

            //Generar nueva contraseña aleatoriamente (función para generar strings aleatorios)
            $password = "aAbBcCdDeEfFgGhHiIjJkKlLmMnNñÑoOpPqQrRsStTuUvVwWxXyYzZ0123456789";
            $passwordCharCount = strlen($password);
            $passwordLength = 8;
            $newPassword = "";

            for ($i = 0; $i < $passwordLength; $i++) {
                $newPassword .= $password[rand(0, $passwordCharCount - 1)];
            }

            //Guardamos al usuario con la nueva contraseña cifrada
            $user->password = Hash::make($newPassword);
            $user->save();
            $response['msg'] = "Nueva contraseña generada: " . $newPassword;
        } else {
            $response['status'] = 0;
            $response['msg'] = "Usuario no encontrado";
        }

        return response()->json($response);
    }
}
