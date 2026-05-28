<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('liberacion_modelos_fundicion', function (Blueprint $table) {
            $table->string('decision', 30)->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('liberacion_modelos_fundicion', function (Blueprint $table) {
            $table->dropColumn('decision');
        });
    }
};
