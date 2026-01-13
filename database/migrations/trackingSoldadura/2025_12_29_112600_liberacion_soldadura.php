<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('soldadura_liberaciones', function (Blueprint $table) {
            $table->id();
            $table->string('id_unico', 30)->unique(); // ID del bote + matrícula operador
            $table->unsignedBigInteger('bote_id');
            $table->foreign('bote_id')->references('id')->on('soldadura_botes_individuales')->onDelete('cascade');
            $table->unsignedBigInteger('id_operador');
            $table->foreign('id_operador')->references('id')->on('users')->onDelete('cascade');
            $table->date('fecha_liberacion');
            $table->string('nombre');
            $table->string('lote');
            $table->decimal('peso', 8, 2);
            $table->string('numero_factura');
            $table->unsignedBigInteger('id_liberador');
            $table->foreign('id_liberador')->references('id')->on('users')->onDelete('cascade');
            $table->enum('estado', ['liberado'])->default('liberado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldadura_liberaciones');
    }
};