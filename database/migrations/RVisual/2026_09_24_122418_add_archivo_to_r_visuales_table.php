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
        Schema::table('r_visuales', function (Blueprint $table) {
            $table->string('archivo_ruta')->nullable()->after('observaciones_generales');
            $table->string('archivo_nombre')->nullable()->after('archivo_ruta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('r_visuales', function (Blueprint $table) {
            $table->dropColumn(['archivo_ruta', 'archivo_nombre']);
        });
    }
};
