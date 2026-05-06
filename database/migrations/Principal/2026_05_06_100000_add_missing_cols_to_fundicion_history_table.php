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
            if (!Schema::hasColumn('fundicion_history', 'status')) {
                $table->enum('status', ['activa', 'inactiva'])->default('activa')->after('ot');
            }
            if (!Schema::hasColumn('fundicion_history', 'ayudas_config')) {
                $table->json('ayudas_config')->nullable()->after('status');
            }
            if (!Schema::hasColumn('fundicion_history', 'alert_sent_at')) {
                $table->timestamp('alert_sent_at')->nullable()->after('ayudas_config');
            }
            if (!Schema::hasColumn('fundicion_history', 'almacen_archivos')) {
                $table->json('almacen_archivos')->nullable()->after('alert_sent_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fundicion_history', function (Blueprint $table) {
            if (Schema::hasColumn('fundicion_history', 'almacen_archivos')) {
                $table->dropColumn('almacen_archivos');
            }
            if (Schema::hasColumn('fundicion_history', 'alert_sent_at')) {
                $table->dropColumn('alert_sent_at');
            }
            if (Schema::hasColumn('fundicion_history', 'ayudas_config')) {
                $table->dropColumn('ayudas_config');
            }
            if (Schema::hasColumn('fundicion_history', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
