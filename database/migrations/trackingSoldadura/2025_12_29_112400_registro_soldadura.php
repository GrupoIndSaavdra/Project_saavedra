<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('soldadura_lotes', function (Blueprint $table) {
            $table->id();
            $table->string('id_unico', 20)->unique();
            $table->date('fecha_ingreso');
            $table->string('nombre');
            $table->string('lote');
            $table->decimal('kilos_totales', 8, 2);
            $table->string('numero_factura');
            $table->integer('botes_generados')->default(0);
            $table->enum('estado', ['registrado', 'procesado'])->default('registrado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldadura_lotes');
    }
};