<?php

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// -- Registro de usuario y autenticacion ---
Route::post('register', [UserController::class, 'registerUser']);
Route::post('login', [UserController::class, 'loginUser']);

// -- operaciones de usuario ---
Route::get('users', [UserController::class, 'getAllUsers']);
Route::get('user/{id}', [UserController::class, 'getUser']);
Route::post('user/{id}', [UserController::class, 'updateUser']);
Route::delete('user/{id}', [UserController::class, 'deleteUser']);

// Permisos
Route::get('permissions/{id}', [PermissionController::class, 'getPermissionsByUser']);
Route::put('permission/{id}/change-status', [PermissionController::class, 'changePermissionStatus']);

// ->middleware('verify.token');