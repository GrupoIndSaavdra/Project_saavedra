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
        Schema::table('r_dimensional_medidas', function (Blueprint $table) {
            if (!Schema::hasColumn('r_dimensional_medidas', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('al_cuello');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('r_dimensional_medidas', function (Blueprint $table) {
            $table->dropColumn('observaciones');
        });
    }
};
