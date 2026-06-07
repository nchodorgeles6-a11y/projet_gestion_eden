<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero')->unique();
            $table->string('fournisseur');
            $table->text('description')->nullable();
            $table->decimal('montant_ht',  15, 2)->default(0);
            $table->decimal('tva',          5, 2)->default(0);
            $table->decimal('montant_ttc', 15, 2)->default(0);
            $table->date('date_facture');
            $table->date('date_echeance')->nullable();
            $table->enum('statut', ['en_attente', 'payee', 'annulee'])->default('en_attente');
            $table->string('categorie')->nullable();
            $table->foreignUuid('departement_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->index(['statut', 'date_facture']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
