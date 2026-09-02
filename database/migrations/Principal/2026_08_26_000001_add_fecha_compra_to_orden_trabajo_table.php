<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade la columna `fecha_compra` a la tabla `orden_trabajo`.
     */
    public function up(): void
    {
        Schema::table('orden_trabajo', function (Blueprint $table) {
            $table->date('fecha_compra')->nullable()->after('prioridad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_trabajo', function (Blueprint $table) {
            $table->dropColumn('fecha_compra');
        });
    }
};
