<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRoleId = \App\Models\Role::where('role', 'Administrador')->value('id');
        $sellerRoleId = \App\Models\Role::where('role', 'Vendedor')->value('id');

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('Hola123'),
                'name' => 'Karen Coto',
                'gender' => 'mujer',
                'birth' => '2000-01-01',
                'email' => 'admin@libreria.test',
                'role_id' => $adminRoleId,
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['username' => 'vendedor'],
            [
                'password' => Hash::make('Hola123'),
                'name' => 'Vendedor de prueba',
                'gender' => 'no especificado',
                'birth' => '2000-01-01',
                'email' => 'vendedor@libreria.test',
                'role_id' => $sellerRoleId,
                'status' => 'active',
            ]
        );
    }
}
