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
        Schema::table('pre_ordenes_fundicion', function (Blueprint $table) {
            $table->dropUnique('uq_preorden_ot');
            $table->unique(['ot', 'proveedor'], 'uq_preorden_ot_proveedor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_ordenes_fundicion', function (Blueprint $table) {
            $table->dropUnique('uq_preorden_ot_proveedor');
            $table->unique('ot', 'uq_preorden_ot');
        });
    }
};
