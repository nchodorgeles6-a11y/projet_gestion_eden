<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Departement;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $depts = Departement::pluck('id', 'nom');

        $data = [
            // ── Janvier ──────────────────────────────────────────────────────────
            ['2026-01-05', 'revenu',  'Finance',          850000,  'manuel',  'Prestation de service — Mission audit SARL Horizon'],
            ['2026-01-08', 'dépense', 'Direction',         95000,  'manuel',  'Transport & Déplacement — Carburant véhicules direction'],
            ['2026-01-10', 'dépense', 'Informatique',     220000,  'manuel',  'Fournitures & Matériel — Achat 2 claviers + souris'],
            ['2026-01-15', 'dépense', 'Ressources Humaines', 48000, 'manuel', 'Factures & Abonnements — Abonnement internet janvier'],
            ['2026-01-20', 'revenu',  'Marketing',        320000,  'manuel',  'Contrat & Marché — Contrat campagne communication ONG Espoir'],
            ['2026-01-25', 'dépense', 'Finance',           75000,  'manuel',  'Impôts & Taxes — Patente trimestre 1'],
            ['2026-01-28', 'dépense', 'Direction',         60000,  'manuel',  'Restauration & Réception — Déjeuner partenaires BSIC'],

            // ── Février ───────────────────────────────────────────────────────────
            ['2026-02-03', 'revenu',  'Finance',         1200000,  'manuel',  'Prestation de service — Consulting stratégie entreprise TechBuild'],
            ['2026-02-07', 'dépense', 'Comptabilité',     38000,   'manuel',  'Factures & Abonnements — Renouvellement logiciel comptable'],
            ['2026-02-10', 'dépense', 'Ressources Humaines', 115000,'manuel', 'Formation & Développement — Séminaire RH Abidjan'],
            ['2026-02-14', 'dépense', 'Direction',         85000,  'manuel',  'Transport & Déplacement — Mission Yamoussoukro'],
            ['2026-02-18', 'revenu',  'Marketing',        450000,  'manuel',  'Vente de produit — Lot supports visuels client Horizon'],
            ['2026-02-22', 'dépense', 'Informatique',     180000,  'manuel',  'Maintenance & Réparations — Réparation serveur principal'],
            ['2026-02-26', 'dépense', 'Finance',           52000,  'manuel',  'Assurances — Prime assurance bureaux T1'],

            // ── Mars ──────────────────────────────────────────────────────────────
            ['2026-03-04', 'revenu',  'Finance',          680000,  'manuel',  'Contrat & Marché — Marché fourniture de services SODECI'],
            ['2026-03-06', 'dépense', 'Direction',        120000,  'manuel',  'Loyer & Charges locatives — Loyer bureaux mars'],
            ['2026-03-10', 'dépense', 'Informatique',      65000,  'manuel',  'Factures & Abonnements — Facture fibre optique mars'],
            ['2026-03-14', 'revenu',  'Marketing',        290000,  'manuel',  'Prestation de service — Création identité visuelle startup AgriCI'],
            ['2026-03-19', 'dépense', 'Ressources Humaines', 92000, 'manuel', 'Transport & Déplacement — Frais terrain recrutement Bouaké'],
            ['2026-03-24', 'dépense', 'Comptabilité',      43000,  'manuel',  'Fournitures & Matériel — Ramettes papier + cartouches'],
            ['2026-03-28', 'revenu',  'Finance',          500000,  'manuel',  'Remboursement — Remboursement avance client BuildPro'],

            // ── Avril ─────────────────────────────────────────────────────────────
            ['2026-04-02', 'dépense', 'Direction',        120000,  'manuel',  'Loyer & Charges locatives — Loyer bureaux avril'],
            ['2026-04-05', 'revenu',  'Finance',          750000,  'manuel',  'Prestation de service — Audit financier annuel SA Côtière'],
            ['2026-04-09', 'dépense', 'Informatique',     350000,  'manuel',  'Fournitures & Matériel — Achat onduleur salle serveur'],
            ['2026-04-11', 'dépense', 'Marketing',         72000,  'manuel',  'Prestation externe — Photographe événement lancement produit'],
            ['2026-04-16', 'revenu',  'Marketing',        185000,  'manuel',  'Vente de produit — Vente supports imprimés campagne santé'],
            ['2026-04-20', 'dépense', 'Finance',           48000,  'manuel',  'Factures & Abonnements — Facture téléphonie entreprise'],
            ['2026-04-25', 'dépense', 'Ressources Humaines', 135000,'manuel', 'Formation & Développement — Formation Excel avancé équipe comptable'],
            ['2026-04-29', 'revenu',  'Finance',          300000,  'manuel',  'Subvention & Financement — Appui FDFP formation professionnelle'],

            // ── Mai ───────────────────────────────────────────────────────────────
            ['2026-05-02', 'dépense', 'Direction',        120000,  'manuel',  'Loyer & Charges locatives — Loyer bureaux mai'],
            ['2026-05-06', 'revenu',  'Finance',          920000,  'manuel',  'Contrat & Marché — Marché réalisation système ERP MicroBuild'],
            ['2026-05-08', 'dépense', 'Informatique',      88000,  'manuel',  'Maintenance & Réparations — Maintenance préventive parc informatique'],
            ['2026-05-12', 'dépense', 'Comptabilité',      60000,  'manuel',  'Impôts & Taxes — Déclaration TVA mai'],
            ['2026-05-15', 'dépense', 'Marketing',         55000,  'manuel',  'Transport & Déplacement — Déplacement équipe terrain Korhogo'],
            ['2026-05-19', 'revenu',  'Marketing',        410000,  'manuel',  'Prestation de service — Stratégie digitale client FinanceCI'],
            ['2026-05-23', 'dépense', 'Direction',         78000,  'manuel',  'Restauration & Réception — Réception partenaires institutionnels'],
            ['2026-05-28', 'dépense', 'Finance',          150000,  'manuel',  'Assurances — Prime assurance responsabilité civile T2'],

            // ── Juin ──────────────────────────────────────────────────────────────
            ['2026-06-02', 'dépense', 'Direction',        120000,  'manuel',  'Loyer & Charges locatives — Loyer bureaux juin'],
            ['2026-06-03', 'revenu',  'Finance',          600000,  'manuel',  'Prestation de service — Mission conseil restructuration RH'],
            ['2026-06-05', 'dépense', 'Informatique',      42000,  'manuel',  'Factures & Abonnements — Renouvellement hébergement serveur web'],
        ];

        foreach ($data as [$date, $type, $deptNom, $montant, $source, $description]) {
            $deptId = $depts[$deptNom] ?? $depts->first();
            if (!$deptId) continue;

            Transaction::create([
                'id'               => Str::uuid(),
                'departement_id'   => $deptId,
                'user_id'          => null,
                'type'             => $type,
                'montant'          => $montant,
                'date_transaction' => $date,
                'source'           => $source,
                'description'      => $description,
            ]);
        }
    }
}
