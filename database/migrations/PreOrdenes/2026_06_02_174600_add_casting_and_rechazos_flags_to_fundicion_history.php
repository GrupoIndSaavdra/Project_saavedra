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
        Schema::table('fundicion_history', function (Blueprint $table) {
            $table->boolean('casting_pdf_generated')->default(false)->after('calidad_revision_status');
            $table->boolean('rechazos_procesados')->default(false)->after('casting_pdf_generated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fundicion_history', function (Blueprint $table) {
            $table->dropColumn(['casting_pdf_generated', 'rechazos_procesados']);
        });
    }
};
