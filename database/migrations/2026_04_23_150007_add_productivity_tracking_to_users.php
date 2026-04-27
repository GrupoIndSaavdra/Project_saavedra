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
        Schema::table('users', function (Blueprint $table) {
            $table->string('prod_status')->default('none'); // none, welcome, form, machining
            $table->timestamp('prod_start_at')->nullable();
            $table->string('prod_locked_type')->nullable(); // inicio, formulario, produccion
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['prod_status', 'prod_start_at', 'prod_locked_type']);
        });
    }
};
