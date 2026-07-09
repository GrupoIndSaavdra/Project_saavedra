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
        Schema::table('tratamiento_termicos', function (Blueprint $table) {
            $table->integer('cantidad')->default(0)->after('archivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tratamiento_termicos', function (Blueprint $table) {
            $table->dropColumn('cantidad');
        });
    }
};
