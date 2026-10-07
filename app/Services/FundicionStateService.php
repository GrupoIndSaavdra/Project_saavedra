<?php

namespace App\Services;

use App\Models\FundicionHistory;
use App\Models\LiberacionModeloFundicion;
use App\Models\PreOrdenFundicion;
use Illuminate\Support\Facades\Auth;

/**
 * Servicio para determinar el estado visual y de flujo (FSM) de una Orden de Trabajo
 * en el módulo de Fundición.
 */
class FundicionStateService
{
    /**
     * Resuelve el estado de una OT, devolviendo la configuración para la UI.
     * 
     * @param FundicionHistory $reg El registro de la OT.
     * @param FundicionHistory $targetReg El registro destino (útil para reprocesos).
     * @param array $aprobados Lista de clases aprobadas.
     * @param bool $isReproceso Indica si es una OT de reproceso (termina en _R\d+).
     * @return array Array asociativo con 'fsmState', 'icon', 'label', 'tooltip', 'borderColor', 'bgColor', 'textColor'
     */
    public static function resolverEstadoOT(FundicionHistory $reg, FundicionHistory $targetReg, array $aprobados, bool $isReproceso): array
    {
        $libStatus = $targetReg->calidad_revision_status ?? null;
        $fsmState = FundicionStateConstants::FSM_RECIBIDO;

        // FIX: Evitar que OTs de reproceso hereden el estado final "Casting Aprobado" de la OT original
        if ($isReproceso) {
            // Un reproceso no puede mostrar casting_aprobado de la OT original.
            // Solo es vÃ¡lido si hay un casting propio para esta OT de reproceso.
            $castingPdfPropio = PreOrdenFundicion::where('ot', $reg->ot)
                ->where('pdf_filename', 'LIKE', '%Casting%')
                ->exists();
            if ($libStatus === FundicionStateConstants::CASTING_APROBADO && !$castingPdfPropio) {
                $libStatus = null; // Reseteamos para que caiga en la lógica natural del reproceso
            }
        }

        $userPerfil = Auth::check() ? Auth::user()->perfil : null;

        $stateKey = self::determinarStateKey($reg, $targetReg, $aprobados, $isReproceso, $libStatus, $userPerfil);

        return self::getUIConfig($stateKey);
    }

    /**
     * Determina la clave de estado lógico (State Key) evaluando las condiciones del registro.
     */
    private static function determinarStateKey(FundicionHistory $reg, FundicionHistory $targetReg, array $aprobados, bool $isReproceso, ?string $libStatus, ?int $userPerfil): string
    {
        $isCalidadUser = in_array($userPerfil, [3, 4, '3', '4']);
        $alertaCalidadEnviada = self::alertaCalidadEnviada($targetReg);

        // 1. ENVIADO A PROVEEDOR (Casting Aprobado)
        if ($libStatus === 'casting_aprobado' || $libStatus === FundicionStateConstants::CASTING_APROBADO) {
            if (!$isReproceso || PreOrdenFundicion::where('ot', $reg->ot)->where('pdf_filename', 'LIKE', '%Casting%')->exists()) {
                return 'ENVIADO_PROVEEDOR';
            }
        }

        // 2. CASTING Y ESCANEO DE CASTING
        if ($targetReg->casting_pdf_generated) {
            return 'POR_ESCANEAR_CASTING'; // Representa PFC generado pero falta firmar/escanear/enviar
        }

        // 3. REPROCESO (Almacén procesÃ³ los rechazos de calidad y ya generÃ³ la OT hija)
        if ($reg->rechazos_procesados) {
            return count($aprobados) > 0 ? 'RECHAZOS_PROCESADOS_APROBADO' : 'RECHAZOS_PROCESADOS_RECHAZADO';
        }

        // 4. VEREDICTOS DE CALIDAD (Aprobado, Rechazado, Mixto)
        if (in_array($libStatus, [FundicionStateConstants::CALIDAD_APROBADO, FundicionStateConstants::CALIDAD_PARCIAL, 'aprobado', 'calidad_aprobado'])) {
            if ($alertaCalidadEnviada) {
                return $isCalidadUser ? 'EN_ALMACEN' : 'LIBERADO_ALMACEN';
            }
            return 'LDM_GENERADO';
        }
        if (in_array($libStatus, [FundicionStateConstants::CALIDAD_RECHAZADO, 'rechazado', 'calidad_rechazado'])) {
            if ($alertaCalidadEnviada) {
                return $isCalidadUser ? 'EN_ALMACEN' : 'RECHAZADO_ALMACEN';
            }
            return 'RDM_GENERADO';
        }
        if (in_array($libStatus, [FundicionStateConstants::CALIDAD_MIXTO, 'mixto', 'calidad_mixto'])) {
            if ($alertaCalidadEnviada) {
                return 'MIXTO_ALMACEN';
            }
            // LDM y RDM/SCAR ya generados en la misma OT, aún sin enviar la alerta
            return 'FORMATOS_MIXTOS';
        }

        // 5. EN REVISIÃN (Calidad está evaluando)
        if (in_array($libStatus, ['pendiente', 'revisando', 'calidad_parcial'])) {
            return 'CALIDAD_REVISANDO';
        }

        // 6. ACCIONES DE ALMACÃN ENVIADAS A CALIDAD
        // A) Correo Enviado
        if ($targetReg->pre_orden_email_sent) {
            return $isCalidadUser ? 'POR_LIBERAR_CALIDAD' : (!$targetReg->isAlmacenFullyProcessed() ? 'PROCESO_PARCIAL_CORREO' : 'CORREO_ENVIADO');
        }

        // B) Pre-Orden Generada pero NO enviada
        if ($targetReg->pre_orden_sent) {
            return $isCalidadUser ? 'PEND_CORREO_CALIDAD' : (!$targetReg->isAlmacenFullyProcessed() ? 'PROCESO_PARCIAL_PRE_ORDEN' : 'POR_ESCANEAR_PFM');
        }

        // C) Tengo Modelo Reportado
        if ($targetReg->tiene_modelo) {
            return $isCalidadUser ? 'PEND_CORREO_CALIDAD' : (!$targetReg->isAlmacenFullyProcessed() ? 'PROCESO_PARCIAL_MODELO' : 'TIENE_MODELO');
        }

        if ($isReproceso && in_array($libStatus, [null, 'pendiente']) && !$targetReg->tiene_modelo && !$targetReg->pre_orden_sent && !$targetReg->pre_orden_email_sent) {
            return 'REPROCESO_RECHAZADO';
        }

        // DEFAULT
        return $isCalidadUser ? 'PEND_ALMACEN_CALIDAD' : 'NUEVO';
    }

    /**
     * Indica si Calidad ya envió la alerta (correo) hacia Almacén para la OT.
     *
     * La columna `alerta_calidad_sent` no se escribe en ningún flujo, por lo que la fuente
     * real es `alerta_enviada` en las liberaciones: se marca al enviar la alerta, mientras que
     * las liberaciones con decisión y sin alerta son formatos generados pendientes de enviar.
     */
    private static function alertaCalidadEnviada(FundicionHistory $targetReg): bool
    {
        if ($targetReg->alerta_calidad_sent) {
            return true;
        }

        $liberaciones = LiberacionModeloFundicion::where('ot', $targetReg->ot)
            ->whereNotNull('decision')
            ->get(['alerta_enviada']);

        return $liberaciones->contains('alerta_enviada', true);
    }

    /**
     * Mapea la clave de estado a su configuración visual (UI) correspondiente.
     *
     * FUENTE ÃNICA DE VERDAD de iconos y colores. Las guÃ­as de estados
     * (almacen/partials/sidebar_legend.blade.php y calidad/partials/sidebar_legend.blade.php)
     * y los stateMachine.js de ambos módulos deben replicar EXACTAMENTE estos valores.
     *
     * Paleta (borde / fondo / texto) â un color distinto por estado visible:
     *   Nuevo .............. sky     #0ea5e9 / #f0f9ff / #0369a1  (Recibido.png)
     *   En Espera .......... gris    #a3a3a3 / #fafafa / #525252  (Espera.png)
     *   Por Escanear ....... cyan    #06b6d4 / #ecfeff / #0e7490  (Escanear-icon.png)
     *   Tengo Modelo ....... teal    #14b8a6 / #f0fdfa / #0f766e  (almacen.png)
     *   En Calidad ......... indigo  #6366f1 / #eef2ff / #4338ca  (enviando.png)
     *   Por Liberar ........ slate   #64748b / #f1f5f9 / #334155  (Recibido.png)
     *   Enviado a Proveedor  purple  #9333ea / #faf5ff / #7e22ce  (Proveedor.png)
     *   Reproceso .......... pink    #ec4899 / #fdf2f8 / #be185d  (Reproceso.png)
     *   Aprobado ........... green   #22c55e / #f0fdf4 / #15803d  (Aprobado.png)
     *   Rechazado .......... red     #ef4444 / #fef2f2 / #b91c1c  (Rechazado.png)
     *   Formatos Mixtos .... gold    #ca8a04 / #fef9c3 / #713f12  (Formatos_Mixtos_icon.png)
     *   Mixto .............. yellow  #eab308 / #fefce8 / #854d0e  (Mixto.png)
     *   En Revisión ........ amber   #f59e0b / #fffbeb / #b45309  (Revisando.png)
     *   Proceso Parcial .... orange  #f97316 / #fff7ed / #c2410c  (Revisando.png)
     */
    private static function getUIConfig(string $stateKey): array
    {
        return match ($stateKey) {
            'ENVIADO_PROVEEDOR' => [
                'fsmState' => FundicionStateConstants::FSM_ENVIADO_PROVEEDOR,
                'icon' => 'Proveedor.png',
                'label' => 'Enviado a Proveedor',
                'tooltip' => 'Pre-orden de casting enviada al proveedor, proceso finalizado',
                'borderColor' => '#9333ea',
                'bgColor' => '#faf5ff',
                'textColor' => '#7e22ce',
            ],
            'POR_ESCANEAR_CASTING' => [
                'fsmState' => FundicionStateConstants::FSM_POR_ESCANEAR,
                'icon' => 'PFC-icon.png',
                'label' => 'Pre-Orden de Fabricación de Casting',
                'tooltip' => 'Pre-orden generada, pendiente de firmar y enviar',
                'borderColor' => '#84cc16',
                'bgColor' => '#f7fee7',
                'textColor' => '#4d7c0f',
            ],
            'LDM_GENERADO', 'RECHAZOS_PROCESADOS_APROBADO' => [
                'fsmState' => FundicionStateConstants::FSM_LDM_GENERADO,
                'icon' => 'LDM-icon.png',
                'label' => 'Formato LDM',
                'tooltip' => 'Modelo aprobado y liberado por Calidad (Formato LDM)',
                'borderColor' => '#22c55e',
                'bgColor' => '#f0fdf4',
                'textColor' => '#15803d',
            ],
            'RDM_GENERADO' => [
                'fsmState' => FundicionStateConstants::FSM_RDM_GENERADO,
                'icon' => 'RDM-SCAR-icon.png',
                'label' => 'Formato RDM y SCAR',
                'tooltip' => 'Modelo rechazado por Calidad (Formato RDM/SCAR)',
                'borderColor' => '#f43f5e',
                'bgColor' => '#fff1f2',
                'textColor' => '#be123c',
            ],
            'FORMATOS_MIXTOS' => [
                'fsmState' => FundicionStateConstants::FSM_FORMATOS_MIXTOS,
                'icon' => 'Formatos_Mixtos_icon.png',
                'label' => 'Formatos Mixtos',
                'tooltip' => 'Formatos LDM y RDM/SCAR generados, pendiente de enviar la alerta',
                'borderColor' => '#ca8a04',
                'bgColor' => '#fef9c3',
                'textColor' => '#713f12',
            ],
            'LIBERADO_ALMACEN' => [
                'fsmState' => FundicionStateConstants::FSM_LIBERADO_ALMACEN,
                'icon' => 'Aprobado.png',
                'label' => 'Liberado',
                'tooltip' => 'OT aprobada y liberada por Calidad',
                'borderColor' => '#22c55e',
                'bgColor' => '#f0fdf4',
                'textColor' => '#15803d',
            ],
            'RECHAZADO_ALMACEN' => [
                'fsmState' => FundicionStateConstants::FSM_RECHAZADO_ALMACEN,
                'icon' => 'Rechazado.png',
                'label' => 'Rechazado',
                'tooltip' => 'OT rechazada por Calidad',
                'borderColor' => '#ef4444',
                'bgColor' => '#fef2f2',
                'textColor' => '#b91c1c',
            ],
            'MIXTO_ALMACEN' => [
                'fsmState' => FundicionStateConstants::FSM_MIXTO_ALMACEN,
                'icon' => 'Mixto.png',
                'label' => 'Mixto',
                'tooltip' => 'LiberaciÃ³n mixta por Calidad',
                'borderColor' => '#eab308',
                'bgColor' => '#fefce8',
                'textColor' => '#854d0e',
            ],
            'EN_ALMACEN' => [
                'fsmState' => FundicionStateConstants::FSM_EN_ALMACEN,
                'icon' => 'almacen.png',
                'label' => 'En Almacén',
                'tooltip' => 'Dictamen enviado a Almacén',
                'borderColor' => '#3b82f6',
                'bgColor' => '#eff6ff',
                'textColor' => '#1d4ed8',
            ],
            'CALIDAD_REVISANDO' => [
                'fsmState' => FundicionStateConstants::FSM_REVISANDO,
                'icon' => 'Revisando.png',
                'label' => 'En Revisión',
                'tooltip' => 'Calidad está realizando la revisión del modelo',
                'borderColor' => '#f59e0b',
                'bgColor' => '#fffbeb',
                'textColor' => '#b45309',
            ],
            'POR_LIBERAR_CALIDAD' => [
                'fsmState' => FundicionStateConstants::FSM_POR_LIBERAR,
                'icon' => 'por_liberar_icon.png',
                'label' => 'Por Liberar',
                'tooltip' => 'Correo de notificación recibido, listo para revisión de Calidad',
                'borderColor' => '#64748b',
                'bgColor' => '#f1f5f9',
                'textColor' => '#334155',
            ],
            'CORREO_ENVIADO' => [
                'fsmState' => FundicionStateConstants::FSM_CORREO_ENVIADO,
                'icon' => 'Quality.png',
                'label' => 'En Calidad',
                'tooltip' => 'Correo enviado, en espera de revisión por Calidad',
                'borderColor' => '#6366f1',
                'bgColor' => '#eef2ff',
                'textColor' => '#4338ca',
            ],
            'PEND_CORREO_CALIDAD', 'PEND_ALMACEN_CALIDAD' => [
                'fsmState' => FundicionStateConstants::FSM_ESPERA,
                'icon' => 'Espera.png',
                'label' => 'En Espera',
                'tooltip' => 'En espera de la otra área',
                'borderColor' => '#a3a3a3',
                'bgColor' => '#fafafa',
                'textColor' => '#525252',
            ],
            'POR_ESCANEAR_PFM' => [
                'fsmState' => FundicionStateConstants::FSM_POR_ESCANEAR,
                'icon' => 'PFM-icon.png',
                'label' => 'Pre-Orden de Fabricación de Modelo',
                'tooltip' => 'Pre-orden generada, pendiente de firmar y enviar',
                'borderColor' => '#8b5cf6',
                'bgColor' => '#f5f3ff',
                'textColor' => '#6d28d9',
            ],
            'TIENE_MODELO' => [
                'fsmState' => FundicionStateConstants::FSM_TIENE_MODELO,
                'icon' => 'perspectiva-icon.png',
                'label' => 'Tengo Modelo',
                'tooltip' => 'Modelo físico disponible, pendiente de procesar',
                'borderColor' => '#14b8a6',
                'bgColor' => '#f0fdfa',
                'textColor' => '#0f766e',
            ],
            'PROCESO_PARCIAL_CORREO', 'PROCESO_PARCIAL_PRE_ORDEN', 'PROCESO_PARCIAL_MODELO' => [
                'fsmState' => FundicionStateConstants::FSM_PROCESO_PARCIAL,
                'icon' => 'proceso_parcial-icon.png',
                'label' => 'Proceso Parcial',
                'tooltip' => 'Proceso parcial, esperando las demás clases',
                'borderColor' => '#f97316',
                'bgColor' => '#fff7ed',
                'textColor' => '#c2410c',
            ],
            'RECHAZOS_PROCESADOS_RECHAZADO', 'REPROCESO_RECHAZADO' => [
                'fsmState' => FundicionStateConstants::FSM_REPROCESO,
                'icon' => 'Reproceso.png',
                'label' => 'Reproceso',
                'tooltip' => 'Retornado hacia un nuevo ciclo de modelo (Reproceso)',
                'borderColor' => '#ec4899',
                'bgColor' => '#fdf2f8',
                'textColor' => '#be185d',
            ],
            'NUEVO' => [
                'fsmState' => FundicionStateConstants::FSM_RECIBIDO,
                'icon' => 'Recibido.png',
                'label' => 'Nuevo',
                'tooltip' => 'Nueva OT recibida sin acciones registradas',
                'borderColor' => '#0ea5e9',
                'bgColor' => '#f0f9ff',
                'textColor' => '#0369a1',
            ],
            default => [
                'fsmState' => FundicionStateConstants::FSM_RECIBIDO,
                'icon' => 'Recibido.png',
                'label' => 'Desconocido',
                'tooltip' => 'Estado no determinado',
                'borderColor' => '#cbd5e1',
                'bgColor' => '#f1f5f9',
                'textColor' => '#64748b',
            ],
        };
    }
}
