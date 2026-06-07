<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Poste;
use App\Models\Departement;
use Illuminate\Support\Str;

class PosteSeeder extends Seeder
{
    public function run(): void
    {
        $depts = Departement::pluck('id', 'nom');

        $postes = [
            'Direction' => [
                ['Directeur Général', 'Pilotage stratégique et représentation légale de l\'entreprise'],
                ['Directeur Général Adjoint', 'Appui à la direction générale et supervision des opérations'],
                ['Secrétaire de Direction', 'Gestion de l\'agenda, accueil et correspondances de la direction'],
                ['Chargé de Communication', 'Relations presse, communication interne et externe'],
                ['Conseiller Juridique', 'Veille juridique, rédaction de contrats et conformité légale'],
            ],

            'Ressources Humaines' => [
                ['Directeur RH', 'Définition et pilotage de la politique ressources humaines'],
                ['Responsable Recrutement', 'Gestion du processus de recrutement et intégration des nouveaux employés'],
                ['Gestionnaire de Paie', 'Traitement des salaires, déclarations sociales et fiscales'],
                ['Chargé de Formation', 'Identification des besoins, organisation et suivi des formations'],
                ['Assistant RH', 'Support administratif RH, gestion des dossiers du personnel'],
            ],

            'Finance' => [
                ['Directeur Financier', 'Supervision des finances, budget prévisionnel et reporting'],
                ['Contrôleur de Gestion', 'Analyse des performances financières et tableaux de bord'],
                ['Trésorier', 'Gestion de la trésorerie, flux de paiements et relations bancaires'],
                ['Analyste Financier', 'Études financières, valorisation et recommandations d\'investissement'],
                ['Assistant Comptable', 'Saisie comptable, rapprochements et archivage des pièces justificatives'],
            ],

            'Comptabilité' => [
                ['Chef Comptable', 'Supervision de la comptabilité générale et production des états financiers'],
                ['Comptable Général', 'Tenue des livres comptables et clôture mensuelle des comptes'],
                ['Comptable Fournisseurs', 'Gestion des factures fournisseurs et règlements'],
                ['Comptable Clients', 'Suivi des créances clients, relances et encaissements'],
                ['Auditeur Interne', 'Contrôle des procédures internes et conformité comptable'],
            ],

            'Informatique' => [
                ['Directeur Informatique', 'Stratégie SI, supervision des projets technologiques et sécurité'],
                ['Développeur Full Stack', 'Développement et maintenance des applications web et métier'],
                ['Administrateur Système', 'Gestion des serveurs, réseaux et infrastructure informatique'],
                ['Technicien Informatique', 'Support utilisateurs, maintenance du parc informatique'],
                ['Responsable Cybersécurité', 'Sécurité des systèmes d\'information et gestion des risques numériques'],
                ['Chef de Projet IT', 'Coordination des projets informatiques, planification et livraisons'],
            ],

            'Marketing' => [
                ['Directeur Marketing', 'Définition de la stratégie marketing et supervision des campagnes'],
                ['Responsable Digital', 'Gestion des réseaux sociaux, SEO/SEA et marketing en ligne'],
                ['Chef de Produit', 'Positionnement, développement et suivi du cycle de vie des produits'],
                ['Graphiste / Designer', 'Création de supports visuels, identité de marque et maquettes'],
                ['Chargé de Communication', 'Rédaction de contenus, relations presse et événementiel'],
            ],
        ];

        foreach ($postes as $deptNom => $liste) {
            $deptId = $depts[$deptNom] ?? null;
            if (!$deptId)
                continue;

            foreach ($liste as [$nom, $description]) {
                Poste::create([
                    'id' => Str::uuid(),
                    'nom' => $nom,
                    'description' => $description,
                    'departement_id' => $deptId,
                ]);
            }
        }
    }
}
