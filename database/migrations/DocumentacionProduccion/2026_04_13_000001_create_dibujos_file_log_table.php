<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de auditoría para registrar todas las acciones realizadas
     * sobre los archivos físicos de dibujos/planos PDF en DIBUJOS_GIS.
     * Fuente de verdad: el filesystem. Esta tabla es solo registro histórico.
     */
    public function up(): void
    {
        Schema::create('dibujos_file_log', function (Blueprint $table) {
            $table->id();

            // Usuario que realizó la acción (nullable en caso de no estar autenticado)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            // Nombre completo + matrícula en el momento de la acción (desnormalizado para historial)
            $table->string('user_name', 200)->nullable();

            // Tipo de acción realizada sobre el archivo físico
            $table->enum('action', [
                'crear_carpeta',
                'subir_pdf',
                'eliminar_pdf',
                'reemplazar_pdf',
            ]);

            // Ruta relativa dentro de DIBUJOS_GIS (ej. "OT001/Clase_A")
            $table->string('ruta', 500);

            // Nombre del archivo afectado (null si la acción es solo de carpeta)
            $table->string('archivo', 300)->nullable();

            $table->timestamps();

            // Índice para consultas por usuario y fecha
            $table->index('user_id', 'idx_dibujos_log_user');
            $table->index('created_at', 'idx_dibujos_log_fecha');
            $table->index('action', 'idx_dibujos_log_action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dibujos_file_log');
    }
};
