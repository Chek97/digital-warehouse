<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Services\Permissions\PermissionsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{

    public function __construct(private PermissionsService $permissionsService) {}

    public function getPermissionsByUser($id)
    {
        try {
            $userPermissions = Permission::where("user_id", $id)->get();

            return response()->json([
                "status" => true,
                "data" => $userPermissions
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "Error al listar los permisos de usuario",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function changePermissionStatus(Request $request, $id)
    {
        try {

            $validator = Validator::make($request->all(), [
                'newStatus' => 'required|string|in:active,desactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "error" => $validator->errors()
                ], 422);
            }

            $this->permissionsService->changePermissionStatus($request, $id);

            return response()->json([
                "status" => true,
                "data" => [],
                "message" => "El permiso del usuario fue modificado con exito"
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "Error al actualizar el permiso del usuario",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
