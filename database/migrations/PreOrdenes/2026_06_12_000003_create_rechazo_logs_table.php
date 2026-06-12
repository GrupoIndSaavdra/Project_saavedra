<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de auditoría del Formato de Rechazo de Modelo.
     * Registra cada vez que Calidad genera el PDF de rechazo (F-CCL-LDM en estado rechazado)
     * y cuando Almacén confirma la recepción física del modelo rechazado.
     *
     * Acciones posibles:
     *   generar          => Calidad generó el PDF de rechazo.
     *   confirmar_recepcion => Almacén confirmó la recepción física del modelo rechazado.
     */
    public function up(): void
    {
        Schema::create('rechazo_logs', function (Blueprint $table) {
            $table->id();

            // OT a la que pertenece el log
            $table->string('ot', 200)->index();

            // Tipo de modelo rechazado
            $table->string('tipo_modelo', 50)->nullable()
                ->comment('Fondo | Obturador | Molde | Bombillo');

            // Tipo de acción realizada
            $table->string('accion', 60)
                ->comment('generar | confirmar_recepcion');

            // Nombre del PDF de rechazo generado (F-CCL-LDM_..._RECHAZADO.pdf)
            $table->string('pdf_filename', 300)->nullable();

            // Motivo de rechazo (copia del campo de la liberación)
            $table->text('motivo_rechazo')->nullable();

            // Usuario que realizó la acción
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_nombre', 150)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rechazo_logs');
    }
};
