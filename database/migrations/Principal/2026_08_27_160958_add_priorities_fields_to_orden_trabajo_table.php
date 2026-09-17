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
        Schema::table('orden_trabajo', function (Blueprint $table) {
            $table->string('fecha_real')->nullable();
            $table->string('forma_grabados')->nullable();
            $table->string('entrega_tecamac')->nullable();
            $table->text('observaciones_prioridad')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_trabajo', function (Blueprint $table) {
            $table->dropColumn(['fecha_real', 'forma_grabados', 'entrega_tecamac', 'observaciones_prioridad']);
        });
    }
};
