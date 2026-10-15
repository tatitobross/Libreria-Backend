<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Privilege;

class PrivilegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::where('username', 'admin')->first();
        $role = \App\Models\Role::where('role', 'Administrador')->first();

        if (! $user || ! $role) {
            return;
        }

        Privilege::firstOrCreate(
            ['user_id' => $user->id, 'role_id' => $role->id],
            ['save' => 'S', 'update' => 'S', 'delete' => 'S', 'export' => 'S']
        );
    }
}
