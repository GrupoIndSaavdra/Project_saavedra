<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de registro de Pre-Órdenes de Fabricación de Modelos (4ALM-17).
     * Guarda la cabecera y las filas detalladas de cada pre-orden generada,
     * vinculada a la OT correspondiente del módulo de Fundición.
     */
    public function up(): void
    {
        Schema::create('pre_ordenes_fundicion', function (Blueprint $table) {
            $table->id();

            // Relación con la OT del historial de Fundición
            $table->string('ot', 100)->index();

            // Datos de cabecera del formulario
            $table->string('folio', 30);
            $table->string('proveedor', 200);
            $table->date('fecha_creacion');
            $table->date('fecha_entrega')->nullable();
            $table->string('moldura', 200)->nullable();
            $table->text('observaciones')->nullable();

            // Filas dinámicas de modelos solicitados (JSON)
            // Estructura: [{ tipo_modelo, impresiones, cantidad, id_clase, clase_nombre, codigo_modelo }, ...]
            $table->json('filas');

            // Control de versiones y estado del PDF
            $table->string('pdf_filename', 300)->nullable()->comment('Nombre del último PDF generado');
            $table->unsignedInteger('version')->default(1)->comment('Número de veces que se ha regenerado el PDF');

            // Usuario que realizó la última modificación
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_nombre', 150)->nullable();

            $table->timestamps();

            // Una OT y proveedor tienen una sola pre-orden activa (se sobreescribe al editar)
            $table->unique(['ot', 'proveedor'], 'uq_preorden_ot_proveedor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_ordenes_fundicion');
    }
};
