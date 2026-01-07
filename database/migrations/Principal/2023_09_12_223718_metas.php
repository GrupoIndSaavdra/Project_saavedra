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
        if (!Schema::hasTable('metas')) {
            Schema::create('metas', function (Blueprint $table) {
                $table->id();
                $table->string('id_ot');
                $table->string('id_usuario');
                $table->date('fecha');
                $table->time('h_inicio');
                $table->time('h_termino');
                $table->integer('t_estandar')->nullable();
                $table->float('meta')->nullable();
                $table->float('resultado')->nullable();
                $table->string('maquina')->nullable();
                $table->unsignedBigInteger('id_clase')->nullable();
                $table->unsignedBigInteger('id_proceso')->nullable();
                $table->string('proceso');
                $table->timestamps();
                $table->foreign('id_usuario')->references('matricula')->on('users');
                $table->foreign('id_ot')->references('id')->on('orden_trabajo');
                $table->foreign('id_clase')->references('id')->on('clases');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};