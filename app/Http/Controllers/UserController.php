<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    // Registrar acomodadores, o usuarios solicitantes
    public function registerUser(PostUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $newUser = new User();

            $newUser->name = $request->name;
            $newUser->last_name = $request->exists('last_name') ? $request->last_name : null;
            $newUser->username = $request->username;
            $newUser->password = Hash::make($request->password);

            if ($request->hasFile('photo')) {

                $photo = $request->file('photo');

                $photoName = uniqid() . '_' . $photo->getClientOriginalName();
                $photoPath = $request->username . '/' . $photoName;

                $photo->storeAs($request->username, $photoName, 'users');

                $newUser->photo = $photoPath;
            }

            if ($request->role_id == 1) {
                DB::rollBack();
                return response()->json([
                    "status" => false,
                    "message" => "No se puede crear un usuario con este rol en el sistema"
                ], 401);
            }

            $newUser->role_id = $request->role_id;

            if ($newUser->save()) {
                DB::commit();
                return response()->json([
                    "status" => true,
                    "message" => "El usuario fue creado con exito"
                ], 201);
            } else {
                DB::rollBack();
                return response()->json([
                    "status" => false,
                    "message" => "El usuario no pudo ser creado, intentalo mas tarde"
                ], 400);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "message" => "Error al registrar un usuario",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    // Realizamos el login y creamos el jwt
    public function loginUser(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                "username" => "required|string",
                "password" => "required|string"
            ]);

            if ($validation->fails()) {
                return response()->json(['errors' => $validation->errors()], 422);
            }

            $credentials = $request->only('username', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
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

    public function getUser($id)
    {
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

    public function updateUser(UpdateUserRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            // * Si vas a utilizar form-data, toda la data debe estar en ese formato y debe ser peticion POST
            DB::beginTransaction();
            if ($request->hasFile('photo')) {

                $photo = $request->file('photo');

                if ($user->photo == null) {

                    $photoName = uniqid() . '_' . $photo->getClientOriginalName();
                    $photoPath = $request->username . '/' . $photoName;

                    $photo->storeAs($request->username, $photoName, 'users');

                    $user->photo = $photoPath;
                } else if ($request->username == $user->username) {
                    $photoCompletePath = Storage::url('users/' . $user->photo);
                    unlink(public_path() . $photoCompletePath);

                    $photoName = uniqid() . '_' . $photo->getClientOriginalName();
                    $photoPath = $user->username . '/' . $photoName;

                    $photo->storeAs($request->username, $photoName, 'users');

                    $user->photo = $photoPath;
                } else {
                    // * cuando uses un disco diferente a public llamalo para cualquier operacion
                    if (Storage::disk('users')->exists($user->photo)) {
                        $userDirectory = dirname($user->photo);
                        Storage::disk('users')->delete($user->photo);

                        if (Storage::disk('users')->exists($userDirectory)) {
                            Storage::disk('users')->deleteDirectory($userDirectory);
                        }
                    }

                    $photoName = uniqid() . '_' . $photo->getClientOriginalName();
                    $photoPath = $request->username . '/' . $photoName;

                    $photo->storeAs($request->username, $photoName, 'users');

                    $user->photo = $photoPath;
                }
            }

            $user->name = $request->name;
            $user->last_name = $request->exists('last_name') ? $request->last_name : null;
            $user->username = $request->username;

            $user->save();

            DB::commit();

            return response()->json([
                "status" => true,
                "message" => "Usuario actualizado con exito",
                "user" => $user
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "message" => "Error al actualizar el usuario",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function deleteUser($id)
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);

            if ($user->photo != null && Storage::disk('users')->exists($user->photo)) {
                $userDirectory = dirname($user->photo);
                Storage::disk('users')->delete($user->photo);

                if (Storage::disk('users')->exists($userDirectory)) {
                    Storage::disk('users')->deleteDirectory($userDirectory);
                }
            }

            $user->delete();

            DB::commit();

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

    public function getElements()
    {
        dd("ELementos");
    }
}
