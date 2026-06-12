<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de auditoría de la Pre-Orden de Fabricación de Modelo (4ALM-17).
     * Registra cada generación, actualización y envío de alerta por correo
     * del formato de Pre-Orden de Modelo.
     *
     * Acciones posibles:
     *   generar        => Almacén generó/regeneró el PDF de la pre-orden.
     *   enviar_alerta  => Almacén notificó al proveedor por correo.
     */
    public function up(): void
    {
        Schema::create('preorden_logs', function (Blueprint $table) {
            $table->id();

            // OT a la que pertenece el log
            $table->string('ot', 200)->index();

            // Proveedor al que se dirige la pre-orden
            $table->string('proveedor', 200)->nullable();

            // Tipo de acción realizada
            $table->string('accion', 50)
                ->comment('generar | enviar_alerta');

            // Nombre del PDF generado / enviado
            $table->string('pdf_filename', 300)->nullable();

            // Usuario que realizó la acción
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_nombre', 150)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preorden_logs');
    }
};
