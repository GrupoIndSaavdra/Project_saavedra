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
            if (!Schema::hasColumn('soldaduraPTA_pza', 'material_soldadura')) {
                $table->string('material_soldadura')->nullable()->after('limpieza');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {
            if (Schema::hasColumn('soldaduraPTA_pza', 'material_soldadura')) {
                $table->dropColumn('material_soldadura');
            }
        });
    }
};
