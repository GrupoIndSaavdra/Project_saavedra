<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cavidades_cnominal', function (Blueprint $table) {
            $table->decimal('altura1', 8, 3)->nullable();
            $table->decimal('altura2', 8, 3)->nullable();
            $table->decimal('altura3', 8, 3)->nullable();
        });

        Schema::table('cavidades_tolerancia', function (Blueprint $table) {
            $table->decimal('altura1', 8, 3)->nullable();
            $table->decimal('altura2', 8, 3)->nullable();
            $table->decimal('altura3', 8, 3)->nullable();
        });

        Schema::table('cavidades_pza', function (Blueprint $table) {
            $table->decimal('altura1', 8, 3)->nullable();
            $table->decimal('altura2', 8, 3)->nullable();
            $table->decimal('altura3', 8, 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cavidades_cnominal', function (Blueprint $table) {
            $table->dropColumn(['altura1', 'altura2', 'altura3']);
        });

        Schema::table('cavidades_tolerancia', function (Blueprint $table) {
            $table->dropColumn(['altura1', 'altura2', 'altura3']);
        });

        Schema::table('cavidades_pza', function (Blueprint $table) {
            $table->dropColumn(['altura1', 'altura2', 'altura3']);
        });
    }
};
