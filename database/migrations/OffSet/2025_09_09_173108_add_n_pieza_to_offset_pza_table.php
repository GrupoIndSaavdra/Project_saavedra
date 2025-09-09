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
        if (!Schema::hasColumn('offset_pza', 'n_pieza')) {
            Schema::table('offset_pza', function (Blueprint $table) {
                $table->string('n_pieza')->nullable()->before('n_juego');
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('offset_pza', 'n_pieza')) {
            Schema::table('offset_pza', function (Blueprint $table) {
                $table->dropColumn('n_pieza');
            });
        }
    }
};
