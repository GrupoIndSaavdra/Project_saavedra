<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * ENTIDAD: Bote individual de soldadura
     * Cada bote representa una subdivisión de 5kg del lote original
     */
    public function up(): void
    {
        Schema::create('soldadura_botes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lote_id');
            $table->foreign('lote_id')->references('id')->on('soldadura_lotes')->onDelete('cascade');

            $table->string('matricula', 25)->unique();
            $table->integer('numero_bote');
            $table->decimal('peso_kg', 8, 2)->default(5.00);
            $table->enum('estado', ['pendiente', 'en_transito', 'en_planta', 'liberado'])
                ->default('pendiente');
            $table->timestamps();

            // Índices
            $table->index(['lote_id', 'numero_bote']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldadura_botes');
    }
};