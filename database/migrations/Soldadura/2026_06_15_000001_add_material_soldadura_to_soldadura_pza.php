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
        Schema::table('soldadura_pza', function (Blueprint $table) {
            if (!Schema::hasColumn('soldadura_pza', 'material_soldadura')) {
                $table->string('material_soldadura')->nullable()->after('tipo_soldadura');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soldadura_pza', function (Blueprint $table) {
            if (Schema::hasColumn('soldadura_pza', 'material_soldadura')) {
                $table->dropColumn('material_soldadura');
            }
        });
    }
};
