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
        Schema::table('system_logs', function (Blueprint $table) {
            $table->index('user_matricula');
            $table->index('action');
            $table->index('ot');
            $table->index('clase');
            $table->index('proceso');
            $table->index('created_at'); // Vital para búsquedas por rango de fecha
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->dropIndex(['user_matricula']);
            $table->dropIndex(['action']);
            $table->dropIndex(['ot']);
            $table->dropIndex(['clase']);
            $table->dropIndex(['proceso']);
            $table->dropIndex(['created_at']);
        });
    }
};
