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
        if (!Schema::hasTable('cepillado_cnominal')) {
            Schema::create('cepillado_cnominal', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('radiof_mordaza', 8, 3);
                $table->decimal('radiof_mayor', 8, 3);
                $table->decimal('radiof_sufridera', 8, 3);
                $table->decimal('profuFinal_CFC', 8, 3);
                $table->decimal('profuFinal_mitadMB', 8, 3);
                $table->decimal('profuFinal_PCO', 8, 3);
                $table->decimal('ensamble', 8, 3);
                $table->decimal('distancia_barrenoAli', 8, 3);
                $table->decimal('profu_barrenoAliHembra', 8, 3);
                $table->decimal('profu_barrenoAliMacho', 8, 3);
                $table->decimal('altura_venaHembra', 8, 3);
                $table->decimal('altura_venaMacho', 8, 3);
                $table->decimal('ancho_vena', 8, 3);
                $table->decimal('laterales', 8, 3);
                $table->decimal('pin1', 8, 3);
                $table->decimal('pin2', 8, 3);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cepillado_cnominal');
    }
};
