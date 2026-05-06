<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro histórico de Clases creadas para Ayudas Visuales de Fundición.
     */
    public function up(): void
    {
        Schema::create('ayudas_visuales_fundicion_history', function (Blueprint $table) {
            $table->id();

            // En este módulo, el proceso es fijo "Fundicion"
            $table->string('proceso', 100)->default('Fundicion');

            // Nombre de la Clase
            $table->string('clase', 100);

            $table->timestamps();

            // Evitar duplicados
            $table->unique(['proceso', 'clase'], 'uq_ayudas_fun_hist_clase');

            $table->index('clase', 'idx_ayudas_fun_history_clase');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ayudas_visuales_fundicion_history');
    }
};
