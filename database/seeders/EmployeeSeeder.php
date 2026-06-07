<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Poste;
use App\Models\Affectation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $roleEmploye = Role::where('nom', 'employe')->first();

        $employees = [
            // nom, prenom, email, sexe, poste, salaire_base, date_embauche, prime_transport, date_naissance, lieu_habitation
            ['KOUASSI',    'Ama',        'a.kouassi@eden.ci',    'femme',  'Gestionnaire de Paie',       450000, '2021-03-15', 30000, '1990-07-22', 'Cocody, Abidjan'],
            ['BAMBA',      'Seydou',     's.bamba@eden.ci',      'homme',  'Développeur Full Stack',      600000, '2022-01-10', 35000, '1993-03-05', 'Plateau, Abidjan'],
            ['DIABATÉ',    'Fatoumata',  'f.diabate@eden.ci',    'femme',  'Responsable Recrutement',     500000, '2020-06-01', 30000, '1988-11-14', 'Marcory, Abidjan'],
            ['KONAN',      'Yao',        'y.konan@eden.ci',      'homme',  'Contrôleur de Gestion',       700000, '2019-09-01', 40000, '1985-04-30', 'Yopougon, Abidjan'],
            ['TRAORÉ',     'Mariam',     'm.traore@eden.ci',     'femme',  'Comptable Général',           380000, '2021-11-20', 25000, '1991-08-09', 'Abobo, Abidjan'],
            ['KOFFI',      'Kwame',      'k.koffi@eden.ci',      'homme',  'Administrateur Système',      550000, '2022-04-05', 35000, '1992-01-17', 'Adjamé, Abidjan'],
            ['COULIBALY',  'Ibrahim',    'i.coulibaly@eden.ci',  'homme',  'Analyste Financier',          600000, '2020-02-14', 35000, '1989-06-23', 'Treichville, Abidjan'],
            ['YAO',        'Adjoua',     'a.yao@eden.ci',        'femme',  'Assistant RH',                250000, '2023-01-03', 20000, '1998-12-02', 'Koumassi, Abidjan'],
            ['DOSSO',      'Oumar',      'o.dosso@eden.ci',      'homme',  'Technicien Informatique',     300000, '2023-06-15', 25000, '1997-05-18', 'Port-Bouët, Abidjan'],
            ['GNAGNE',     'Véronique',  'v.gnagne@eden.ci',     'femme',  'Chargé de Communication',     350000, '2021-07-19', 25000, '1990-09-27', 'Cocody, Abidjan'],
            ['DIALLO',     'Moussa',     'm.diallo@eden.ci',     'homme',  'Chef Comptable',              800000, '2018-04-02', 50000, '1982-02-11', 'Riviera, Abidjan'],
            ['AKISSI',     'Carine',     'c.akissi@eden.ci',     'femme',  'Graphiste / Designer',        400000, '2022-09-12', 25000, '1994-07-03', 'Angré, Abidjan'],
            ['SÉRY',       'Emmanuel',   'e.sery@eden.ci',       'homme',  'Responsable Cybersécurité',   750000, '2020-11-30', 40000, '1986-10-16', 'Plateau, Abidjan'],
            ['FOFANA',     'Aminata',    'a.fofana@eden.ci',     'femme',  'Comptable Fournisseurs',      320000, '2022-02-28', 25000, '1993-04-08', 'Attécoubé, Abidjan'],
            ['BOGUI',      'Henri',      'h.bogui@eden.ci',      'homme',  'Trésorier',                   650000, '2019-08-15', 40000, '1984-01-25', 'Deux-Plateaux, Abidjan'],
            ['DIOMANDÉ',   'Kadiatou',   'k.diomande@eden.ci',   'femme',  'Chef de Projet IT',           700000, '2021-05-10', 40000, '1987-03-19', 'Cocody, Abidjan'],
            ['KONÉ',       'Lacina',     'l.kone@eden.ci',       'homme',  'Chargé de Formation',         400000, '2022-10-03', 30000, '1991-12-07', 'Yopougon, Abidjan'],
            ['ESSI',       'Marlène',    'm.essi@eden.ci',        'femme',  'Responsable Digital',         500000, '2021-03-22', 30000, '1990-06-14', 'Marcory, Abidjan'],
            ['TOURÉ',      'Abdoulaye',  'a.toure2@eden.ci',     'homme',  'Conseiller Juridique',        900000, '2017-12-01', 50000, '1980-08-20', 'Riviera, Abidjan'],
            ['ASSOUMOU',   'Grace',      'g.assoumou@eden.ci',   'femme',  'Comptable Clients',           300000, '2023-04-10', 25000, '1996-11-30', 'Abobo, Abidjan'],
            ['CAMARA',     'Lamine',     'l.camara@eden.ci',     'homme',  'Chef de Produit',             550000, '2020-07-06', 35000, '1988-09-12', 'Adjamé, Abidjan'],
            ['N\'GUESSAN', 'Sita',       's.nguessan@eden.ci',   'femme',  'Secrétaire de Direction',     350000, '2022-08-01', 25000, '1992-02-28', 'Treichville, Abidjan'],
            ['OUATTARA',   'Bakary',     'b.ouattara@eden.ci',   'homme',  'Assistant Comptable',         280000, '2024-01-15', 20000, '1999-04-05', 'Koumassi, Abidjan'],
            ['ABOA',       'Prisca',     'p.aboa@eden.ci',       'femme',  'Auditeur Interne',            600000, '2020-09-07', 35000, '1987-07-21', 'Cocody, Abidjan'],
            ['DEMBÉLÉ',    'Samba',      's.dembele@eden.ci',    'homme',  'Directeur Informatique',     1200000, '2016-06-01', 60000, '1978-03-15', 'Plateau, Abidjan'],
        ];

        foreach ($employees as [$nom, $prenom, $email, $sexe, $posteNom, $salaire, $embauche, $transport, $naissance, $lieu]) {
            if (User::where('email', $email)->exists()) {
                $this->command->line("  Existe déjà : $email");
                continue;
            }

            $user = User::create([
                'id'                => Str::uuid(),
                'nom'               => $nom,
                'prenom'            => $prenom,
                'email'             => $email,
                'sexe'              => $sexe,
                'salaire_base'      => $salaire,
                'type_contrat'      => 'employe',
                'date_embauche'     => $embauche,
                'date_naissance'    => $naissance,
                'lieu_habitation'   => $lieu,
                'prime_transport'   => $transport,
                'prime_logement'    => 0,
                'prime_fonction'    => 0,
                'prime_rendement'   => 0,
                'prime_panier'      => 0,
                'bonus_annuel'      => 0,
                'cnps'              => true,
                'assurance_maladie' => true,
                'acces_systeme'     => false,
                'password'          => Hash::make('password'),
            ]);

            if ($roleEmploye) {
                $user->roles()->attach($roleEmploye->id);
            }

            $poste = Poste::where('nom', $posteNom)->first();
            if ($poste) {
                Affectation::create([
                    'id'         => Str::uuid(),
                    'user_id'    => $user->id,
                    'poste_id'   => $poste->id,
                    'date_debut' => $embauche,
                ]);
            }

            $this->command->line("  Créé : $nom $prenom — $posteNom — " . number_format($salaire, 0, ',', ' ') . ' FCFA');
        }

        $this->command->info('25 employés insérés avec succès.');
    }
}
