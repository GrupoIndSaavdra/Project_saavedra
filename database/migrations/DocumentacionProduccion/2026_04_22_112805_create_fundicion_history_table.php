<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla opcional de registro histórico de OTs que han tenido
     * dibujos de fundición creados en el sistema.
     */
    public function up(): void
    {
        Schema::create('fundicion_history', function (Blueprint $table) {
            $table->id();

            // Nombre de la Orden de Trabajo (carpeta raíz)
            $table->string('ot', 100);
            $table->enum('status', ['activa', 'inactiva'])->default('activa');
            $table->json('ayudas_config')->nullable();
            $table->timestamp('alert_sent_at')->nullable();
            $table->json('almacen_archivos')->nullable();

            $table->timestamps();

            $table->unique('ot', 'uq_fundicion_ot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fundicion_history');
    }
};
