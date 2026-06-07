<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('acces_systeme')->default(false)->after('password');
        });

        // Les utilisateurs qui ont déjà un mot de passe gardent leur accès
        DB::table('users')->whereNotNull('password')->update(['acces_systeme' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('acces_systeme');
        });
    }
};
