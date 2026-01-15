<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * EVENTO: Recepción de bote en planta
     * Registra el momento en que un bote llega a la planta
     */
    public function up(): void
    {
        Schema::create('soldadura_recepciones_planta', function (Blueprint $table) {
            $table->id();

            // Foreign key a soldadura_botes
            $table->unsignedBigInteger('bote_id');
            $table->foreign('bote_id')->references('id')->on('soldadura_botes')->onDelete('cascade');
            // Foreign key a users (personal de almacén - perfil 5)
            $table->unsignedBigInteger('recibido_por');
            $table->foreign('recibido_por')->references('id')->on('users')->onDelete('cascade');

            $table->timestamp('fecha_hora_recepcion')->useCurrent();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices
            $table->index('fecha_hora_recepcion');
            $table->unique('bote_id'); // Un bote solo puede ser recibido una vez
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldadura_recepciones_planta');
    }
};