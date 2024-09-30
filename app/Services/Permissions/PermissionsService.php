<?php

namespace App\Services\Permissions;

use App\Models\Permission;
use Exception;
use Illuminate\Support\Facades\DB;

class PermissionsService
{

    public function createPermissions($userId)
    {
        try {
            $names = [
                "Crear Unidad Almacenamiento",
                "Actualizar Unidad Alamacenamiento",
                "Trasladar Unidad de almacenamiento",
                "Eliminar Unidad Almacenamiento",
                "Agregar Archivo",
                "Actualizar Archivo",
                "Trasladar Archivo",
                "Eliminar Archivo"
            ];

            DB::beginTransaction();

            for ($i = 0; $i < 8; $i++) {
                $newPermision = Permission::create([
                    "name" => $names[$i],
                    "status" => "active",
                    "user_id" => $userId
                ]);

                if (!$newPermision) return false;
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function removePermissions($userId)
    {
        try {
            DB::beginTransaction();

            Permission::where("user_id", $userId)->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
