<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('procesos')) {
            Schema::table('procesos', function (Blueprint $table) {
                if (!Schema::hasColumn('procesos', 'primeraOperacionCabezaSoplo')) {
                    $table->integer('primeraOperacionCabezaSoplo')->default(0);
                }
                if (!Schema::hasColumn('procesos', 'segundaOperacionCabezaSoplo')) {
                    $table->integer('segundaOperacionCabezaSoplo')->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procesos', function (Blueprint $table) {
            $table->dropColumn('primeraOperacionCabezaSoplo');
            $table->dropColumn('segundaOperacionCabezaSoplo');
        });
    }
};
