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
        // 1. Índice compuesto en clases para filtrar clases activas por OT al instante
        Schema::table('clases', function (Blueprint $table) {
            $table->index(['finalizada', 'id_ot'], 'idx_clases_finalizada_ot');
        });

        // 2. Covering index en piezas para búsquedas ultra rápidas de OTs activas (Index-Only Scan)
        Schema::table('piezas', function (Blueprint $table) {
            $table->index(['id_clase', 'id_ot'], 'idx_piezas_clase_ot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clases', function (Blueprint $table) {
            $table->dropIndex('idx_clases_finalizada_ot');
        });

        Schema::table('piezas', function (Blueprint $table) {
            $table->dropIndex('idx_piezas_clase_ot');
        });
    }
};