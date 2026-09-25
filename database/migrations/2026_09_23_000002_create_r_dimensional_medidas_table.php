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
        if (!Schema::hasTable('r_dimensional_medidas')) {
            Schema::create('r_dimensional_medidas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('r_dimensional_id');
                $table->string('numero_pieza')->default('1');
                $table->string('c')->nullable();
                $table->string('cuello')->nullable();
                $table->string('altu_1')->nullable();
                $table->string('talon')->nullable();
                $table->string('b')->nullable();
                $table->string('k')->nullable();
                $table->string('l')->nullable();
                $table->string('f2')->nullable();
                $table->string('e2')->nullable();
                $table->string('d')->nullable();
                $table->string('a')->nullable();
                $table->string('volumen')->nullable();
                $table->json('datos_extra')->nullable();
                $table->timestamps();

                $table->foreign('r_dimensional_id')
                    ->references('id')
                    ->on('r_dimensionales')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_dimensional_medidas');
    }
};
