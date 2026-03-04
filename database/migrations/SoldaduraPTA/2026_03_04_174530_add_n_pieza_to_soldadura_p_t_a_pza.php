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
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {
            $table->string('n_pieza')->nullable()->after('n_juego')->comment("ej: '1M', '1H' — identificador por mitad");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {
            $table->dropColumn('n_pieza');
        });
    }
};