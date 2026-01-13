<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('soldadura_botes_individuales', function (Blueprint $table) {
            $table->id();
            $table->string('id_unico', 25)->unique(); // ID del lote + número incremental
            $table->unsignedBigInteger('lote_id');
            $table->foreign('lote_id')->references('id')->on('soldadura_lotes')->onDelete('cascade');
            $table->string('nombre');
            $table->string('lote');
            $table->decimal('peso', 8, 2)->default(5.00); // Peso fijo de 5kg
            $table->string('numero_factura');
            $table->integer('numero_bote'); // Número incremental del bote
            $table->enum('estado', ['en_camino', 'en_planta', 'liberado'])->default('en_camino');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldadura_botes_individuales');
    }
};