<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE motifs MODIFY COLUMN type ENUM('conge','permission','maladie','autre','retard') NOT NULL DEFAULT 'autre'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE motifs MODIFY COLUMN type ENUM('conge','permission','maladie','autre') NOT NULL DEFAULT 'autre'");
    }
};
