<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración segura para añadir las columnas del módulo PreOrdenes
 * a la tabla `fundicion_history` en servidores que ya tienen datos.
 *
 * Usa hasColumn() en cada campo para que sea idempotente:
 * si la columna ya existe no falla, simplemente la omite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fundicion_history', function (Blueprint $table) {

            // 1. tiene_modelo
            if (!Schema::hasColumn('fundicion_history', 'tiene_modelo')) {
                $table->boolean('tiene_modelo')->default(false)->after('status');
            }

            // 2. pre_orden_sent
            if (!Schema::hasColumn('fundicion_history', 'pre_orden_sent')) {
                $table->boolean('pre_orden_sent')->default(false)->after('tiene_modelo');
            }

            // 3. pre_orden_email_sent
            if (!Schema::hasColumn('fundicion_history', 'pre_orden_email_sent')) {
                $table->boolean('pre_orden_email_sent')->default(false)->after('pre_orden_sent');
            }

            // 4. calidad_revision_status
            if (!Schema::hasColumn('fundicion_history', 'calidad_revision_status')) {
                $table->string('calidad_revision_status', 30)
                    ->nullable()
                    ->comment('null | pendiente | aprobado | rechazado | mixto | casting_aprobado')
                    ->after('pre_orden_email_sent');
            }

            // 5. casting_pdf_generated
            if (!Schema::hasColumn('fundicion_history', 'casting_pdf_generated')) {
                $table->boolean('casting_pdf_generated')->default(false)->after('calidad_revision_status');
            }

            // 6. rechazos_procesados
            if (!Schema::hasColumn('fundicion_history', 'rechazos_procesados')) {
                $table->boolean('rechazos_procesados')->default(false)->after('casting_pdf_generated');
            }

            // 7. ayudas_config
            if (!Schema::hasColumn('fundicion_history', 'ayudas_config')) {
                $table->json('ayudas_config')->nullable()->after('rechazos_procesados');
            }

            // 8. alert_sent_at
            if (!Schema::hasColumn('fundicion_history', 'alert_sent_at')) {
                $table->timestamp('alert_sent_at')->nullable()->after('ayudas_config');
            }

            // 9. almacen_archivos
            if (!Schema::hasColumn('fundicion_history', 'almacen_archivos')) {
                $table->json('almacen_archivos')->nullable()->after('alert_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fundicion_history', function (Blueprint $table) {
            $cols = [
                'tiene_modelo', 'pre_orden_sent', 'pre_orden_email_sent',
                'calidad_revision_status', 'casting_pdf_generated',
                'rechazos_procesados', 'ayudas_config',
                'alert_sent_at', 'almacen_archivos',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('fundicion_history', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
