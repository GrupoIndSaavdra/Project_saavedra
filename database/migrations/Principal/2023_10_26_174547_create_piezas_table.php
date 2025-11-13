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
        if (!Schema::hasTable('piezas')) {
            Schema::create('piezas', function (Blueprint $table) {
                $table->id();
                $table->string('id_ot');
                $table->unsignedBigInteger('id_clase');
                $table->string('n_pieza');
                $table->string('id_operador');
                $table->integer('maquina');
                $table->string('proceso');
                $table->string('error');
                $table->integer('liberacion')->default(0);
                $table->dateTime('fecha_liberacion')->nullable();
                $table->string('user_liberacion')->nullable();
                $table->string('observacion_liberacion')->nullable();
                $table->foreign('id_ot')->references('id')->on('orden_trabajo');
                $table->foreign('id_clase')->references('id')->on('clases');
                $table->foreign('id_operador')->references('matricula')->on('users');
                $table->foreign('user_liberacion')->references('matricula')->on('users');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piezas');
    }
};
