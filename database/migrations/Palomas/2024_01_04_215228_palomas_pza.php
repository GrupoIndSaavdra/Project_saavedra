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
        if (!Schema::hasTable('palomas_pza')) {
            Schema::create('palomas_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->decimal('anchoPaloma', 8, 3)->nullable();
                $table->decimal('gruesoPaloma', 8, 3)->nullable();
                $table->decimal('profundidadPaloma', 8, 3)->nullable();
                $table->decimal('rebajeLlanta', 8, 3)->nullable();
                $table->string('error')->nullable();
                $table->string('observaciones')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('palomas');

                $table->unique('id_pza');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('palomas_pza');
    }
};
