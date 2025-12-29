<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('soldadura_registro', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_ingreso');
            $table->string('nombre');
            $table->string('lote');
            $table->decimal('kilos', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldadura_registro');
    }
};