<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $this->call([
            RoleSeeder::class,
            DepartementSeeder::class,
            PosteSeeder::class,
            TypeSuivieSeeder::class,
            MotifSeeder::class,
            AdminUserSeeder::class,
            TransactionSeeder::class,
        ]);
    
    }
}
