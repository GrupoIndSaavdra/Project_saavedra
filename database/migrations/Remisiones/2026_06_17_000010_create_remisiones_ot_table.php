<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remisiones_ot', function (Blueprint $table) {
            $table->id();
            $table->string('id_ot', 50);
            $table->unsignedBigInteger('id_clase');
            $table->string('filename');
            $table->string('path');
            $table->string('descripcion')->nullable();
            $table->string('uploaded_by')->nullable(); // matrícula del usuario que subió
            $table->boolean('visible')->default(true);
            $table->timestamps();

            $table->foreign('id_clase')->references('id')->on('clases')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remisiones_ot');
    }
};
