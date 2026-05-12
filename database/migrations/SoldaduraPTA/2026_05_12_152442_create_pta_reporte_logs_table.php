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
        Schema::create('pta_reporte_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ot_id');            // OT relacionada
            $table->unsignedBigInteger('clase_id');         // Clase relacionada
            $table->string('ot_nombre')->nullable();         // Etiqueta legible OT (ej. "OT #5 — Bombillo")
            $table->string('clase_nombre')->nullable();      // Nombre de la clase
            $table->string('destinatario');                  // Email al que se envió
            $table->string('estado', 20)->default('enviado'); // 'enviado' | 'error'
            $table->text('mensaje_error')->nullable();       // Detalle del error si hubo
            $table->unsignedBigInteger('enviado_por')->nullable(); // matricula del usuario
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pta_reporte_logs');
    }
};