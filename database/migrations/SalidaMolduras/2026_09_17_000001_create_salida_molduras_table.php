<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla principal de reportes "Salida de Molduras".
 * Registra la información general del formato por OT y clase.
 * Código de formato: F PRO CPT | Versión 6 | Fecha Rev: 22/04/2026
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salida_molduras', function (Blueprint $table) {
            $table->id();

            // Relación con la Orden de Trabajo (string, no FK estricta por arquitectura del proyecto)
            $table->string('ot_id')->comment('ID de la Orden de Trabajo relacionada');

            // Clase de la OT detectada automáticamente (MOLDES, BOMBILLOS, etc.)
            $table->string('clase')->comment('Clase de la OT (ej. 1 - MOLDES, 3 - BOMBILLOS)');

            // Nombre de la moldura (obtenido de la relación moldura→OT)
            $table->string('nombre_moldura')->comment('Nombre de la moldura asociada a la OT');

            // Inspector que llena el formato (perfil Calidad o Admin)
            $table->unsignedBigInteger('inspector_id')->comment('ID del usuario inspector que crea el reporte');
            $table->foreign('inspector_id')->references('id')->on('users')->onDelete('restrict');

            // Información del cliente y pedido
            $table->string('cliente')->nullable()->comment('Nombre del cliente de la OT');
            $table->integer('cantidad_pedido')->default(0)->comment('Cantidad de pedido de la OT');
            $table->integer('cantidad_consignacion')->default(0)->comment('Cantidad adicional de consignación');

            // Fecha de inicio del formato (capturada manualmente)
            $table->date('fecha_inicio')->nullable()->comment('Fecha de inicio registrada en el encabezado');

            // Observaciones generales del reporte (máx. 250 caracteres)
            $table->string('observaciones', 250)->nullable()->comment('Observaciones generales del reporte');

            // Tipo de formato detectado automáticamente según la clase
            $table->enum('formato', ['moldes', 'bombillos'])
                  ->comment('Formato: moldes = grid simple | bombillos = grid con 90° y LP');

            $table->boolean('enviado')->default(false)->comment('Indica si el reporte ya fue enviado por correo');
            $table->integer('pdf_generado_count')->default(0)->comment('Contador de veces que se ha generado el PDF');

            $table->timestamps();
            $table->softDeletes();

            // Índice para búsquedas frecuentes por OT y clase
            $table->index(['ot_id', 'clase'], 'salida_molduras_ot_clase_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salida_molduras');
    }
};
