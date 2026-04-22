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
        if (!Schema::hasTable('CandadoObturador_tolerancia')) {
            Schema::create('CandadoObturador_tolerancia', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso')->unique();
                $table->decimal('altura', 8, 3)->nullable();
                $table->decimal('alturaCandado1', 8, 3)->nullable();
                $table->decimal('alturaCandado2', 8, 3)->nullable();
                $table->decimal('alturaAsientoObturador1', 8, 3)->nullable();
                $table->decimal('alturaAsientoObturador2', 8, 3)->nullable();
                $table->decimal('profundidadSoldadura1', 8, 3)->nullable();
                $table->decimal('profundidadSoldadura2', 8, 3)->nullable();
                $table->decimal('pushUp', 8, 3)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CandadoObturador_tolerancia');
    }
};
