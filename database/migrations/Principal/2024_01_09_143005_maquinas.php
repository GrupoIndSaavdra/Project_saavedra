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
        if (!Schema::hasTable('maquinas')) {
            Schema::create('maquinas', function (Blueprint $table) {
                $table->id();
                $table->string('maquina', 50);
                $table->string('proceso');
                $table->unsignedBigInteger('id_meta');
                $table->foreign('id_meta')->references('id')->on('metas');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maquinas');
    }
};
