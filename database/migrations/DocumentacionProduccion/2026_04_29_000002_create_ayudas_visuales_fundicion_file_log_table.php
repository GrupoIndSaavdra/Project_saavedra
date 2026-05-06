<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de auditoría para registrar todas las acciones realizadas
     * sobre las Ayudas Visuales de Fundición en AYUDAS_GIS/Fundicion.
     */
    public function up(): void
    {
        Schema::create('ayudas_visuales_fundicion_file_log', function (Blueprint $table) {
            $table->id();

            // Usuario que realizó la acción
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            // Nombre completo + matrícula (historial)
            $table->string('user_name', 200)->nullable();

            // Tipo de acción
            $table->enum('action', [
                'crear_carpeta',
                'subir_pdf',
                'eliminar_pdf',
                'reemplazar_pdf',
            ]);

            // Ruta relativa (ej. "Fundicion/Bombillo")
            $table->string('ruta', 500);

            // Nombre del archivo afectado
            $table->string('archivo', 300)->nullable();

            $table->timestamps();

            // Índices
            $table->index('user_id', 'idx_ayudas_fun_log_user');
            $table->index('created_at', 'idx_ayudas_fun_log_fecha');
            $table->index('action', 'idx_ayudas_fun_log_action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ayudas_visuales_fundicion_file_log');
    }
};
