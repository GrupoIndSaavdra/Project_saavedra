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
        if (!Schema::hasTable('rebajes_pza')) {
            Schema::create('rebajes_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->decimal('rebaje1', 8, 3)->nullable();
                $table->decimal('rebaje2', 8, 3)->nullable();
                $table->decimal('rebaje3', 8, 3)->nullable();
                $table->decimal('profundidad_bordonio', 8, 3)->nullable();
                $table->decimal('vena1', 8, 3)->nullable();
                $table->decimal('vena2', 8, 3)->nullable();
                $table->decimal('simetria', 8, 3)->nullable();
                $table->string('error')->nullable();
                $table->string('observaciones')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('rebajes');

                $table->unique('id_pza');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rebajes_pza');
    }
};
