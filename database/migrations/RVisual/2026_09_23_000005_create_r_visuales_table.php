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
        Schema::create('r_visuales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ot_id')->index();
            $table->string('clase')->index(); // Molde, Bombillo, etc.
            $table->string('nombre_moldura')->nullable();
            $table->string('cliente')->nullable();
            $table->string('codigo')->nullable();
            $table->string('sistema')->default('P.S.B.A.');
            $table->string('codigo_formato')->default('4CAL-01');
            $table->integer('nivel_revision')->default(0);
            $table->date('fecha_elaboracion')->nullable();
            $table->date('fecha_revision')->nullable();
            $table->date('fecha_aprobacion')->nullable();

            // Especificaciones de volumen
            $table->string('vol_molde_sd')->default('NA')->nullable();
            $table->string('vol_conjunto_fisico')->default('NA ml')->nullable();
            $table->string('vol_bombillo_sd')->nullable();
            $table->string('vol_conjunto_sd')->default('NA')->nullable();

            // Volumen desplazado parámetros
            $table->string('vol_desplazado_max')->nullable();
            $table->string('vol_desplazado_ideal')->nullable();
            $table->string('vol_desplazado_min')->nullable();

            // Observaciones generales y estado
            $table->text('observaciones_generales')->nullable();
            $table->unsignedBigInteger('inspector_id')->nullable()->index();
            $table->string('estado')->default('en_proceso');

            $table->timestamps();

            // Llave única por OT y Clase
            $table->unique(['ot_id', 'clase'], 'unique_r_visual_ot_clase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_visuales');
    }
};
