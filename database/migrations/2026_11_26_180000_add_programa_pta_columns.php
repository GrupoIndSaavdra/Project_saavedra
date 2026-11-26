<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('soldadurapta')) {
            Schema::table('soldadurapta', function (Blueprint $table) {
                if (! Schema::hasColumn('soldadurapta', 'programa_pta')) {
                    $table->string('programa_pta', 500)->nullable()->after('id_ot');
                }
            });
        }

        if (Schema::hasTable('soldadurapta_pza')) {
            Schema::table('soldadurapta_pza', function (Blueprint $table) {
                if (! Schema::hasColumn('soldadurapta_pza', 'programa_pta')) {
                    $table->string('programa_pta', 500)->nullable()->after('observaciones');
                }
                if (! Schema::hasColumn('soldadurapta_pza', 'p2_programa_pta')) {
                    $table->string('p2_programa_pta', 500)->nullable()->after('p2_observaciones');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('soldadurapta') && Schema::hasColumn('soldadurapta', 'programa_pta')) {
            Schema::table('soldadurapta', function (Blueprint $table) {
                $table->dropColumn('programa_pta');
            });
        }

        if (Schema::hasTable('soldadurapta_pza')) {
            Schema::table('soldadurapta_pza', function (Blueprint $table) {
                if (Schema::hasColumn('soldadurapta_pza', 'p2_programa_pta')) {
                    $table->dropColumn('p2_programa_pta');
                }
                if (Schema::hasColumn('soldadurapta_pza', 'programa_pta')) {
                    $table->dropColumn('programa_pta');
                }
            });
        }
    }
};

