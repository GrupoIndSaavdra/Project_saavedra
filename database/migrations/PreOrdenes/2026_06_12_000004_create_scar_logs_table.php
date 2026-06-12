<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de auditoría del Formato SCAR de Modelos (F-CCL-SCAR).
     * Registra cada generación, actualización y envío de alerta del
     * formato SCAR (Solicitud de Acción Correctiva de Rechazo).
     *
     * Acciones posibles:
     *   generar        => Calidad generó/regeneró el PDF del SCAR.
     *   enviar_alerta  => Calidad envió la alerta de SCAR por correo.
     */
    public function up(): void
    {
        Schema::create('scar_logs', function (Blueprint $table) {
            $table->id();

            // OT a la que pertenece el log
            $table->string('ot', 200)->index();

            // Tipo de modelo involucrado en el SCAR
            $table->string('tipo_modelo', 100)->nullable()
                ->comment('Fondo | Obturador | Molde | Bombillo (puede ser múltiples separados por coma)');

            // Número de folio del SCAR (F-SDM-YYYYMMDD-XXXX)
            $table->string('no_scar', 50)->nullable();

            // Tipo de acción realizada
            $table->string('accion', 50)
                ->comment('generar | enviar_alerta');

            // Nombre del PDF generado (F-CCL-SCAR_...)
            $table->string('pdf_filename', 300)->nullable();

            // Proveedor al que aplica el SCAR
            $table->string('proveedor', 200)->nullable();

            // Usuario que realizó la acción
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_nombre', 150)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scar_logs');
    }
};
