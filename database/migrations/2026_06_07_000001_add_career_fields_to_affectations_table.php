<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->string('motif_changement')->nullable()->after('date_fin');
            $table->decimal('salaire_capture', 15, 2)->nullable()->after('motif_changement');
            $table->json('primes_capture')->nullable()->after('salaire_capture');
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropColumn(['motif_changement', 'salaire_capture', 'primes_capture']);
        });
    }
};
