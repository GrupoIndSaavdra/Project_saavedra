<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Agrega n_pieza a soldaduraPTA_pza
 *
 * Identificador por mitad de pieza, ej: '1M' (macho), '1H' (hembra).
 * Debe correr ANTES de 2026_02_23_000001_add_pta_fields_to_soldaduraPTA_pza.php
 * porque ese archivo agrega columnas usando n_pieza como ancla (->after('n_pieza')).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {
            if (!Schema::hasColumn('soldaduraPTA_pza', 'n_pieza')) {
                $table->string('n_pieza')->nullable()->after('n_juego');
            }
        });
    }

    public function down(): void
    {
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {
            $table->dropColumn('n_pieza');
        });
    }
};
