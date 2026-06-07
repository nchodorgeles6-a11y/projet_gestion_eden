<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'id' => Str::uuid(),
            'nom' => 'Admin',
            'prenom' => 'Principal',
            'email' => 'admin@eden.com',
            'telephone' => '0700000000',
            'lieu_habitation' => 'Abidjan',
            'sexe' => 'homme',
            'salaire_base' => 500000,
            'date_embauche' => now(),
            'date_naissance' => '1990-01-01',
            'password' => Hash::make('password'),
        ]);

        $roleAdmin = Role::where('nom', 'admin')->first();

        $admin->roles()->attach($roleAdmin->id);
    }
}