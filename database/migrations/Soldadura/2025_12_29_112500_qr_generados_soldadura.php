<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('soldadura_qr_generados', function (Blueprint $table) {
            $table->id();
            
            // Operador
            $table->unsignedBigInteger('id_operador');
            $table->foreign('id_operador')->references('id')->on('users')->onDelete('cascade');
            
            // Fecha de generación
            $table->date('fecha_generacion');
            
            // Datos de la soldadura
            $table->string('nombre');
            $table->string('lote');
            
            // Cantidad de este QR específico
            $table->decimal('kilos', 8, 2);
            
            // Contenido del QR (para validación)
            $table->text('qr_content');
            
            // Estado del QR
            $table->enum('estado', ['generado', 'liberado', 'cancelado'])->default('generado');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldadura_qr_generados');
    }
};