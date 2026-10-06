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
        Schema::create('r_visual_medidas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('r_visual_id')->index();
            $table->string('numero_pieza')->default('1');
            $table->string('temp_agua')->default('20°');
            $table->string('vol_real')->nullable();
            $table->string('dif_vs_vol_ideal')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('r_visual_id')
                ->references('id')
                ->on('r_visuales')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_visual_medidas');
    }
};
