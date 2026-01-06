<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('qr_generados', function (Blueprint $table) {
            $table->id();
            $table->string('id_operador');
            $table->unsignedBigInteger('id_soldadura');
            $table->date('fecha_entrega');
            $table->decimal('cantidad_entregada', 8, 2);
            $table->text('contenido_qr');
            $table->string('archivo_qr')->nullable();
            $table->timestamps();
            
            // Índices para búsquedas
            $table->index('id_operador');
            $table->index('id_soldadura');
            $table->index('fecha_entrega');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_generados');
    }
};