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
        if (!Schema::hasColumn('tiempos_produccion', 'id_clase')) {
            Schema::table('tiempos_produccion', function (Blueprint $table) {
                $table->unsignedBigInteger('id_clase')->nullable()->after('id');
                $table->foreign('id_clase')->references('id')->on('clases')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tiempos_produccion', 'id_clase')) {
            Schema::table('tiempos_produccion', function (Blueprint $table) {
                $table->dropForeign(['id_clase']);
                $table->dropColumn('id_clase');
            });
        }
    }
};
