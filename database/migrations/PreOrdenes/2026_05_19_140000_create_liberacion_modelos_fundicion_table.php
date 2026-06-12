<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de registro de Liberación de Modelos de Fundición.
     * Almacena las medidas capturadas por Calidad y el veredicto
     * (aprobado / rechazado) para cada OT revisada.
     */
    public function up(): void
    {
        Schema::create('liberacion_modelos_fundicion', function (Blueprint $table) {
            $table->id();

            // Relación con la OT del historial de Fundición
            $table->string('ot', 100)->index();

            // Estado de la revisión de Calidad
            // Valores posibles: pendiente | aprobado | rechazado
            $table->string('estado', 30)->default('pendiente');

            // Decisión de Calidad (aprobado | rechazado)
            $table->string('decision', 30)->nullable();
            
            // Archivo PDF generado
            $table->string('pdf_filename')->nullable();

            // Origen que disparó la revisión
            // Valores posibles: pre_orden | con_modelo
            $table->string('tipo_origen', 30)->nullable()
                ->comment('pre_orden: Almacén no tenía modelo y se mandó fabricar | con_modelo: Almacén ya contaba con él');

            // Tipo de modelo seleccionado en el formulario
            $table->string('tipo_modelo', 30)
                ->nullable()
                ->comment('Fondo | Obturador | Molde | Bombillo');

            // Medidas del Modelo (Macho y Hembra)
            $table->json('medidas_modelo')->nullable();
            $table->text('observaciones_modelo')->nullable();

            // Medidas de Plantilla y Templadera
            $table->json('medidas_plantilla')->nullable();
            $table->text('observaciones_plantilla')->nullable();

            // Medidas de Fondo
            $table->json('medidas_fondo')->nullable();
            $table->text('observaciones_fondo')->nullable();

            // Medidas de Obturador
            $table->json('medidas_obturador')->nullable();
            $table->text('observaciones_obturador')->nullable();

            // Motivo de rechazo
            $table->text('motivo_rechazo')->nullable()->comment('Obligatorio si estado = rechazado');

            // Auditoría de Calidad
            $table->unsignedBigInteger('user_id_calidad')->nullable();
            $table->string('user_nombre_calidad', 150)->nullable();
            $table->timestamp('fecha_revision')->nullable();

            $table->timestamps();

            // Múltiples tipos de modelo permitidos por cada OT
            $table->unique(['ot', 'tipo_modelo'], 'uq_liberacion_modelo_ot_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liberacion_modelos_fundicion');
    }
};
