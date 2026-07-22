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
        Schema::table('fundicion_history', function (Blueprint $table) {
            $table->json('clases_enviadas')->nullable()->after('ayudas_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fundicion_history', function (Blueprint $table) {
            $table->dropColumn('clases_enviadas');
        });
    }
};
