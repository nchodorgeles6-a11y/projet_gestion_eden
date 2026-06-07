<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Motif;
use Illuminate\Support\Str;

class MotifSeeder extends Seeder
{
    public function run(): void
    {
        $motifs = [
            // ── Congés légaux — Code du Travail ivoirien ─────────────────────
            // type = 'conge' → apparaissent dans le formulaire de demande de congé
            // et dans le sélecteur de congé du module de présence

            // 2,2 jours ouvrables / mois de travail (≈ 26 j/an) + ancienneté
            ['nom' => 'Congé annuel',                              'type' => 'conge'],

            // Variable selon ancienneté, sur certificat médical
            ['nom' => 'Congé maladie',                             'type' => 'conge'],

            // 14 semaines payées (6 semaines avant + 8 semaines après accouchement)
            ['nom' => 'Congé maternité',                           'type' => 'conge'],

            // 2 jours ouvrables payés à la naissance d'un enfant (secteur privé)
            ['nom' => 'Congé paternité',                           'type' => 'conge'],

            // 4 jours pour le mariage du salarié lui-même
            ['nom' => 'Congé mariage — salarié (4 jours)',         'type' => 'conge'],

            // 2 jours pour le mariage d'un enfant, frère ou sœur
            ['nom' => 'Congé mariage — enfant / frère / sœur (2 jours)', 'type' => 'conge'],

            // 5 jours pour le décès du conjoint, d'un enfant, du père ou de la mère
            ['nom' => 'Congé décès — conjoint / enfant / parent (5 jours)', 'type' => 'conge'],

            // 2 à 3 jours pour un frère, une sœur ou certains parents par alliance
            ['nom' => 'Congé décès — frère / sœur / famille (2 à 3 jours)', 'type' => 'conge'],

            // Baptême, communion, confirmation, etc. — généralement 1 jour / événement
            ['nom' => 'Congé événement familial (1 jour)',         'type' => 'conge'],

            // Selon conventions collectives ou accord de l'employeur
            ['nom' => 'Congé formation',                           'type' => 'conge'],

            // Accord employeur / salarié, non rémunéré
            ['nom' => 'Congé sans solde',                          'type' => 'conge'],

            // ── Permissions ───────────────────────────────────────────────────
            // type = 'permission' → déclenchent le sélecteur de permission
            // dans le module de présence (max 10 j/an, sans retenue, non déductibles)

            ['nom' => 'Permission exceptionnelle',                 'type' => 'permission'],
            ['nom' => 'Absence autorisée',                         'type' => 'permission'],

            // ── Maladies / accidents ──────────────────────────────────────────
            // type = 'maladie' → absences médicales sans demande de congé formelle

            ['nom' => 'Maladie (certificat médical)',               'type' => 'maladie'],
            ['nom' => 'Accident du travail',                        'type' => 'maladie'],
            ['nom' => 'Hospitalisation',                            'type' => 'maladie'],

            // ── Autres motifs (module présence) ──────────────────────────────
            // type = 'autre' → motifs de suivi sans document associé

            ['nom' => 'Absence non justifiée',                     'type' => 'autre'],
            ['nom' => 'Mission professionnelle',                   'type' => 'autre'],
            ['nom' => 'Télétravail',                               'type' => 'autre'],

            // ── Motifs de retard ──────────────────────────────────────────────
            // type = 'retard' → affichés uniquement dans le module présence
            // lorsque le statut choisi est "Retard"

            ['nom' => 'Problème de transport',                     'type' => 'retard'],
            ['nom' => 'Urgence familiale',                         'type' => 'retard'],
            ['nom' => 'Rendez-vous médical',                       'type' => 'retard'],
            ['nom' => 'Force majeure',                             'type' => 'retard'],
            ['nom' => 'Retard non justifié',                       'type' => 'retard'],
        ];

        foreach ($motifs as $data) {
            Motif::create([
                'id'   => Str::uuid(),
                'nom'  => $data['nom'],
                'type' => $data['type'],
            ]);
        }
    }
}
