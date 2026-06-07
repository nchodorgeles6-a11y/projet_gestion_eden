<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type_contrat', ['employe', 'prestataire'])->default('employe')->after('telephone');
            $table->enum('mode_paiement', ['fixe', 'par_prestation'])->default('fixe')->after('salaire_base');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['type_contrat', 'mode_paiement']);
        });
    }
};
