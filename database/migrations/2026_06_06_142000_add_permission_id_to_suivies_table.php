<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suivies', function (Blueprint $table) {
            $table->foreignUuid('permission_id')->nullable()->after('conge_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('suivies', function (Blueprint $table) {
            $table->dropForeign(['permission_id']);
            $table->dropColumn('permission_id');
        });
    }
};
