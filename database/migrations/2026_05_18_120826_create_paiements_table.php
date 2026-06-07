<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('montant',10,2);
            $table->string('mois');
            $table->year('annee');
            // $table->enum('mode_paiement', ['espece', 'virement', 'cheque','mobile_money']);
            $table->enum('statut', ['en_attente', 'payé']) ->default('payé'); 
            $table->json('meta')->nullable(); //(json)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
