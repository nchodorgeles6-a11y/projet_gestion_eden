<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin',
            'rh',
            'employe',
            'prestataire',
        ];

        foreach ($roles as $role) {

            Role::create([
                'id' => Str::uuid(),
                'nom' => $role,
            ]);
        }
    }
}