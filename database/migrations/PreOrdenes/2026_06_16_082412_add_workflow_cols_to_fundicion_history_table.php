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
            $table->boolean('dibujos_vistos_almacen')->default(false);
            $table->boolean('pre_orden_autorizada')->default(false);
            $table->boolean('alerta_calidad_sent')->default(false);
            $table->boolean('documentos_revisados_calidad')->default(false);
            $table->boolean('alerta_almacen_2_sent')->default(false);
            $table->boolean('documentos_vistos_almacen_2')->default(false);
            $table->boolean('documentos_firmados_cargados')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fundicion_history', function (Blueprint $table) {
            $table->dropColumn([
                'dibujos_vistos_almacen',
                'pre_orden_autorizada',
                'alerta_calidad_sent',
                'documentos_revisados_calidad',
                'alerta_almacen_2_sent',
                'documentos_vistos_almacen_2',
                'documentos_firmados_cargados'
            ]);
        });
    }
};
