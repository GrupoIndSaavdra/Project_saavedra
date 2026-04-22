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
        Schema::table('CandadoObturador_pza', function (Blueprint $table) {
            $table->index('id_meta');
            $table->index('id_proceso');
            $table->index('estado');
            $table->index('n_juego');
            $table->index('n_pieza');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('CandadoObturador_pza', function (Blueprint $table) {
            $table->dropIndex(['id_meta']);
            $table->dropIndex(['id_proceso']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['n_juego']);
            $table->dropIndex(['n_pieza']);
        });
    }
};
