<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('clases', 'material')) {
            Schema::table('clases', function (Blueprint $table) {
                $table->string('material')->nullable()->after('tamanio');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('clases', 'material')) {
            Schema::table('clases', function (Blueprint $table) {
                $table->dropColumn('material');
            });
        }
    }
};
