<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * ENTIDAD PRINCIPAL: Lote de soldadura
     * Contiene la información del lote completo que ingresa
     */
    public function up(): void
    {
        Schema::create('soldadura_lotes', function (Blueprint $table) {
            $table->id();
            $table->string('matricula', 20)->unique()->comment('ID único generado automáticamente');
            $table->string('nombre')->comment('Nombre/tipo de soldadura');
            $table->string('lote')->comment('Número de lote del proveedor');
            $table->string('numero_factura')->comment('Número de factura');
            $table->decimal('peso_total_kg', 10, 2)->comment('Peso total del lote en kilogramos');
            $table->date('fecha_ingreso')->comment('Fecha de ingreso del lote');
            $table->integer('botes_generados')->default(0)->comment('Cantidad de botes de 5kg generados');
            $table->timestamps();
            
            // Índices para búsquedas
            $table->index('fecha_ingreso');
            $table->index('numero_factura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldadura_lotes');
    }
};