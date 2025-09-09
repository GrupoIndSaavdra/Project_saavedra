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
        if (!Schema::hasColumn('rebajes_pza', 'correcto')) {
            Schema::table('rebajes_pza', function (Blueprint $table) {
                $table->integer('correcto')->nullable()->after('id_proceso');
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('rebajes_pza', 'correcto')) {
            Schema::table('rebajes_pza', function (Blueprint $table) {
                $table->dropColumn('correcto');
            });
        }
    }
};
