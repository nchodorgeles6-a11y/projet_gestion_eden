<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('departement_id')->nullable()->constrained()->onDelete('set null');
            $table->smallInteger('annee');
            $table->tinyInteger('mois')->nullable()->comment('null = budget annuel, 1-12 = mensuel');
            $table->string('categorie');
            $table->decimal('montant', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['annee', 'mois', 'categorie']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
