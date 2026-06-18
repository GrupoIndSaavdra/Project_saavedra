<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcialidades_ot', function (Blueprint $table) {
            $table->id();
            $table->string('id_ot', 50);
            $table->unsignedBigInteger('id_clase');
            $table->unsignedBigInteger('id_remision')->nullable();
            $table->integer('cantidad');
            $table->string('descripcion')->nullable();
            $table->date('fecha_recepcion');
            $table->string('registrado_por')->nullable(); // matrícula del usuario
            $table->timestamps();

            $table->foreign('id_clase')->references('id')->on('clases')->onDelete('cascade');
            $table->foreign('id_remision')->references('id')->on('remisiones_ot')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcialidades_ot');
    }
};
