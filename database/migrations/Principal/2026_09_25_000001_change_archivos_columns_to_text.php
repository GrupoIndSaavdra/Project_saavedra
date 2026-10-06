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
        Schema::table('r_dimensionales', function (Blueprint $table) {
            $table->text('archivo_excel_ruta')->nullable()->change();
            $table->text('archivo_excel_nombre')->nullable()->change();
        });

        Schema::table('r_visuales', function (Blueprint $table) {
            $table->text('archivo_ruta')->nullable()->change();
            $table->text('archivo_nombre')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('r_dimensionales', function (Blueprint $table) {
            $table->string('archivo_excel_ruta', 255)->nullable()->change();
            $table->string('archivo_excel_nombre', 255)->nullable()->change();
        });

        Schema::table('r_visuales', function (Blueprint $table) {
            $table->string('archivo_ruta', 255)->nullable()->change();
            $table->string('archivo_nombre', 255)->nullable()->change();
        });
    }
};
