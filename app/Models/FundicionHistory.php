<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property string      $ot
 * @property array|null  $ayudas_config
 * @property string|null $status
 * @property \Carbon\Carbon|null $alert_sent_at
 * @property array|null  $almacen_archivos
 * @property bool        $tiene_modelo
 * @property bool        $pre_orden_sent
 * @property bool        $pre_orden_email_sent
 * @property string|null $calidad_revision_status
 * @property bool        $casting_pdf_generated
 * @property bool        $rechazos_procesados
 * @property bool        $dibujos_vistos_almacen
 * @property bool        $pre_orden_autorizada
 * @property bool        $alerta_calidad_sent
 * @property bool        $documentos_revisados_calidad
 * @property bool        $alerta_almacen_2_sent
 * @property bool        $documentos_vistos_almacen_2
 * @property bool        $documentos_firmados_cargados
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class FundicionHistory extends Model
{
    use HasFactory;

    protected $table = 'fundicion_history';

    protected $fillable = [
        'ot',
        'ayudas_config',
        'clases_enviadas',
        'pending_almacen_changes',
        'status',
        'alert_sent_at',
        'almacen_archivos',
        'tiene_modelo',
        'pre_orden_sent',
        'pre_orden_email_sent',
        'calidad_revision_status',
        'casting_pdf_generated',
        'rechazos_procesados',
        'dibujos_vistos_almacen',
        'pre_orden_autorizada',
        'alerta_calidad_sent',
        'documentos_revisados_calidad',
        'alerta_almacen_2_sent',
        'documentos_vistos_almacen_2',
        'documentos_firmados_cargados',
    ];

    protected $casts = [
        'alert_sent_at'                  => 'datetime',
        'almacen_archivos'               => 'array',
        'ayudas_config'                  => 'array',
        'clases_enviadas'                => 'array',
        'pending_almacen_changes'        => 'array',
        'tiene_modelo'                   => 'boolean',
        'pre_orden_sent'                 => 'boolean',
        'pre_orden_email_sent'           => 'boolean',
        'casting_pdf_generated'          => 'boolean',
        'rechazos_procesados'            => 'boolean',
        'dibujos_vistos_almacen'         => 'boolean',
        'pre_orden_autorizada'           => 'boolean',
        'alerta_calidad_sent'            => 'boolean',
        'documentos_revisados_calidad'   => 'boolean',
        'alerta_almacen_2_sent'          => 'boolean',
        'documentos_firmados_cargados'   => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($history) {
            // Si la clase se eliminó de ayudas_config, debemos olvidarla de clases_enviadas para siempre
            $ayudasActuales = is_array($history->ayudas_config) ? $history->ayudas_config : [];
            $enviadas = is_array($history->clases_enviadas) ? $history->clases_enviadas : [];
            
            if (!empty($enviadas)) {
                $nuevoEnviadas = [];
                foreach ($enviadas as $key => $val) {
                    if (is_numeric($key)) {
                        // Legacy: $enviadas era una lista plana ['Pistones', 'Bombillo']
                        if (in_array($val, $ayudasActuales)) {
                            $nuevoEnviadas[] = $val;
                        }
                    } else {
                        // Nuevo: $enviadas es un diccionario ['Pistones' => 'hash123']
                        if (in_array($key, $ayudasActuales)) {
                            $nuevoEnviadas[$key] = $val;
                        }
                    }
                }
                $history->clases_enviadas = $nuevoEnviadas;
            }
        });
    }

    public function isAlmacenFullyProcessed(): bool
    {
        $esReproceso = (bool) preg_match('/_R\d+$/i', $this->ot);
        $previousOtForRechazo = $this->ot;
        if ($esReproceso) {
            $baseOt = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $this->ot);
            $latestRechazo = \App\Models\LiberacionModeloFundicion::where('ot', 'LIKE', $baseOt . '%', 'and')
                ->where('ot', '!=', $this->ot, 'and')
                ->where('decision', '=', 'rechazar', 'and')
                ->orderBy('id', 'desc')
                ->first();
                
            if ($latestRechazo) {
                $previousOtForRechazo = $latestRechazo->ot;
            } else {
                $previousOtForRechazo = $baseOt;
            }
        }
        $rechazadosClases = \App\Models\LiberacionModeloFundicion::where('ot', '=', $previousOtForRechazo, 'and')
            ->where('decision', '=', 'rechazar', 'and')
            ->pluck('tipo_modelo')
            ->unique()
            ->filter(fn($v) => !empty($v))
            ->values()
            ->toArray();

        $otClasesActivas = $esReproceso
            ? array_map('strtolower', $rechazadosClases)
            : (is_array($this->ayudas_config) ? array_map('strtolower', $this->ayudas_config) : []);
        $clasesProcesadas = [];

        $preOrdenesEnviadas = \App\Models\PreOrdenFundicion::where('ot', '=', $this->ot, 'and')->where('is_sent', '=', 1, 'and')->get();
        foreach ($preOrdenesEnviadas as $po) {
            $filas = is_string($po->filas) ? json_decode($po->filas, true) : $po->filas;
            if (is_array($filas)) {
                foreach ($filas as $f) {
                    if (!empty($f['clase'] ?? $f['clase_nombre'])) {
                        $clasesProcesadas[] = strtolower($f['clase'] ?? $f['clase_nombre']);
                    }
                }
            }
        }

        $liberacionesFisicas = \App\Models\LiberacionModeloFundicion::where('ot', '=', $this->ot, 'and')
            ->where('tipo_origen', '=', 'con_modelo', 'and')
            ->whereNotNull('tipo_modelo', 'and')
            ->where('tipo_modelo', '!=', '', 'and')
            ->pluck('tipo_modelo')->toArray();
        foreach ($liberacionesFisicas as $lf) {
            if (!empty($lf)) {
                $clasesProcesadas[] = strtolower($lf);
            }
        }
        $clasesProcesadas = array_filter(array_unique($clasesProcesadas), fn($v) => $v !== '');
        $clasesProcesadas = array_values($clasesProcesadas);

        $clasesActivasFaltantes = [];
        foreach ($otClasesActivas as $clActiva) {
            $cubierta = false;
            foreach ($clasesProcesadas as $cp) {
                if ($cp === '' || $clActiva === '') continue;
                if (strpos($cp, strtolower($clActiva)) !== false || strpos(strtolower($clActiva), $cp) !== false) {
                    $cubierta = true;
                    break;
                }
            }
            if (!$cubierta) {
                $clasesActivasFaltantes[] = $clActiva;
            }
        }

        return count($otClasesActivas) > 0 && count($clasesActivasFaltantes) === 0;
    }
}
