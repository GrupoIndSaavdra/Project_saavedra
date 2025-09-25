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
        if (!Schema::hasTable('revCalificado_cnominal')) {
            Schema::create('revCalificado_cnominal', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('diametro_ceja', 8, 3)->nullable();
                $table->decimal('diametro_sufridera', 8, 3)->nullable();
                $table->decimal('altura_sufridera', 8, 3)->nullable();
                $table->decimal('diametro_conexion', 8, 3)->nullable();
                $table->decimal('altura_conexion', 8, 3)->nullable();
                $table->decimal('diametro_caja', 8, 3)->nullable();
                $table->decimal('altura_caja', 8, 3)->nullable();
                $table->decimal('altura_total', 8, 3)->nullable();
                $table->decimal('simetria', 8, 3)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revCalificado_cnominal');
    }
};
