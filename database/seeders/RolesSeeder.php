<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(!Role::find(1)){
            Role::create([
                'name' => 'Admin',
                'description' => 'Administrador de la aplicacion'
            ]);
        }
        
        if(!Role::find(2)){
            Role::create([
                'name' => 'Acomodador',
                'description' => 'Encargado de gestionar la informacion de las bodegas'
            ]);
        }
        
        if(!Role::find(3)){
            Role::create([
                'name' => 'Solicitante',
                'description' => 'El usuario que guarda archivos en el sistema'
            ]);
        }
    }
}
