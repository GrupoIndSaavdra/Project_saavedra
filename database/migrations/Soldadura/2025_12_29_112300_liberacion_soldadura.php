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
        if (!Schema::hasTable('soldadura_liberacion')) {
            Schema::create('soldadura_liberacion', function (Blueprint $table) {
                $table->id();
                
                // FK a soldadura_pza
                $table->unsignedBigInteger('id_pza');
                $table->foreign('id_pza')->references('id')->on('soldadura_pza')->onDelete('cascade');

                // FK a soldadura (proceso)
                $table->unsignedBigInteger('id_proceso');
                $table->foreign('id_proceso')->references('id')->on('soldadura')->onDelete('cascade');

                // Operador
                $table->unsignedBigInteger('id_operador');
                $table->foreign('id_operador')->references('id')->on('users')->onDelete('cascade');

                // Cantidad entregada al operador
                $table->decimal('cantidad', 10, 2);

                // Fecha de entrega
                $table->date('fecha_entrega');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soldadura_liberacion');
    }
};