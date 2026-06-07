<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->date('date_transaction')->default(now()->toDateString())->after('montant');
            $table->enum('source', ['manuel', 'salaire', 'paiement'])->default('manuel')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['date_transaction', 'source']);
        });
    }
};
