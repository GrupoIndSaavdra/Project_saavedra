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
        Schema::table('remisiones_ot', function (Blueprint $table) {
            $table->boolean('visible')->default(true)->after('uploaded_by');
        });

        Schema::table('parcialidades_ot', function (Blueprint $table) {
            $table->unsignedBigInteger('id_remision')->nullable()->after('id_clase');
            $table->foreign('id_remision')->references('id')->on('remisiones_ot')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcialidades_ot', function (Blueprint $table) {
            $table->dropForeign(['id_remision']);
            $table->dropColumn('id_remision');
        });

        Schema::table('remisiones_ot', function (Blueprint $table) {
            $table->dropColumn('visible');
        });
    }
};
