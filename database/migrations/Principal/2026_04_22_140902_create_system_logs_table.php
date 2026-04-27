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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_matricula')->nullable();
            $table->string('action'); // Ej: "Login", "Captura Medida", "Liberación"
            $table->text('details')->nullable(); // JSON o texto extra opcional
            $table->string('ot')->nullable();
            $table->string('clase')->nullable();
            $table->string('proceso')->nullable();
            $table->string('maquina')->nullable();
            $table->string('n_pieza')->nullable();
            $table->string('h_inicio')->nullable();
            $table->string('h_termino')->nullable();
            $table->integer('id_ot')->nullable();
            $table->integer('id_clase')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
