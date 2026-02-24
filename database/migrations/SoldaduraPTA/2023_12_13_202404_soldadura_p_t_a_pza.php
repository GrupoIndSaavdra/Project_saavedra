<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('soldaduraPTA_pza')) {
            Schema::create('soldaduraPTA_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('estado')->default(0);
                $table->string('n_juego');
                $table->string('n_pieza')->nullable();   // ej: '1M', '1H' — identificador por mitad
                $table->decimal('temp_calentado', 8, 3)->nullable();
                $table->decimal('temp_dispositivo', 8, 3)->nullable();
                $table->char('limpieza')->nullable();
                $table->string('error')->nullable();
                $table->string('observaciones')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('soldaduraPTA');

                $table->unique('id_pza');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soldaduraPTA_pza');
    }
};
