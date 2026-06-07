<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeSuivie;
use Illuminate\Support\Str;

class TypeSuivieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'présent',
            'absent',
            'retard',
        ];

        foreach ($types as $type) {
            TypeSuivie::create([
                'id' => Str::uuid(),
                'nom' => $type,
            ]);
        }
    }
}