<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletins_paie', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('mois');
            $table->year('annee');

            // Éléments de rémunération
            $table->decimal('salaire_base', 12, 2)->default(0);
            $table->decimal('prime_transport', 12, 2)->default(0);
            $table->decimal('prime_logement', 12, 2)->default(0);
            $table->decimal('prime_fonction', 12, 2)->default(0);
            $table->decimal('prime_rendement', 12, 2)->default(0);
            $table->decimal('prime_panier', 12, 2)->default(0);
            $table->decimal('bonus_annuel', 12, 2)->default(0);
            $table->decimal('heures_sup', 6, 2)->default(0);
            $table->decimal('taux_heures_sup', 12, 2)->default(0);
            $table->decimal('avantages_nature_montant', 12, 2)->default(0);
            $table->decimal('salaire_brut', 12, 2)->default(0);

            // Retenues salariales
            $table->decimal('cnps_salarie', 12, 2)->default(0);
            $table->decimal('assurance_maladie_salarie', 12, 2)->default(0);
            $table->decimal('igr', 12, 2)->default(0);
            $table->decimal('is_salaire', 12, 2)->default(0);
            $table->decimal('avance_salaire', 12, 2)->default(0);
            $table->decimal('pret_entreprise', 12, 2)->default(0);
            $table->decimal('autres_retenues', 12, 2)->default(0);
            $table->decimal('total_retenues', 12, 2)->default(0);
            $table->decimal('net_a_payer', 12, 2)->default(0);

            // Cotisations patronales
            $table->decimal('cnps_employeur', 12, 2)->default(0);
            $table->decimal('accident_travail', 12, 2)->default(0);
            $table->decimal('prestations_familiales', 12, 2)->default(0);
            $table->decimal('formation_professionnelle', 12, 2)->default(0);
            $table->decimal('cout_total_employeur', 12, 2)->default(0);

            // Paiement
            $table->enum('mode_paiement', ['virement', 'especes', 'mobile_money', 'cheque'])->default('virement');
            $table->date('date_paiement')->nullable();
            $table->enum('statut', ['brouillon', 'valide', 'paye'])->default('brouillon');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletins_paie');
    }
};
