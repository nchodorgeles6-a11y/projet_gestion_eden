<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('prime_transport', 10, 2)->default(0)->after('mode_paiement');
            $table->decimal('prime_logement', 10, 2)->default(0)->after('prime_transport');
            $table->decimal('prime_fonction', 10, 2)->default(0)->after('prime_logement');
            $table->decimal('prime_rendement', 10, 2)->default(0)->after('prime_fonction');
            $table->decimal('prime_panier', 10, 2)->default(0)->after('prime_rendement');
            $table->decimal('bonus_annuel', 10, 2)->default(0)->after('prime_panier');
            $table->boolean('cnps')->default(true)->after('bonus_annuel');
            $table->boolean('assurance_maladie')->default(false)->after('cnps');
            $table->json('avantages_nature')->nullable()->after('assurance_maladie');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'prime_transport', 'prime_logement', 'prime_fonction',
                'prime_rendement', 'prime_panier', 'bonus_annuel',
                'cnps', 'assurance_maladie', 'avantages_nature',
            ]);
        });
    }
};
