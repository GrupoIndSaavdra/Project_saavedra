<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna calidad_revision_status a fundicion_history
     * para representar el estado de la máquina de estados completa.
     *
     * null        => Sin acción pendiente de Calidad
     * pendiente   => Almacén envió pre-orden o confirmó modelo; Calidad debe revisar
     * aprobado    => Calidad aprobó la liberación
     * rechazado   => Calidad rechazó la liberación
     */
    public function up(): void
    {
        Schema::table('fundicion_history', function (Blueprint $table) {
            $table->string('calidad_revision_status', 30)
                ->nullable()
                ->after('pre_orden_sent')
                ->comment('null | pendiente | aprobado | rechazado');
        });
    }

    public function down(): void
    {
        Schema::table('fundicion_history', function (Blueprint $table) {
            $table->dropColumn('calidad_revision_status');
        });
    }
};
