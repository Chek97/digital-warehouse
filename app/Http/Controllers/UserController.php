<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostUserRequest;
use App\Http\Requests\UpdateUserRequest;
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
            if($request->hasFile('photo')){

            }
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

    public function getUser($id){
        try {
            $user = User::findOrFail($id);
            //? Al converitr un objeto eloquent a un array o en formato JSON, los campos hidden se aplican

            return response()->json([
                "status" => true,
                "message" => "Datos del usuario: " . $user->username,
                "user" => $user
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "Error al obtener el usuario",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function updateUser(UpdateUserRequest $request, $id){
        try {
            $data = $request->validated();
            $user = User::findOrFail($id);
            // TODO: aprender sobre storage en laravel y cuando la data es opcional
            // ? Si vas a utilizar form-data, toda la data debe estar en ese formato
            
            return response()->json([
                "status" => true,
                "message" => "Usuario actualizado con exito",
                "user" => $user
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "Error al actualizar el usuario",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function deleteUser($id){
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                "status" => true,
                "message" => "Usuario eliminado con exito",
            ], 200);            
        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "Error al borrar el usuario",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function getElements(){
        dd("ELementos");
    }
}
