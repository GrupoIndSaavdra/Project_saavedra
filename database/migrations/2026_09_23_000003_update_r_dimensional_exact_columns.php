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
        Schema::table('r_dimensionales', function (Blueprint $table) {
            if (!Schema::hasColumn('r_dimensionales', 'valores_nominales')) {
                $table->json('valores_nominales')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('r_dimensionales', 'tolerancias')) {
                $table->json('tolerancias')->nullable()->after('valores_nominales');
            }
        });

        Schema::table('r_dimensional_medidas', function (Blueprint $table) {
            if (!Schema::hasColumn('r_dimensional_medidas', 'c1')) {
                $table->string('c1')->nullable()->after('numero_pieza');
            }
            if (!Schema::hasColumn('r_dimensional_medidas', 'c2')) {
                $table->string('c2')->nullable()->after('c1');
            }
            if (!Schema::hasColumn('r_dimensional_medidas', 'e')) {
                $table->string('e')->nullable()->after('cuello');
            }
            if (!Schema::hasColumn('r_dimensional_medidas', 'f1')) {
                $table->string('f1')->nullable()->after('d');
            }
            if (!Schema::hasColumn('r_dimensional_medidas', 'a_total')) {
                $table->string('a_total')->nullable()->after('f1');
            }
            if (!Schema::hasColumn('r_dimensional_medidas', 'f')) {
                $table->string('f')->nullable()->after('a_total');
            }
            if (!Schema::hasColumn('r_dimensional_medidas', 'altura')) {
                $table->string('altura')->nullable()->after('f');
            }
            if (!Schema::hasColumn('r_dimensional_medidas', 'altura_2')) {
                $table->string('altura_2')->nullable()->after('altura');
            }
            if (!Schema::hasColumn('r_dimensional_medidas', 'al_cuello')) {
                $table->string('al_cuello')->nullable()->after('altura_2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('r_dimensionales', function (Blueprint $table) {
            $table->dropColumn(['valores_nominales', 'tolerancias']);
        });

        Schema::table('r_dimensional_medidas', function (Blueprint $table) {
            $table->dropColumn(['c1', 'c2', 'e', 'f1', 'a_total', 'f', 'altura', 'altura_2', 'al_cuello']);
        });
    }
};
