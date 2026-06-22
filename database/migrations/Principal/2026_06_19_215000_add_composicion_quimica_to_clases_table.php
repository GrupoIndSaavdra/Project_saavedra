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
        if (Schema::hasTable('clases') && !Schema::hasColumn('clases', 'composicion_quimica')) {
            Schema::table('clases', function (Blueprint $table) {
                $table->string('composicion_quimica')->nullable()->after('tamanio');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('clases') && Schema::hasColumn('clases', 'composicion_quimica')) {
            Schema::table('clases', function (Blueprint $table) {
                $table->dropColumn('composicion_quimica');
            });
        }
    }
};
