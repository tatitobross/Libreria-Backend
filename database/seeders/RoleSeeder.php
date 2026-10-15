<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $legacyAdmin = Role::where('role', 'Admin')->first();
        if ($legacyAdmin) {
            $legacyAdmin->update(['role' => 'Administrador']);
        } else {
            Role::firstOrCreate(['role' => 'Administrador']);
        }

        $legacySeller = Role::whereIn('role', ['Cajero', 'Bibliotecaria'])->first();
        if ($legacySeller) {
            $legacySeller->update(['role' => 'Vendedor']);
        } else {
            Role::firstOrCreate(['role' => 'Vendedor']);
        }
    }
}
