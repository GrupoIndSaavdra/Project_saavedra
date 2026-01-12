<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('soldadura_liberacion')) {
            Schema::create('soldadura_liberacion', function (Blueprint $table) {
                $table->id();

                // Operador
                $table->unsignedBigInteger('id_operador');
                $table->foreign('id_operador')->references('id')->on('users')->onDelete('cascade');

                // Fecha de entrega
                $table->date('fecha_entrega');

                // Datos de la soldadura
                $table->string('nombre'); // nombre de la soldadura
                $table->string('lote');   // lote de la soldadura

                // Cantidad entregada
                $table->decimal('cantidad', 10, 2);
                
                // Referencia al QR generado (opcional)
                $table->unsignedBigInteger('qr_generado_id')->nullable();
                $table->foreign('qr_generado_id')->references('id')->on('soldadura_qr_generados')->onDelete('set null');
                
                // Estado de la liberación
                $table->enum('estado', ['generado', 'liberado', 'cancelado'])->default('liberado');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soldadura_liberacion');
    }
};