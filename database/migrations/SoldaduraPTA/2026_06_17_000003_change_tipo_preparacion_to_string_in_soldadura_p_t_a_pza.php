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
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {
            $table->string('tipo_preparacion', 50)->nullable()->change();
            $table->string('p2_tipo_preparacion', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {
            // Reverting back to tinyInteger is not always fully safe if alphanumeric data exists,
            // but we define the down path nonetheless.
            $table->tinyInteger('tipo_preparacion')->nullable()->change();
            $table->tinyInteger('p2_tipo_preparacion')->nullable()->change();
        });
    }
};
