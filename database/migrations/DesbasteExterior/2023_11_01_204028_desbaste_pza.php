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
        if (!Schema::hasTable('desbaste_pza')) {
            Schema::create('desbaste_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->string('n_pieza')->nullable();
                $table->decimal('diametro_mordaza', 8, 3)->nullable();
                $table->decimal('diametro_ceja', 8, 3)->nullable();
                $table->decimal('diametro_sufrideraExtra', 8, 3)->nullable();
                $table->decimal('simetria_ceja', 8, 3)->nullable();
                $table->decimal('simetria_mordaza', 8, 3)->nullable();
                $table->decimal('altura_ceja', 8, 3)->nullable();
                $table->decimal('altura_sufridera', 8, 3)->nullable();
                $table->string('observaciones')->nullable();
                $table->string('error')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('desbasteExterior');

                $table->unique('id_pza');
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desbaste_pza');
    }
};
