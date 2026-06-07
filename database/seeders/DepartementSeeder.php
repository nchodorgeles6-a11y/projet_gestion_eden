<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departement;
use Illuminate\Support\Str;

class DepartementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departements = [
            'Direction',
            'Ressources Humaines',
            'Finance',
            'Informatique',
            'Marketing',
            'Comptabilité',
        ];

        foreach ($departements as $departement) {

            Departement::create([
                'id' => Str::uuid(),
                'nom' => $departement,
            ]);
        }
    }
}