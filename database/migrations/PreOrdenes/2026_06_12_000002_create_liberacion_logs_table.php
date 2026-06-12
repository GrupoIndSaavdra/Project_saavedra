<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de auditoría del Formato de Liberación de Modelo (F-CCL-LDM).
     * Registra cada borrador, aprobación, rechazo y envío de alerta de
     * los formatos de revisión dimensional de modelos de fundición.
     *
     * Acciones posibles:
     *   guardar        => Calidad guardó datos (borrador parcial).
     *   aprobar        => Calidad registró una aprobación del tipo de modelo.
     *   rechazar       => Calidad registró un rechazo del tipo de modelo.
     *   enviar_alerta  => Calidad envió la alerta final con PDFs por correo.
     */
    public function up(): void
    {
        Schema::create('liberacion_logs', function (Blueprint $table) {
            $table->id();

            // OT a la que pertenece el log
            $table->string('ot', 200)->index();

            // Tipo de modelo evaluado
            $table->string('tipo_modelo', 50)->nullable()
                ->comment('Fondo | Obturador | Molde | Bombillo');

            // Tipo de acción realizada
            $table->string('accion', 50)
                ->comment('guardar | aprobar | rechazar | enviar_alerta');

            // Nombre del PDF generado (F-CCL-LDM_...)
            $table->string('pdf_filename', 300)->nullable();

            // Estado resultante de la OT tras la acción
            $table->string('estado_global', 50)->nullable()
                ->comment('pendiente | aprobado | rechazado | mixto | calidad_aprobado | etc.');

            // Usuario que realizó la acción
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_nombre', 150)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liberacion_logs');
    }
};
