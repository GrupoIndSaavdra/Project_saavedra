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
        Schema::create('r_interno_salidas_pdfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('r_interno_salida_id')->constrained('r_interno_salidas')->onDelete('cascade');
            $table->integer('version');
            $table->string('nombre_archivo', 255);
            $table->text('ruta');
            $table->foreignId('creado_por')->constrained('users', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_interno_salidas_pdfs');
    }
};
