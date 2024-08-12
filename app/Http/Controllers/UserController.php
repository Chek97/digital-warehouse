<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    // Registrar acomodadores, o usuarios solicitantes
    public function registerUser(PostUserRequest $request)
    {
        try {
            $newUser = new User();

            $newUser->name = $request->name;
            $newUser->last_name = $request->exists('last_name') ? $request->last_name : '';
            $newUser->username = $request->username;
            $newUser->password = Hash::make($request->password);
            //todo: agregar foto proximamente
            //$newUser->photo = $request->photo;
            if ($request->role_id == 1) {
                return response()->json([
                    "status" => false,
                    "message" => "No se puede crear un usuario con este rol en el sistema"
                ], 401);
            }
            $newUser->role_id = $request->role_id;

            if ($newUser->save()) {
                return response()->json([
                    "status" => true,
                    "message" => "El usuario fue creado con exito"
                ], 201);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "El usuario no pudo ser creado, intentalo mas tarde"
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "Error al registrar un usuario",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    // Realizamos el login y creamos el jwt
    public function loginUser(Request $request){
        try {
            $validation = Validator::make($request->all(), [
                "username" => "required|string",
                "password" => "required|string"
            ]);

            if($validation->fails()){
                return response()->json(['errors' => $validation->errors()], 422);
            }

            $credentials = $request->only('username', 'password');

            if(!$token = JWTAuth::attempt($credentials)){
                return response()->json([
                    "status" => false,
                    "message" => "Error, el usuario y/o contraseña son incorrectas"
                ], 401);
            }

            return response()->json([
                "status" => true,
                "message" => "Inicio de sesion exitoso",
                "token" => [
                    "jwt" => $token,
                    "expires_in" => now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
                    "user" => auth()->user()
                ],
            ], 200);

        } catch (Exception $e) {
            //todo: configurar un helper o servicio para la respuesta http
            return response()->json([
                "status" => false,
                "message" => "Error al registrar un usuario",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
