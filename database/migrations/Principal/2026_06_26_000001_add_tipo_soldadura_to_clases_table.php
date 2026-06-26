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
        if (Schema::hasTable('clases') && !Schema::hasColumn('clases', 'tipo_soldadura')) {
            Schema::table('clases', function (Blueprint $table) {
                $table->string('tipo_soldadura')->nullable()->after('composicion_quimica');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('clases') && Schema::hasColumn('clases', 'tipo_soldadura')) {
            Schema::table('clases', function (Blueprint $table) {
                $table->dropColumn('tipo_soldadura');
            });
        }
    }
};
