<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * EVENTO: Liberación de bote a operador
     * Registra cuando un bote es entregado a un operador para uso
     */
    public function up(): void
    {
        Schema::create('soldadura_liberaciones', function (Blueprint $table) {
            $table->id();

            // Foreign key a soldadura_botes
            $table->unsignedBigInteger('bote_id');
            $table->foreign('bote_id')->references('id')->on('soldadura_botes')->onDelete('cascade');

            // Foreign key a users (operador de planta - perfil 2)
            $table->unsignedBigInteger('operador_id');
            $table->foreign('operador_id')->references('id')->on('users')->onDelete('cascade');

            // Foreign key a users (personal de almacén - perfil 5)
            $table->unsignedBigInteger('liberado_por');
            $table->foreign('liberado_por')->references('id')->on('users')->onDelete('cascade');

            $table->string('matricula_liberacion', 50)->unique();
            $table->timestamp('fecha_hora_liberacion')->useCurrent();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices
            $table->index('fecha_hora_liberacion');
            $table->unique('bote_id'); // Un bote solo puede ser liberado una vez
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldadura_liberaciones');
    }
};