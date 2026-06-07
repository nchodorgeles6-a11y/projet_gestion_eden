<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suivies', function (Blueprint $table) {
            // Rendre motif_id optionnel (présence n'a pas de motif)
            $table->foreignUuid('motif_id')->nullable()->change();
            // Absence justifiée ou non
            $table->boolean('justifiee')->default(false)->after('motif_id');
            // Lien optionnel vers un congé enregistré
            $table->foreignUuid('conge_id')->nullable()->after('justifiee');
            $table->foreign('conge_id')->references('id')->on('conges')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('suivies', function (Blueprint $table) {
            $table->dropForeign(['conge_id']);
            $table->dropColumn(['justifiee', 'conge_id']);
            $table->foreignUuid('motif_id')->nullable(false)->change();
        });
    }
};
