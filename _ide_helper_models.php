<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo whereUpdatedAt($value)
 */
	class AcabadoBombilo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro_mordaza
 * @property string|null $diametro_ceja
 * @property string|null $diametro_sufridera
 * @property string|null $altura_mordaza
 * @property string|null $altura_ceja
 * @property string|null $altura_sufridera
 * @property string|null $diametro_boca
 * @property string|null $diametro_asiento_corona
 * @property string|null $diametro_llanta
 * @property string|null $diametro_caja_corona
 * @property string|null $profundidad_corona
 * @property string|null $angulo_30
 * @property string|null $profundidad_caja_corona
 * @property string|null $simetria
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereAlturaCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereAlturaMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereAlturaSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereAngulo30($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereDiametroAsientoCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereDiametroBoca($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereDiametroCajaCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereDiametroCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereDiametroLlanta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereDiametroMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereDiametroSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereProfundidadCajaCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereProfundidadCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereSimetria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_cnominal whereUpdatedAt($value)
 */
	class AcabadoBombilo_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $diametro_mordaza
 * @property string|null $diametro_ceja
 * @property string|null $diametro_sufridera
 * @property string|null $altura_mordaza
 * @property string|null $altura_ceja
 * @property string|null $altura_sufridera
 * @property string|null $gauge_ceja
 * @property string|null $gauge_corona
 * @property string|null $gauge_llanta
 * @property string|null $altura_total
 * @property string|null $diametro_boca
 * @property string|null $diametro_asiento_corona
 * @property string|null $diametro_llanta
 * @property string|null $diametro_caja_corona
 * @property string|null $profundidad_corona
 * @property string|null $angulo_30
 * @property string|null $profundidad_caja_corona
 * @property string|null $simetria
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $n_pieza
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereAlturaCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereAlturaMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereAlturaSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereAlturaTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereAngulo30($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereDiametroAsientoCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereDiametroBoca($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereDiametroCajaCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereDiametroCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereDiametroLlanta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereDiametroMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereDiametroSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereGaugeCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereGaugeCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereGaugeLlanta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereProfundidadCajaCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereProfundidadCorona($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereSimetria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_pza whereUpdatedAt($value)
 */
	class AcabadoBombilo_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro_mordaza1
 * @property string|null $diametro_mordaza2
 * @property string|null $diametro_ceja1
 * @property string|null $diametro_ceja2
 * @property string|null $diametro_sufridera1
 * @property string|null $diametro_sufridera2
 * @property string|null $altura_mordaza1
 * @property string|null $altura_mordaza2
 * @property string|null $altura_ceja1
 * @property string|null $altura_ceja2
 * @property string|null $altura_sufridera1
 * @property string|null $altura_sufridera2
 * @property string|null $diametro_boca1
 * @property string|null $diametro_boca2
 * @property string|null $diametro_asiento_corona1
 * @property string|null $diametro_asiento_corona2
 * @property string|null $diametro_llanta1
 * @property string|null $diametro_llanta2
 * @property string|null $diametro_caja_corona1
 * @property string|null $diametro_caja_corona2
 * @property string|null $profundidad_corona1
 * @property string|null $profundidad_corona2
 * @property string|null $angulo_301
 * @property string|null $angulo_302
 * @property string|null $profundidad_caja_corona1
 * @property string|null $profundidad_caja_corona2
 * @property string|null $simetria1
 * @property string|null $simetria2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereAlturaCeja1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereAlturaCeja2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereAlturaMordaza1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereAlturaMordaza2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereAlturaSufridera1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereAlturaSufridera2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereAngulo301($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereAngulo302($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroAsientoCorona1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroAsientoCorona2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroBoca1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroBoca2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroCajaCorona1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroCajaCorona2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroCeja1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroCeja2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroLlanta1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroLlanta2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroMordaza1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroMordaza2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroSufridera1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereDiametroSufridera2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereProfundidadCajaCorona1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereProfundidadCajaCorona2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereProfundidadCorona1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereProfundidadCorona2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereSimetria1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereSimetria2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoBombilo_tolerancia whereUpdatedAt($value)
 */
	class AcabadoBombilo_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde whereUpdatedAt($value)
 */
	class AcabadoMolde extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro_mordaza
 * @property string|null $diametro_ceja
 * @property string|null $diametro_sufridera
 * @property string|null $altura_mordaza
 * @property string|null $altura_ceja
 * @property string|null $altura_sufridera
 * @property string|null $diametro_conexion_fondo
 * @property string|null $diametro_llanta
 * @property string|null $diametro_caja_fondo
 * @property string|null $altura_conexion_fondo
 * @property string|null $profundidad_llanta
 * @property string|null $profundidad_caja_fondo
 * @property string|null $simetria
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereAlturaCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereAlturaConexionFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereAlturaMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereAlturaSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereDiametroCajaFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereDiametroCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereDiametroConexionFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereDiametroLlanta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereDiametroMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereDiametroSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereProfundidadCajaFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereProfundidadLlanta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereSimetria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_cnominal whereUpdatedAt($value)
 */
	class AcabadoMolde_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $diametro_mordaza
 * @property string|null $diametro_ceja
 * @property string|null $diametro_sufridera
 * @property string|null $altura_mordaza
 * @property string|null $altura_ceja
 * @property string|null $altura_sufridera
 * @property string|null $gauge_ceja
 * @property string|null $altura_total
 * @property string|null $diametro_conexion_fondo
 * @property string|null $diametro_llanta
 * @property string|null $diametro_caja_fondo
 * @property string|null $altura_conexion_fondo
 * @property string|null $profundidad_llanta
 * @property string|null $profundidad_caja_fondo
 * @property string|null $simetria
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $n_pieza
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereAlturaCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereAlturaConexionFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereAlturaMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereAlturaSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereAlturaTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereDiametroCajaFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereDiametroCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereDiametroConexionFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereDiametroLlanta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereDiametroMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereDiametroSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereGaugeCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereProfundidadCajaFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereProfundidadLlanta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereSimetria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_pza whereUpdatedAt($value)
 */
	class AcabadoMolde_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro_mordaza1
 * @property string|null $diametro_mordaza2
 * @property string|null $diametro_ceja1
 * @property string|null $diametro_ceja2
 * @property string|null $diametro_sufridera1
 * @property string|null $diametro_sufridera2
 * @property string|null $altura_mordaza1
 * @property string|null $altura_mordaza2
 * @property string|null $altura_ceja1
 * @property string|null $altura_ceja2
 * @property string|null $altura_sufridera1
 * @property string|null $altura_sufridera2
 * @property string|null $diametro_conexion_fondo1
 * @property string|null $diametro_conexion_fondo2
 * @property string|null $diametro_llanta1
 * @property string|null $diametro_llanta2
 * @property string|null $diametro_caja_fondo1
 * @property string|null $diametro_caja_fondo2
 * @property string|null $altura_conexion_fondo1
 * @property string|null $altura_conexion_fondo2
 * @property string|null $profundidad_llanta1
 * @property string|null $profundidad_llanta2
 * @property string|null $profundidad_caja_fondo1
 * @property string|null $profundidad_caja_fondo2
 * @property string|null $simetria1
 * @property string|null $simetria2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereAlturaCeja1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereAlturaCeja2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereAlturaConexionFondo1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereAlturaConexionFondo2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereAlturaMordaza1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereAlturaMordaza2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereAlturaSufridera1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereAlturaSufridera2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroCajaFondo1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroCajaFondo2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroCeja1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroCeja2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroConexionFondo1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroConexionFondo2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroLlanta1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroLlanta2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroMordaza1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroMordaza2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroSufridera1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereDiametroSufridera2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereProfundidadCajaFondo1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereProfundidadCajaFondo2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereProfundidadLlanta1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereProfundidadLlanta2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereSimetria1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereSimetria2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcabadoMolde_tolerancia whereUpdatedAt($value)
 */
	class AcabadoMolde_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado query()
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado whereUpdatedAt($value)
 */
	class Asentado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int $estado
 * @property string $n_juego
 * @property string|null $sin_juego
 * @property string|null $sin_luz
 * @property string|null $error
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereSinJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereSinLuz($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asentado_pza whereUpdatedAt($value)
 */
	class Asentado_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_name
 * @property string $action
 * @property string $ruta
 * @property string|null $archivo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFileLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFileLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFileLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFileLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFileLog whereArchivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFileLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFileLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFileLog whereRuta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFileLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFileLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFileLog whereUserName($value)
 */
	class AyudaVisualFileLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_name
 * @property string $action
 * @property string $ruta
 * @property string|null $archivo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionFileLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionFileLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionFileLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionFileLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionFileLog whereArchivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionFileLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionFileLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionFileLog whereRuta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionFileLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionFileLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionFileLog whereUserName($value)
 */
	class AyudaVisualFundicionFileLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $proceso
 * @property string $clase
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionHistory whereClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionHistory whereProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualFundicionHistory whereUpdatedAt($value)
 */
	class AyudaVisualFundicionHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $proceso
 * @property string $clase
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualHistory whereClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualHistory whereProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AyudaVisualHistory whereUpdatedAt($value)
 */
	class AyudaVisualHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra query()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra whereUpdatedAt($value)
 */
	class BarrenoManiobra extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $profundidad_barreno
 * @property string|null $diametro_machuelo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_cnominal whereDiametroMachuelo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_cnominal whereProfundidadBarreno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_cnominal whereUpdatedAt($value)
 */
	class BarrenoManiobra_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $n_pieza
 * @property string|null $profundidad_barreno
 * @property string|null $diametro_machuelo
 * @property string|null $acetatoBM
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereAcetatoBM($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereDiametroMachuelo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereProfundidadBarreno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_pza whereUpdatedAt($value)
 */
	class BarrenoManiobra_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $profundidad_barreno1
 * @property string|null $profundidad_barreno2
 * @property string|null $diametro_machuelo1
 * @property string|null $diametro_machuelo2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_tolerancia whereDiametroMachuelo1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_tolerancia whereDiametroMachuelo2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_tolerancia whereProfundidadBarreno1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_tolerancia whereProfundidadBarreno2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoManiobra_tolerancia whereUpdatedAt($value)
 */
	class BarrenoManiobra_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad query()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad whereUpdatedAt($value)
 */
	class BarrenoProfundidad extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $broca1
 * @property string|null $tiempo1
 * @property string|null $broca2
 * @property string|null $tiempo2
 * @property string|null $broca3
 * @property string|null $tiempo3
 * @property string|null $entradaSalida
 * @property string|null $diametro_arrastre1
 * @property string|null $diametro_arrastre2
 * @property string|null $diametro_arrastre3
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereBroca1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereBroca2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereBroca3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereDiametroArrastre1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereDiametroArrastre2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereDiametroArrastre3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereEntradaSalida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereTiempo1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereTiempo2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereTiempo3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_cnominal whereUpdatedAt($value)
 */
	class BarrenoProfundidad_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $n_pieza
 * @property string|null $broca1
 * @property string|null $tiempo1
 * @property string|null $broca2
 * @property string|null $tiempo2
 * @property string|null $broca3
 * @property string|null $tiempo3
 * @property string|null $entrada
 * @property string|null $salida
 * @property string|null $diametro_arrastre1
 * @property string|null $diametro_arrastre2
 * @property string|null $diametro_arrastre3
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereBroca1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereBroca2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereBroca3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereDiametroArrastre1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereDiametroArrastre2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereDiametroArrastre3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereSalida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereTiempo1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereTiempo2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereTiempo3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_pza whereUpdatedAt($value)
 */
	class BarrenoProfundidad_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $broca1
 * @property string|null $tiempo1
 * @property string|null $broca2
 * @property string|null $tiempo2
 * @property string|null $broca3
 * @property string|null $tiempo3
 * @property string|null $entrada
 * @property string|null $salida
 * @property string|null $diametro_arrastre1
 * @property string|null $diametro_arrastre2
 * @property string|null $diametro_arrastre3
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereBroca1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereBroca2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereBroca3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereDiametroArrastre1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereDiametroArrastre2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereDiametroArrastre3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereSalida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereTiempo1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereTiempo2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereTiempo3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrenoProfundidad_tolerancia whereUpdatedAt($value)
 */
	class BarrenoProfundidad_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * Modelo de índice para los documentos de Calidad — Maquinados.
 * 
 * Sincronizado automáticamente cada 5 min por el Command
 * App\Console\Commands\SyncMaquinadosDocs.
 *
 * @property int         $id
 * @property string      $nombre_archivo
 * @property string      $ruta_storage
 * @property string      $tipo               'dibujo' | 'ayuda'
 * @property string      $estado             'activo'  | 'inactivo'
 * @property string|null $ot
 * @property string|null $clase
 * @property string|null $proceso
 * @property string|null $fecha_archivo
 * @property \Carbon\Carbon|null $primera_deteccion_at
 * @property \Carbon\Carbon|null $ultima_deteccion_at
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc activos()
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc ayudas()
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc dibujos()
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc inactivos()
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc query()
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereFechaArchivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereNombreArchivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc wherePrimeraDeteccionAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereRutaStorage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereUltimaDeteccionAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalidadMaquinadoDoc whereUpdatedAt($value)
 */
	class CalidadMaquinadoDoc extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property int|null $id_clase
 * @property int|null $operacion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Orden_trabajo $orden_trabajo
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador query()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador whereIdClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador whereOperacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador whereUpdatedAt($value)
 */
	class CandadoObturador extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $altura
 * @property string|null $alturaCandado1
 * @property string|null $alturaCandado2
 * @property string|null $alturaAsientoObturador1
 * @property string|null $alturaAsientoObturador2
 * @property string|null $profundidadSoldadura1
 * @property string|null $profundidadSoldadura2
 * @property string|null $pushUp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CandadoObturador|null $proceso
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal whereAltura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal whereAlturaAsientoObturador1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal whereAlturaAsientoObturador2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal whereAlturaCandado1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal whereAlturaCandado2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal whereProfundidadSoldadura1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal whereProfundidadSoldadura2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal wherePushUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_cnominal whereUpdatedAt($value)
 */
	class CandadoObturador_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $n_pieza
 * @property string|null $altura
 * @property string|null $alturaCandado1
 * @property string|null $alturaCandado2
 * @property string|null $alturaAsientoObturador1
 * @property string|null $alturaAsientoObturador2
 * @property string|null $profundidadSoldadura1
 * @property string|null $profundidadSoldadura2
 * @property string|null $pushUp
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Metas|null $meta
 * @property-read \App\Models\CandadoObturador $proceso
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereAltura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereAlturaAsientoObturador1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereAlturaAsientoObturador2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereAlturaCandado1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereAlturaCandado2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereProfundidadSoldadura1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereProfundidadSoldadura2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza wherePushUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_pza whereUpdatedAt($value)
 */
	class CandadoObturador_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $altura
 * @property string|null $alturaCandado1
 * @property string|null $alturaCandado2
 * @property string|null $alturaAsientoObturador1
 * @property string|null $alturaAsientoObturador2
 * @property string|null $profundidadSoldadura1
 * @property string|null $profundidadSoldadura2
 * @property string|null $pushUp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CandadoObturador|null $proceso
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia whereAltura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia whereAlturaAsientoObturador1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia whereAlturaAsientoObturador2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia whereAlturaCandado1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia whereAlturaCandado2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia whereProfundidadSoldadura1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia whereProfundidadSoldadura2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia wherePushUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CandadoObturador_tolerancia whereUpdatedAt($value)
 */
	class CandadoObturador_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades whereUpdatedAt($value)
 */
	class Cavidades extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $profundidad1
 * @property string|null $diametro1
 * @property string|null $profundidad2
 * @property string|null $diametro2
 * @property string|null $profundidad3
 * @property string|null $diametro3
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $altura1
 * @property string|null $altura2
 * @property string|null $altura3
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereAltura1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereAltura2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereAltura3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereDiametro1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereDiametro2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereDiametro3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereProfundidad1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereProfundidad2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereProfundidad3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_cnominal whereUpdatedAt($value)
 */
	class Cavidades_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $profundidad1
 * @property string|null $diametro1
 * @property string|null $profundidad2
 * @property string|null $diametro2
 * @property string|null $profundidad3
 * @property string|null $diametro3
 * @property string|null $acetatoBM
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $n_pieza
 * @property string|null $altura1
 * @property string|null $altura2
 * @property string|null $altura3
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereAcetatoBM($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereAltura1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereAltura2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereAltura3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereDiametro1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereDiametro2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereDiametro3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereProfundidad1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereProfundidad2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereProfundidad3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_pza whereUpdatedAt($value)
 */
	class Cavidades_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $profundidad1_1
 * @property string|null $profundidad2_1
 * @property string|null $diametro1_1
 * @property string|null $diametro2_1
 * @property string|null $profundidad1_2
 * @property string|null $profundidad2_2
 * @property string|null $diametro1_2
 * @property string|null $diametro2_2
 * @property string|null $profundidad1_3
 * @property string|null $profundidad2_3
 * @property string|null $diametro1_3
 * @property string|null $diametro2_3
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $altura1
 * @property string|null $altura2
 * @property string|null $altura3
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereAltura1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereAltura2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereAltura3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereDiametro11($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereDiametro12($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereDiametro13($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereDiametro21($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereDiametro22($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereDiametro23($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereProfundidad11($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereProfundidad12($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereProfundidad13($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereProfundidad21($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereProfundidad22($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereProfundidad23($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cavidades_tolerancia whereUpdatedAt($value)
 */
	class Cavidades_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado whereUpdatedAt($value)
 */
	class Cepillado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $radiof_mordaza
 * @property string $radiof_mayor
 * @property string $radiof_sufridera
 * @property string $profuFinal_CFC
 * @property string $profuFinal_mitadMB
 * @property string $profuFinal_PCO
 * @property string $ensamble
 * @property string $distancia_barrenoAli
 * @property string $profu_barrenoAliHembra
 * @property string $profu_barrenoAliMacho
 * @property string $altura_venaHembra
 * @property string $altura_venaMacho
 * @property string $ancho_vena
 * @property string $laterales
 * @property string $pin1
 * @property string $pin2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereAlturaVenaHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereAlturaVenaMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereAnchoVena($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereDistanciaBarrenoAli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereEnsamble($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereLaterales($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal wherePin1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal wherePin2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereProfuBarrenoAliHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereProfuBarrenoAliMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereProfuFinalCFC($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereProfuFinalMitadMB($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereProfuFinalPCO($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereRadiofMayor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereRadiofMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereRadiofSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_cnominal whereUpdatedAt($value)
 */
	class Cepillado_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $radiof_mordaza1
 * @property string $radiof_mordaza2
 * @property string $radiof_mayor1
 * @property string $radiof_mayor2
 * @property string $radiof_sufridera1
 * @property string $radiof_sufridera2
 * @property string $profuFinal_CFC1
 * @property string $profuFinal_CFC2
 * @property string $profuFinal_mitadMB1
 * @property string $profuFinal_mitadMB2
 * @property string $profuFinal_PCO1
 * @property string $profuFinal_PCO2
 * @property string $ensamble1
 * @property string $ensamble2
 * @property string $distancia_barrenoAli1
 * @property string $distancia_barrenoAli2
 * @property string $profu_barrenoAliHembra1
 * @property string $profu_barrenoAliHembra2
 * @property string $profu_barrenoAliMacho1
 * @property string $profu_barrenoAliMacho2
 * @property string $altura_venaHembra1
 * @property string $altura_venaHembra2
 * @property string $altura_venaMacho1
 * @property string $altura_venaMacho2
 * @property string $ancho_vena1
 * @property string $ancho_vena2
 * @property string|null $laterales1
 * @property string|null $laterales2
 * @property string $pin1
 * @property string $pin2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereAlturaVenaHembra1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereAlturaVenaHembra2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereAlturaVenaMacho1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereAlturaVenaMacho2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereAnchoVena1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereAnchoVena2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereDistanciaBarrenoAli1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereDistanciaBarrenoAli2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereEnsamble1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereEnsamble2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereLaterales1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereLaterales2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia wherePin1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia wherePin2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereProfuBarrenoAliHembra1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereProfuBarrenoAliHembra2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereProfuBarrenoAliMacho1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereProfuBarrenoAliMacho2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereProfuFinalCFC1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereProfuFinalCFC2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereProfuFinalMitadMB1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereProfuFinalMitadMB2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereProfuFinalPCO1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereProfuFinalPCO2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereRadiofMayor1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereRadiofMayor2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereRadiofMordaza1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereRadiofMordaza2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereRadiofSufridera1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereRadiofSufridera2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cepillado_tolerancia whereUpdatedAt($value)
 */
	class Cepillado_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_ot
 * @property string $nombre
 * @property string|null $tamanio
 * @property int|null $seccion
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pieza> $piezas
 * @property int $pedido
 * @property string $fecha_inicio
 * @property string $hora_inicio
 * @property string|null $fecha_termino
 * @property string|null $hora_termino
 * @property int $finalizada
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fecha_proceso> $fechasProcesos
 * @property-read int|null $fechas_procesos_count
 * @property-read int|null $piezas_count
 * @property-read \App\Models\Procesos|null $procesos
 * @method static \Database\Factories\ClaseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Clase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Clase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Clase query()
 * @method static \Illuminate\Database\Eloquent\Builder|Clase whereFechaInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Clase whereFechaTermino($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Clase whereFinalizada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Clase whereHoraInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Clase whereHoraTermino($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Clase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Clase whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Clase whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Clase wherePedido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Clase wherePiezas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Clase whereSeccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Clase whereTamanio($value)
 */
	class Clase extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado query()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado whereUpdatedAt($value)
 */
	class Copiado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro1_cilindrado
 * @property string|null $profundidad1_cilindrado
 * @property string|null $diametro2_cilindrado
 * @property string|null $profundidad2_cilindrado
 * @property string|null $diametro_sufridera
 * @property string|null $diametro_ranura
 * @property string|null $profundidad_ranura
 * @property string|null $profundidad_sufridera
 * @property string|null $altura_total
 * @property string|null $diametro1_cavidades
 * @property string|null $profundidad1_cavidades
 * @property string|null $diametro2_cavidades
 * @property string|null $profundidad2_cavidades
 * @property string|null $diametro3
 * @property string|null $profundidad3
 * @property string|null $diametro4
 * @property string|null $profundidad4
 * @property string|null $volumen
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereAlturaTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereDiametro1Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereDiametro1Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereDiametro2Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereDiametro2Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereDiametro3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereDiametro4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereDiametroRanura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereDiametroSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereProfundidad1Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereProfundidad1Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereProfundidad2Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereProfundidad2Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereProfundidad3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereProfundidad4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereProfundidadRanura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereProfundidadSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_cnominal whereVolumen($value)
 */
	class Copiado_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $n_pieza
 * @property string|null $diametro1_cilindrado
 * @property string|null $profundidad1_cilindrado
 * @property string|null $diametro2_cilindrado
 * @property string|null $profundidad2_cilindrado
 * @property string|null $diametro_sufridera
 * @property string|null $diametro_ranura
 * @property string|null $profundidad_ranura
 * @property string|null $profundidad_sufridera
 * @property string|null $altura_total
 * @property string|null $observaciones_cilindrado
 * @property string|null $error_cilindrado
 * @property string|null $diametro1_cavidades
 * @property string|null $profundidad1_cavidades
 * @property string|null $diametro2_cavidades
 * @property string|null $profundidad2_cavidades
 * @property string|null $diametro3
 * @property string|null $profundidad3
 * @property string|null $diametro4
 * @property string|null $profundidad4
 * @property string|null $volumen
 * @property string|null $observaciones_cavidades
 * @property string|null $error_cavidades
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereAlturaTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereDiametro1Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereDiametro1Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereDiametro2Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereDiametro2Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereDiametro3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereDiametro4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereDiametroRanura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereDiametroSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereErrorCavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereErrorCilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereObservacionesCavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereObservacionesCilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereProfundidad1Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereProfundidad1Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereProfundidad2Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereProfundidad2Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereProfundidad3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereProfundidad4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereProfundidadRanura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereProfundidadSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_pza whereVolumen($value)
 */
	class Copiado_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro1_cilindrado
 * @property string|null $profundidad1_cilindrado
 * @property string|null $diametro2_cilindrado
 * @property string|null $profundidad2_cilindrado
 * @property string|null $diametro_sufridera
 * @property string|null $diametro_ranura
 * @property string|null $profundidad_ranura
 * @property string|null $profundidad_sufridera
 * @property string|null $altura_total
 * @property string|null $diametro1_cavidades
 * @property string|null $profundidad1_cavidades
 * @property string|null $diametro2_cavidades
 * @property string|null $profundidad2_cavidades
 * @property string|null $diametro3
 * @property string|null $profundidad3
 * @property string|null $diametro4
 * @property string|null $profundidad4
 * @property string|null $volumen
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereAlturaTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereDiametro1Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereDiametro1Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereDiametro2Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereDiametro2Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereDiametro3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereDiametro4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereDiametroRanura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereDiametroSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereProfundidad1Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereProfundidad1Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereProfundidad2Cavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereProfundidad2Cilindrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereProfundidad3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereProfundidad4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereProfundidadRanura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereProfundidadSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Copiado_tolerancia whereVolumen($value)
 */
	class Copiado_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DesbasteExterior newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DesbasteExterior newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DesbasteExterior query()
 * @method static \Illuminate\Database\Eloquent\Builder|DesbasteExterior whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DesbasteExterior whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DesbasteExterior whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DesbasteExterior whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DesbasteExterior whereUpdatedAt($value)
 */
	class DesbasteExterior extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $diametro_mordaza
 * @property string $diametro_ceja
 * @property string $diametro_sufrideraExtra
 * @property string $simetria_ceja
 * @property string $simetria_mordaza
 * @property string $altura_ceja
 * @property string $altura_sufridera
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal whereAlturaCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal whereAlturaSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal whereDiametroCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal whereDiametroMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal whereDiametroSufrideraExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal whereSimetriaCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal whereSimetriaMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_cnominal whereUpdatedAt($value)
 */
	class Desbaste_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $n_pieza
 * @property string|null $diametro_mordaza
 * @property string|null $diametro_ceja
 * @property string|null $diametro_sufrideraExtra
 * @property string|null $simetria_ceja
 * @property string|null $simetria_mordaza
 * @property string|null $altura_ceja
 * @property string|null $altura_sufridera
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereAlturaCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereAlturaSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereDiametroCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereDiametroMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereDiametroSufrideraExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereSimetriaCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereSimetriaMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_pza whereUpdatedAt($value)
 */
	class Desbaste_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $diametro_mordaza1
 * @property string $diametro_mordaza2
 * @property string $diametro_ceja1
 * @property string $diametro_ceja2
 * @property string $diametro_sufrideraExtra1
 * @property string $diametro_sufrideraExtra2
 * @property string $simetria_ceja1
 * @property string $simetria_ceja2
 * @property string $simetria_mordaza1
 * @property string $simetria_mordaza2
 * @property string $altura_ceja1
 * @property string $altura_ceja2
 * @property string $altura_sufridera1
 * @property string $altura_sufridera2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereAlturaCeja1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereAlturaCeja2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereAlturaSufridera1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereAlturaSufridera2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereDiametroCeja1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereDiametroCeja2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereDiametroMordaza1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereDiametroMordaza2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereDiametroSufrideraExtra1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereDiametroSufrideraExtra2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereSimetriaCeja1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereSimetriaCeja2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereSimetriaMordaza1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereSimetriaMordaza2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Desbaste_tolerancia whereUpdatedAt($value)
 */
	class Desbaste_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_name
 * @property string $action
 * @property string $ruta
 * @property string|null $archivo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoFileLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoFileLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoFileLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoFileLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoFileLog whereArchivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoFileLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoFileLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoFileLog whereRuta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoFileLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoFileLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoFileLog whereUserName($value)
 */
	class DibujoFileLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ot
 * @property string $clase
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoOtHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoOtHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoOtHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoOtHistory whereClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoOtHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoOtHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoOtHistory whereOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DibujoOtHistory whereUpdatedAt($value)
 */
	class DibujoOtHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM whereUpdatedAt($value)
 */
	class EmbudoCM extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $conexion_lineaPartida
 * @property string|null $conexion_90G
 * @property string|null $altura_conexion
 * @property string|null $diametro_embudo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_cnominal whereAlturaConexion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_cnominal whereConexion90G($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_cnominal whereConexionLineaPartida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_cnominal whereDiametroEmbudo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_cnominal whereUpdatedAt($value)
 */
	class EmbudoCM_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $conexion_lineaPartida
 * @property string|null $conexion_90G
 * @property string|null $altura_conexion
 * @property string|null $diametro_embudo
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereAlturaConexion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereConexion90G($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereConexionLineaPartida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereDiametroEmbudo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_pza whereUpdatedAt($value)
 */
	class EmbudoCM_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $conexion_lineaPartida
 * @property string|null $conexion_90G
 * @property string|null $altura_conexion
 * @property string|null $diametro_embudo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_tolerancias newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_tolerancias newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_tolerancias query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_tolerancias whereAlturaConexion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_tolerancias whereConexion90G($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_tolerancias whereConexionLineaPartida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_tolerancias whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_tolerancias whereDiametroEmbudo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_tolerancias whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_tolerancias whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmbudoCM_tolerancias whereUpdatedAt($value)
 */
	class EmbudoCM_tolerancias extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $clase
 * @property string $proceso
 * @property string $fecha_inicio
 * @property string $fecha_fin
 * @method static \Illuminate\Database\Eloquent\Builder|Fecha_proceso newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fecha_proceso newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fecha_proceso query()
 * @method static \Illuminate\Database\Eloquent\Builder|Fecha_proceso whereClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fecha_proceso whereFechaFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fecha_proceso whereFechaInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fecha_proceso whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fecha_proceso whereProceso($value)
 */
	class Fecha_proceso extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_name
 * @property string $action
 * @property string $ruta
 * @property string|null $archivo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionFileLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionFileLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionFileLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionFileLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionFileLog whereArchivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionFileLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionFileLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionFileLog whereRuta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionFileLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionFileLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionFileLog whereUserName($value)
 */
	class FundicionFileLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ot
 * @property string $status
 * @property bool $tiene_modelo
 * @property bool $pre_orden_sent
 * @property bool $pre_orden_email_sent
 * @property string|null $calidad_revision_status null | pendiente | aprobado | rechazado
 * @property bool $casting_pdf_generated
 * @property bool $rechazos_procesados
 * @property array|null $ayudas_config
 * @property \Illuminate\Support\Carbon|null $alert_sent_at
 * @property array|null $almacen_archivos
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $dibujos_vistos_almacen
 * @property bool $pre_orden_autorizada
 * @property bool $alerta_calidad_sent
 * @property bool $documentos_revisados_calidad
 * @property bool $alerta_almacen_2_sent
 * @property bool $documentos_vistos_almacen_2
 * @property bool $documentos_firmados_cargados
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereAlertSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereAlertaAlmacen2Sent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereAlertaCalidadSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereAlmacenArchivos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereAyudasConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereCalidadRevisionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereCastingPdfGenerated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereDibujosVistosAlmacen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereDocumentosFirmadosCargados($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereDocumentosRevisadosCalidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereDocumentosVistosAlmacen2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory wherePreOrdenAutorizada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory wherePreOrdenEmailSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory wherePreOrdenSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereRechazosProcesados($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereTieneModelo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FundicionHistory whereUpdatedAt($value)
 */
	class FundicionHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * Imagen asociada a una herramienta Tecamac.
 * 
 * Cada registro representa UNA foto con su tipo, nombre y ruta.
 * 
 * Tipos permitidos:
 *   herramienta           → foto del inserto / cuerpo de la herramienta
 *   accesorio             → foto del accesorio de la herramienta
 *   tornilleria           → foto de tornillería
 *   tornilleria_accesorio → foto de accesorio de tornillería
 *
 * @property int $id
 * @property int $herramienta_id
 * @property string $tipo
 * @property string|null $nombre
 * @property string $ruta
 * @property int $orden
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\HerramientaTecamac $herramienta
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaImagen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaImagen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaImagen query()
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaImagen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaImagen whereHerramientaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaImagen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaImagen whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaImagen whereOrden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaImagen whereRuta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaImagen whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaImagen whereUpdatedAt($value)
 */
	class HerramientaImagen extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property array|null $proceso
 * @property string|null $nombre_herramienta
 * @property string|null $descripcion_herramienta
 * @property string|null $descripcion_inserto
 * @property string|null $nombre_accesorio
 * @property string|null $accesorios
 * @property int $cantidad_portaherramientas
 * @property float|null $profundidad_corte
 * @property int|null $rpm
 * @property string|null $avances
 * @property int|null $minimo
 * @property int|null $maximo
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HerramientaImagen> $imagenes
 * @property-read int|null $imagenes_count
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac query()
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereAccesorios($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereAvances($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereCantidadPortaherramientas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereDescripcionHerramienta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereDescripcionInserto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereMaximo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereMinimo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereNombreAccesorio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereNombreHerramienta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereProfundidadCorte($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereRpm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HerramientaTecamac whereUpdatedAt($value)
 */
	class HerramientaTecamac extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionLog query()
 */
	class LiberacionLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ot
 * @property string $estado
 * @property string|null $decision
 * @property string|null $pdf_filename
 * @property string|null $tipo_origen pre_orden: Almacén no tenía modelo y se mandó fabricar | con_modelo: Almacén ya contaba con él
 * @property string|null $tipo_modelo Fondo | Obturador | Molde | Bombillo
 * @property array|null $medidas_modelo
 * @property string|null $observaciones_modelo
 * @property array|null $medidas_plantilla
 * @property string|null $observaciones_plantilla
 * @property array|null $medidas_fondo
 * @property string|null $observaciones_fondo
 * @property array|null $medidas_obturador
 * @property string|null $observaciones_obturador
 * @property string|null $motivo_rechazo Obligatorio si estado = rechazado
 * @property int|null $user_id_calidad
 * @property string|null $user_nombre_calidad
 * @property \Illuminate\Support\Carbon|null $fecha_revision
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FundicionHistory|null $historial
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion query()
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereDecision($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereFechaRevision($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereMedidasFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereMedidasModelo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereMedidasObturador($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereMedidasPlantilla($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereMotivoRechazo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereObservacionesFondo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereObservacionesModelo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereObservacionesObturador($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereObservacionesPlantilla($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion wherePdfFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereTipoModelo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereTipoOrigen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereUserIdCalidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionModeloFundicion whereUserNombreCalidad($value)
 */
	class LiberacionModeloFundicion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\User|null $operador
 * @property-read \App\Models\QRGeneradoSoldadura|null $qrGenerado
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionSoldadura newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionSoldadura newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LiberacionSoldadura query()
 */
	class LiberacionSoldadura extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_name
 * @property string $action
 * @property string $ruta
 * @property string|null $archivo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ManualFileLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManualFileLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManualFileLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ManualFileLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualFileLog whereArchivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualFileLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualFileLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualFileLog whereRuta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualFileLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualFileLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualFileLog whereUserName($value)
 */
	class ManualFileLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $proceso
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ManualHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManualHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManualHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ManualHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualHistory whereProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualHistory whereUpdatedAt($value)
 */
	class ManualHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $maquina
 * @property string $proceso
 * @property int $id_meta
 * @method static \Illuminate\Database\Eloquent\Builder|Maquinas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Maquinas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Maquinas query()
 * @method static \Illuminate\Database\Eloquent\Builder|Maquinas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Maquinas whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Maquinas whereMaquina($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Maquinas whereProceso($value)
 */
	class Maquinas extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_ot
 * @property string $id_usuario
 * @property string $fecha
 * @property string $h_inicio
 * @property string $h_termino
 * @property int|null $t_estandar
 * @property float|null $meta
 * @property float|null $resultado
 * @property string|null $maquina
 * @property int|null $id_clase
 * @property int|null $id_proceso
 * @property string $proceso
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\MetasFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Metas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Metas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Metas query()
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereHInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereHTermino($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereIdClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereIdUsuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereMaquina($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereResultado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereTEstandar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Metas whereUpdatedAt($value)
 */
	class Metas extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nombre
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Moldura newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Moldura newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Moldura query()
 * @method static \Illuminate\Database\Eloquent\Builder|Moldura whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Moldura whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Moldura whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Moldura whereUpdatedAt($value)
 */
	class Moldura extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet query()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet whereUpdatedAt($value)
 */
	class OffSet extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $anchoRanura
 * @property string|null $profuTaconHembra
 * @property string|null $profuTaconMacho
 * @property string|null $simetriaHembra
 * @property string|null $simetriaMacho
 * @property string|null $anchoTacon
 * @property string|null $barrenoLateralHembra
 * @property string|null $barrenoLateralMacho
 * @property string|null $alturaTaconInicial
 * @property string|null $alturaTaconIntermedia
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereAlturaTaconInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereAlturaTaconIntermedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereAnchoRanura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereAnchoTacon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereBarrenoLateralHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereBarrenoLateralMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereProfuTaconHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereProfuTaconMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereSimetriaHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereSimetriaMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_cnominal whereUpdatedAt($value)
 */
	class OffSet_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $anchoRanura
 * @property string|null $profuTaconHembra
 * @property string|null $profuTaconMacho
 * @property string|null $simetriaHembra
 * @property string|null $simetriaMacho
 * @property string|null $anchoTacon
 * @property string|null $barrenoLateralHembra
 * @property string|null $barrenoLateralMacho
 * @property string|null $alturaTaconInicial
 * @property string|null $alturaTaconIntermedia
 * @property string|null $error
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $n_pieza
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereAlturaTaconInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereAlturaTaconIntermedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereAnchoRanura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereAnchoTacon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereBarrenoLateralHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereBarrenoLateralMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereProfuTaconHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereProfuTaconMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereSimetriaHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereSimetriaMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_pza whereUpdatedAt($value)
 */
	class OffSet_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $anchoRanura
 * @property string|null $profuTaconHembra
 * @property string|null $profuTaconMacho
 * @property string|null $simetriaHembra
 * @property string|null $simetriaMacho
 * @property string|null $anchoTacon
 * @property string|null $barrenoLateralHembra
 * @property string|null $barrenoLateralMacho
 * @property string|null $alturaTaconInicial
 * @property string|null $alturaTaconIntermedia
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereAlturaTaconInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereAlturaTaconIntermedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereAnchoRanura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereAnchoTacon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereBarrenoLateralHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereBarrenoLateralMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereProfuTaconHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereProfuTaconMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereSimetriaHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereSimetriaMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffSet_tolerancia whereUpdatedAt($value)
 */
	class OffSet_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_moldura
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Clase> $clases
 * @property-read int|null $clases_count
 * @property-read \App\Models\Moldura $moldura
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PtaResultado> $ptaResultados
 * @property-read int|null $pta_resultados_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SoldaduraPTA> $soldaduraPTA
 * @property-read int|null $soldadura_p_t_a_count
 * @method static \Illuminate\Database\Eloquent\Builder|Orden_trabajo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Orden_trabajo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Orden_trabajo query()
 * @method static \Illuminate\Database\Eloquent\Builder|Orden_trabajo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Orden_trabajo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Orden_trabajo whereIdMoldura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Orden_trabajo whereUpdatedAt($value)
 */
	class Orden_trabajo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas query()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas whereUpdatedAt($value)
 */
	class Palomas extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $anchoPaloma
 * @property string|null $gruesoPaloma
 * @property string|null $profundidadPaloma
 * @property string|null $rebajeLlanta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_cnominal whereAnchoPaloma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_cnominal whereGruesoPaloma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_cnominal whereProfundidadPaloma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_cnominal whereRebajeLlanta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_cnominal whereUpdatedAt($value)
 */
	class Palomas_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $anchoPaloma
 * @property string|null $gruesoPaloma
 * @property string|null $profundidadPaloma
 * @property string|null $rebajeLlanta
 * @property string|null $error
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereAnchoPaloma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereGruesoPaloma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereProfundidadPaloma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereRebajeLlanta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_pza whereUpdatedAt($value)
 */
	class Palomas_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $anchoPaloma
 * @property string|null $gruesoPaloma
 * @property string|null $profundidadPaloma
 * @property string|null $rebajeLlanta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_tolerancia whereAnchoPaloma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_tolerancia whereGruesoPaloma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_tolerancia whereProfundidadPaloma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_tolerancia whereRebajeLlanta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Palomas_tolerancia whereUpdatedAt($value)
 */
	class Palomas_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_ot
 * @property int $id_clase
 * @property string $n_pieza
 * @property string $id_operador
 * @property string $maquina
 * @property string $proceso
 * @property string $error
 * @property int $liberacion
 * @property string|null $fecha_liberacion
 * @property string|null $user_liberacion
 * @property string|null $observacion_liberacion
 * @property string|null $observacion_operador
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Clase $clase
 * @property-read \App\Models\User|null $liberador
 * @property-read \App\Models\User $operador
 * @property-read \App\Models\Orden_trabajo $ordenTrabajo
 * @property-read \App\Models\PtaResultado|null $ptaResultado
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereFechaLiberacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereIdClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereIdOperador($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereLiberacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereMaquina($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereObservacionLiberacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereObservacionOperador($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pieza whereUserLiberacion($value)
 */
	class Pieza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ot
 * @property string $folio
 * @property string $proveedor
 * @property \Illuminate\Support\Carbon $fecha_creacion
 * @property \Illuminate\Support\Carbon|null $fecha_entrega
 * @property string|null $moldura
 * @property string|null $observaciones
 * @property array $filas
 * @property string|null $pdf_filename Nombre del último PDF generado
 * @property int $version Número de veces que se ha regenerado el PDF
 * @property int|null $user_id
 * @property string|null $user_nombre
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion query()
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereFechaCreacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereFechaEntrega($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereFilas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereFolio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereMoldura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion wherePdfFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereProveedor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereUserNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenFundicion whereVersion($value)
 */
	class PreOrdenFundicion extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PreOrdenLog query()
 */
	class PreOrdenLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura whereUpdatedAt($value)
 */
	class PrimeraOpeSoldadura extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $diametro1
 * @property string $profundidad1
 * @property string $diametro2
 * @property string $profundidad2
 * @property string $diametro3
 * @property string $profundidad3
 * @property string $diametroSoldadura
 * @property string $profundidadSoldadura
 * @property string $diametroBarreno
 * @property string $simetriaLinea_partida
 * @property string $pernoAlineacion
 * @property string $Simetria90G
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereDiametro1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereDiametro2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereDiametro3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereDiametroBarreno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereDiametroSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal wherePernoAlineacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereProfundidad1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereProfundidad2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereProfundidad3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereProfundidadSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereSimetria90G($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereSimetriaLineaPartida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_cnominal whereUpdatedAt($value)
 */
	class PrimeraOpeSoldadura_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $n_pieza
 * @property string|null $diametro1
 * @property string|null $profundidad1
 * @property string|null $diametro2
 * @property string|null $profundidad2
 * @property string|null $diametro3
 * @property string|null $profundidad3
 * @property string|null $diametroSoldadura
 * @property string|null $profundidadSoldadura
 * @property string|null $diametroBarreno
 * @property string|null $simetriaLinea_partida
 * @property string|null $pernoAlineacion
 * @property string|null $Simetria90G
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereDiametro1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereDiametro2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereDiametro3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereDiametroBarreno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereDiametroSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza wherePernoAlineacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereProfundidad1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereProfundidad2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereProfundidad3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereProfundidadSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereSimetria90G($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereSimetriaLineaPartida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_pza whereUpdatedAt($value)
 */
	class PrimeraOpeSoldadura_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $diametro1
 * @property string $profundidad1
 * @property string $diametro2
 * @property string $profundidad2
 * @property string $diametro3
 * @property string $profundidad3
 * @property string $diametroSoldadura
 * @property string $profundidadSoldadura
 * @property string $diametroBarreno1
 * @property string $diametroBarreno2
 * @property string $simetriaLinea_partida1
 * @property string $simetriaLinea_partida2
 * @property string $pernoAlineacion
 * @property string $Simetria90G
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereDiametro1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereDiametro2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereDiametro3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereDiametroBarreno1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereDiametroBarreno2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereDiametroSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia wherePernoAlineacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereProfundidad1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereProfundidad2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereProfundidad3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereProfundidadSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereSimetria90G($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereSimetriaLineaPartida1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereSimetriaLineaPartida2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOpeSoldadura_tolerancia whereUpdatedAt($value)
 */
	class PrimeraOpeSoldadura_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Orden_trabajo $orden_trabajo
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo whereUpdatedAt($value)
 */
	class PrimeraOperacionCabezaSoplo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro_exterior
 * @property string|null $longitud
 * @property string|null $diametro_candado
 * @property string|null $longitud_candado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_cnominal whereDiametroCandado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_cnominal whereDiametroExterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_cnominal whereLongitud($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_cnominal whereLongitudCandado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_cnominal whereUpdatedAt($value)
 */
	class PrimeraOperacionCabezaSoplo_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $n_pieza
 * @property string|null $diametro_exterior
 * @property string|null $longitud
 * @property string|null $diametro_candado
 * @property string|null $longitud_candado
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Metas|null $meta
 * @property-read \App\Models\PrimeraOperacionCabezaSoplo $proceso
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereDiametroCandado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereDiametroExterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereLongitud($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereLongitudCandado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_pza whereUpdatedAt($value)
 */
	class PrimeraOperacionCabezaSoplo_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro_exterior1
 * @property string|null $diametro_exterior2
 * @property string|null $longitud1
 * @property string|null $longitud2
 * @property string|null $diametro_candado1
 * @property string|null $diametro_candado2
 * @property string|null $longitud_candado1
 * @property string|null $longitud_candado2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereDiametroCandado1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereDiametroCandado2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereDiametroExterior1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereDiametroExterior2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereLongitud1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereLongitud2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereLongitudCandado1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereLongitudCandado2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrimeraOperacionCabezaSoplo_tolerancia whereUpdatedAt($value)
 */
	class PrimeraOperacionCabezaSoplo_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_clase
 * @property int $cepillado
 * @property int $desbaste_exterior
 * @property int $revision_laterales
 * @property int $pOperacion
 * @property int $barreno_maniobra
 * @property int $sOperacion
 * @property int $soldadura
 * @property int $soldaduraPTA
 * @property int $rectificado
 * @property int $asentado
 * @property int $calificado
 * @property int $acabadoBombillo
 * @property int $acabadoMolde
 * @property int $barreno_profundidad
 * @property int $cavidades
 * @property int $copiado
 * @property int $offSet
 * @property int $palomas
 * @property int $rebajes
 * @property int $grabado
 * @property int $operacionEquipo
 * @property int $embudoCM
 * @property int $primeraOperacionCabezaSoplo
 * @property int $segundaOperacionCabezaSoplo
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos query()
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereAcabadoBombillo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereAcabadoMolde($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereAsentado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereBarrenoManiobra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereBarrenoProfundidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereCalificado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereCavidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereCepillado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereCopiado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereDesbasteExterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereEmbudoCM($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereGrabado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereIdClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereOffSet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereOperacionEquipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos wherePOperacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos wherePalomas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos wherePrimeraOperacionCabezaSoplo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereRebajes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereRectificado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereRevisionLaterales($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereSOperacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereSegundaOperacionCabezaSoplo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Procesos whereSoldaduraPTA($value)
 */
	class Procesos extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ot_id
 * @property int $clase_id
 * @property string|null $ot_nombre
 * @property string|null $clase_nombre
 * @property string $destinatario
 * @property string $estado
 * @property string|null $mensaje_error
 * @property int|null $enviado_por
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Clase|null $clase
 * @property-read string $nombre_enviado_por
 * @property-read \App\Models\Orden_trabajo|null $ordenTrabajo
 * @property-read \App\Models\User|null $usuario
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog whereClaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog whereClaseNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog whereDestinatario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog whereEnviadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog whereMensajeError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog whereOtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog whereOtNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaReporteLog whereUpdatedAt($value)
 */
	class PtaReporteLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ot_id
 * @property int $pieza_id
 * @property string $n_pieza
 * @property string|null $resultado_pico_llenado
 * @property string|null $resultado_pico_soldadura
 * @property string|null $resultado_conexion_llenado
 * @property string|null $resultado_conexion_soldadura
 * @property string|null $resultado_perfilado_llenado
 * @property string|null $resultado_perfilado_soldadura
 * @property string|null $imagen_pico_soldadura
 * @property string|null $imagen_conexion_soldadura
 * @property string|null $imagen_perfilado_soldadura
 * @property bool $liberado_por_admin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Orden_trabajo $ordenTrabajo
 * @property-read \App\Models\Pieza $pieza
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado query()
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereImagenConexionSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereImagenPerfiladoSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereImagenPicoSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereLiberadoPorAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereOtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado wherePiezaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereResultadoConexionLlenado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereResultadoConexionSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereResultadoPerfiladoLlenado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereResultadoPerfiladoSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereResultadoPicoLlenado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereResultadoPicoSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtaResultado whereUpdatedAt($value)
 */
	class PtaResultado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property int $id_clase
 * @property int $operacion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura query()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura whereIdClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura whereOperacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura whereUpdatedAt($value)
 */
	class PySOpeSoldadura extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $altura
 * @property string|null $alturaCandado1
 * @property string|null $alturaCandado2
 * @property string|null $alturaAsientoObturador1
 * @property string|null $alturaAsientoObturador2
 * @property string|null $profundidadSoldadura1
 * @property string|null $profundidadSoldadura2
 * @property string|null $pushUp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal whereAltura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal whereAlturaAsientoObturador1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal whereAlturaAsientoObturador2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal whereAlturaCandado1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal whereAlturaCandado2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal whereProfundidadSoldadura1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal whereProfundidadSoldadura2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal wherePushUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_cnominal whereUpdatedAt($value)
 */
	class PySOpeSoldadura_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $altura
 * @property string|null $alturaCandado1
 * @property string|null $alturaCandado2
 * @property string|null $alturaAsientoObturador1
 * @property string|null $alturaAsientoObturador2
 * @property string|null $profundidadSoldadura1
 * @property string|null $profundidadSoldadura2
 * @property string|null $pushUp
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereAltura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereAlturaAsientoObturador1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereAlturaAsientoObturador2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereAlturaCandado1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereAlturaCandado2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereProfundidadSoldadura1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereProfundidadSoldadura2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza wherePushUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_pza whereUpdatedAt($value)
 */
	class PySOpeSoldadura_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $altura
 * @property string|null $alturaCandado1
 * @property string|null $alturaCandado2
 * @property string|null $alturaAsientoObturador1
 * @property string|null $alturaAsientoObturador2
 * @property string|null $profundidadSoldadura1
 * @property string|null $profundidadSoldadura2
 * @property string|null $pushUp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia whereAltura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia whereAlturaAsientoObturador1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia whereAlturaAsientoObturador2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia whereAlturaCandado1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia whereAlturaCandado2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia whereProfundidadSoldadura1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia whereProfundidadSoldadura2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia wherePushUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PySOpeSoldadura_tolerancia whereUpdatedAt($value)
 */
	class PySOpeSoldadura_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string $n_pieza
 * @property string $n_juego
 * @property string|null $radiof_mordaza
 * @property string|null $radiof_mayor
 * @property string|null $radiof_sufridera
 * @property string|null $profuFinal_CFC
 * @property string|null $profuFinal_mitadMB
 * @property string|null $profuFinal_PCO
 * @property string|null $acetato_MB
 * @property string|null $ensamble
 * @property string|null $distancia_barrenoAli
 * @property string|null $profu_barrenoAliHembra
 * @property string|null $profu_barrenoAliMacho
 * @property string|null $altura_venaHembra
 * @property string|null $altura_venaMacho
 * @property string|null $ancho_vena
 * @property string|null $laterales
 * @property string|null $pin1
 * @property string|null $pin2
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereAcetatoMB($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereAlturaVenaHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereAlturaVenaMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereAnchoVena($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereDistanciaBarrenoAli($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereEnsamble($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereLaterales($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado wherePin1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado wherePin2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereProfuBarrenoAliHembra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereProfuBarrenoAliMacho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereProfuFinalCFC($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereProfuFinalMitadMB($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereProfuFinalPCO($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereRadiofMayor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereRadiofMordaza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereRadiofSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pza_cepillado whereUpdatedAt($value)
 */
	class Pza_cepillado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property mixed $estado
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LiberacionSoldadura> $liberaciones
 * @property-read int|null $liberaciones_count
 * @property-read \App\Models\User|null $operador
 * @method static \Illuminate\Database\Eloquent\Builder|QRGeneradoSoldadura newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QRGeneradoSoldadura newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QRGeneradoSoldadura query()
 */
	class QRGeneradoSoldadura extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder|QrGenerado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QrGenerado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QrGenerado query()
 */
	class QrGenerado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes whereUpdatedAt($value)
 */
	class Rebajes extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $rebaje1
 * @property string|null $rebaje2
 * @property string|null $rebaje3
 * @property string|null $profundidad_bordonio
 * @property string|null $vena1
 * @property string|null $vena2
 * @property string|null $simetria
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal whereProfundidadBordonio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal whereRebaje1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal whereRebaje2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal whereRebaje3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal whereSimetria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal whereVena1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_cnominal whereVena2($value)
 */
	class Rebajes_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $rebaje1
 * @property string|null $rebaje2
 * @property string|null $rebaje3
 * @property string|null $profundidad_bordonio
 * @property string|null $vena1
 * @property string|null $vena2
 * @property string|null $simetria
 * @property string|null $error
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereProfundidadBordonio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereRebaje1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereRebaje2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereRebaje3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereSimetria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereVena1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_pza whereVena2($value)
 */
	class Rebajes_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $rebaje1
 * @property string|null $rebaje2
 * @property string|null $rebaje3
 * @property string|null $profundidad_bordonio
 * @property string|null $vena1
 * @property string|null $vena2
 * @property string|null $simetria
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia whereProfundidadBordonio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia whereRebaje1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia whereRebaje2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia whereRebaje3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia whereSimetria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia whereVena1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rebajes_tolerancia whereVena2($value)
 */
	class Rebajes_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder|RechazoLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RechazoLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RechazoLog query()
 */
	class RechazoLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado whereUpdatedAt($value)
 */
	class Rectificado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int $estado
 * @property string $n_juego
 * @property string|null $cumple
 * @property string|null $error
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza whereCumple($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rectificado_pza whereUpdatedAt($value)
 */
	class Rectificado_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder|RegistroSoldadura newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RegistroSoldadura newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RegistroSoldadura query()
 */
	class RegistroSoldadura extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales query()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales whereUpdatedAt($value)
 */
	class RevLaterales extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $desfasamiento_entrada
 * @property string $desfasamiento_salida
 * @property string $ancho_simetriaEntrada
 * @property string $ancho_simetriaSalida
 * @property string $angulo_corte
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal whereAnchoSimetriaEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal whereAnchoSimetriaSalida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal whereAnguloCorte($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal whereDesfasamientoEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal whereDesfasamientoSalida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_cnominal whereUpdatedAt($value)
 */
	class RevLaterales_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $n_pieza
 * @property string|null $desfasamiento_entrada
 * @property string|null $desfasamiento_salida
 * @property string|null $ancho_simetriaEntrada
 * @property string|null $ancho_simetriaSalida
 * @property string|null $angulo_corte
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereAnchoSimetriaEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereAnchoSimetriaSalida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereAnguloCorte($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereDesfasamientoEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereDesfasamientoSalida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_pza whereUpdatedAt($value)
 */
	class RevLaterales_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $desfasamiento_entrada1
 * @property string $desfasamiento_entrada2
 * @property string $desfasamiento_salida1
 * @property string $desfasamiento_salida2
 * @property string $ancho_simetriaEntrada1
 * @property string $ancho_simetriaEntrada2
 * @property string $ancho_simetriaSalida1
 * @property string $ancho_simetriaSalida2
 * @property string $angulo_corte1
 * @property string $angulo_corte2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereAnchoSimetriaEntrada1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereAnchoSimetriaEntrada2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereAnchoSimetriaSalida1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereAnchoSimetriaSalida2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereAnguloCorte1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereAnguloCorte2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereDesfasamientoEntrada1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereDesfasamientoEntrada2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereDesfasamientoSalida1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereDesfasamientoSalida2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RevLaterales_tolerancia whereUpdatedAt($value)
 */
	class RevLaterales_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder|ScarLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ScarLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ScarLog query()
 */
	class ScarLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ot
 * @property string $no_scar
 * @property string|null $tipo_modelo
 * @property string|null $codigo_modelo
 * @property string|null $proveedor
 * @property string|null $descripcion_no_conformidad
 * @property string|null $causa_raiz
 * @property string|null $acciones_correctivas
 * @property \Illuminate\Support\Carbon|null $fecha_emision
 * @property \Illuminate\Support\Carbon|null $fecha_compromiso
 * @property string $estatus
 * @property int|null $user_id
 * @property string|null $user_nombre
 * @property string|null $cliente_empresa
 * @property string|null $area_solicitante
 * @property string|null $nombre_solicitante
 * @property string|null $nombre_moldura
 * @property bool $evidencia_reporte
 * @property bool $evidencia_dibujos
 * @property bool $evidencia_ayudas
 * @property bool $evidencia_fotos
 * @property bool $evidencia_otro
 * @property bool $accion_regreso
 * @property bool $accion_fabricacion
 * @property bool $accion_otro
 * @property string|null $accion_otro_texto
 * @property string|null $pdf_filename
 * @property string|null $pdf_firmado_filename
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo query()
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereAccionFabricacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereAccionOtro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereAccionOtroTexto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereAccionRegreso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereAccionesCorrectivas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereAreaSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereCausaRaiz($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereClienteEmpresa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereCodigoModelo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereDescripcionNoConformidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereEstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereEvidenciaAyudas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereEvidenciaDibujos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereEvidenciaFotos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereEvidenciaOtro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereEvidenciaReporte($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereFechaCompromiso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereFechaEmision($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereNoScar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereNombreMoldura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereNombreSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo wherePdfFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo wherePdfFirmadoFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereProveedor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereTipoModelo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ScarModelo whereUserNombre($value)
 */
	class ScarModelo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura query()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura whereUpdatedAt($value)
 */
	class SegundaOpeSoldadura extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro1
 * @property string|null $profundidad1
 * @property string|null $diametro2
 * @property string|null $profundidad2
 * @property string|null $diametro3
 * @property string|null $profundidad3
 * @property string|null $diametroSoldadura
 * @property string|null $profundidadSoldadura
 * @property string|null $alturaTotal
 * @property string|null $simetria90G
 * @property string|null $simetriaLinea_Partida
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereAlturaTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereDiametro1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereDiametro2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereDiametro3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereDiametroSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereProfundidad1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereProfundidad2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereProfundidad3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereProfundidadSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereSimetria90G($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereSimetriaLineaPartida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_cnominal whereUpdatedAt($value)
 */
	class SegundaOpeSoldadura_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $n_pieza
 * @property string|null $diametro1
 * @property string|null $profundidad1
 * @property string|null $diametro2
 * @property string|null $profundidad2
 * @property string|null $diametro3
 * @property string|null $profundidad3
 * @property string|null $diametroSoldadura
 * @property string|null $profundidadSoldadura
 * @property string|null $alturaTotal
 * @property string|null $simetria90G
 * @property string|null $simetriaLinea_Partida
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereAlturaTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereDiametro1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereDiametro2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereDiametro3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereDiametroSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereProfundidad1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereProfundidad2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereProfundidad3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereProfundidadSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereSimetria90G($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereSimetriaLineaPartida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_pza whereUpdatedAt($value)
 */
	class SegundaOpeSoldadura_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro1
 * @property string|null $profundidad1
 * @property string|null $diametro2
 * @property string|null $profundidad2
 * @property string|null $diametro3
 * @property string|null $profundidad3
 * @property string|null $diametroSoldadura
 * @property string|null $profundidadSoldadura
 * @property string|null $alturaTotal1
 * @property string|null $alturaTotal2
 * @property string|null $simetria90G1
 * @property string|null $simetria90G2
 * @property string|null $simetriaLinea_Partida
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereAlturaTotal1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereAlturaTotal2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereDiametro1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereDiametro2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereDiametro3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereDiametroSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereProfundidad1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereProfundidad2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereProfundidad3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereProfundidadSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereSimetria90G1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereSimetria90G2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereSimetriaLineaPartida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOpeSoldadura_tolerancia whereUpdatedAt($value)
 */
	class SegundaOpeSoldadura_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Orden_trabajo $orden_trabajo
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo query()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo whereUpdatedAt($value)
 */
	class SegundaOperacionCabezaSoplo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro_exterior
 * @property string|null $longitud
 * @property string|null $diametro_candado
 * @property string|null $longitud_candado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_cnominal whereDiametroCandado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_cnominal whereDiametroExterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_cnominal whereLongitud($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_cnominal whereLongitudCandado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_cnominal whereUpdatedAt($value)
 */
	class SegundaOperacionCabezaSoplo_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $n_pieza
 * @property string|null $diametro_exterior
 * @property string|null $longitud
 * @property string|null $diametro_candado
 * @property string|null $longitud_candado
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Metas|null $meta
 * @property-read \App\Models\SegundaOperacionCabezaSoplo $proceso
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereDiametroCandado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereDiametroExterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereLongitud($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereLongitudCandado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_pza whereUpdatedAt($value)
 */
	class SegundaOperacionCabezaSoplo_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro_exterior1
 * @property string|null $diametro_exterior2
 * @property string|null $longitud1
 * @property string|null $longitud2
 * @property string|null $diametro_candado1
 * @property string|null $diametro_candado2
 * @property string|null $longitud_candado1
 * @property string|null $longitud_candado2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereDiametroCandado1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereDiametroCandado2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereDiametroExterior1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereDiametroExterior2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereLongitud1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereLongitud2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereLongitudCandado1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereLongitudCandado2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SegundaOperacionCabezaSoplo_tolerancia whereUpdatedAt($value)
 */
	class SegundaOperacionCabezaSoplo_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura query()
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura whereUpdatedAt($value)
 */
	class Soldadura extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $lote_id
 * @property string $matricula
 * @property int $numero_bote
 * @property string $peso_kg
 * @property string $estado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $fecha_ingreso
 * @property-read mixed $nombre_soldadura
 * @property-read mixed $numero_factura
 * @property-read mixed $numero_lote
 * @property-read \App\Models\SoldaduraLiberacion|null $liberacion
 * @property-read \App\Models\SoldaduraLote $lote
 * @property-read \App\Models\SoldaduraRecepcionPlanta|null $recepcion
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraBote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraBote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraBote query()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraBote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraBote whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraBote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraBote whereLoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraBote whereMatricula($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraBote whereNumeroBote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraBote wherePesoKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraBote whereUpdatedAt($value)
 */
	class SoldaduraBote extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $bote_id
 * @property int $operador_id
 * @property int $liberado_por
 * @property string $matricula_liberacion
 * @property \Illuminate\Support\Carbon $fecha_hora_liberacion
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SoldaduraBote $bote
 * @property-read \App\Models\User $liberador
 * @property-read \App\Models\User $operador
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion query()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion whereBoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion whereFechaHoraLiberacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion whereLiberadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion whereMatriculaLiberacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion whereOperadorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLiberacion whereUpdatedAt($value)
 */
	class SoldaduraLiberacion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $matricula ID único generado automáticamente
 * @property string $nombre Nombre/tipo de soldadura
 * @property string $lote Número de lote del proveedor
 * @property string $numero_factura Número de factura
 * @property string $peso_total_kg Peso total del lote en kilogramos
 * @property \Illuminate\Support\Carbon $fecha_ingreso Fecha de ingreso del lote
 * @property int $botes_generados Cantidad de botes de 5kg generados
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SoldaduraBote> $botes
 * @property-read int|null $botes_count
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote query()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote whereBotesGenerados($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote whereFechaIngreso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote whereLote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote whereMatricula($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote whereNumeroFactura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote wherePesoTotalKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraLote whereUpdatedAt($value)
 */
	class SoldaduraLote extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA query()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA whereUpdatedAt($value)
 */
	class SoldaduraPTA extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int $estado
 * @property string $n_juego
 * @property string|null $n_pieza ej: '1M', '1H' — identificador por mitad
 * @property string|null $tipo_medida
 * @property string|null $d_conexion_pico
 * @property string|null $d_conexion_obt
 * @property string|null $vl
 * @property string|null $tipo_preparacion
 * @property string|null $perfilado
 * @property string|null $precalentamiento
 * @property string|null $sold_inicial
 * @property string|null $sold_aplicada
 * @property string|null $sold_final
 * @property string|null $corr_inicial
 * @property string|null $corr_aplicada
 * @property string|null $corr_final
 * @property string|null $gas_argon
 * @property string|null $velocidad_calculada
 * @property string|null $resultado
 * @property string|null $defecto_pta
 * @property bool $p2_activa
 * @property string|null $p2_d_conexion_pico
 * @property string|null $p2_d_conexion_obt
 * @property string|null $p2_vl
 * @property string|null $p2_tipo_preparacion
 * @property string|null $p2_perfilado
 * @property string|null $p2_precalentamiento
 * @property string|null $p2_sold_inicial
 * @property string|null $p2_sold_aplicada
 * @property string|null $p2_sold_final
 * @property string|null $p2_corr_inicial
 * @property string|null $p2_corr_aplicada
 * @property string|null $p2_corr_final
 * @property string|null $p2_gas_argon
 * @property string|null $p2_velocidad_calculada
 * @property string|null $p2_resultado
 * @property string|null $p2_defecto_pta
 * @property string|null $p2_observaciones
 * @property string|null $temp_calentado
 * @property string|null $temp_dispositivo
 * @property string|null $limpieza
 * @property string|null $material_soldadura
 * @property string|null $error
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SoldaduraPTA $proceso
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza precalentamientoFila()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereCorrAplicada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereCorrFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereCorrInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereDConexionObt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereDConexionPico($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereDefectoPta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereGasArgon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereLimpieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereMaterialSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2Activa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2CorrAplicada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2CorrFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2CorrInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2DConexionObt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2DConexionPico($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2DefectoPta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2GasArgon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2Observaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2Perfilado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2Precalentamiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2Resultado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2SoldAplicada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2SoldFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2SoldInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2TipoPreparacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2VelocidadCalculada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereP2Vl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza wherePerfilado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza wherePrecalentamiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereResultado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereSoldAplicada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereSoldFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereSoldInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereTempCalentado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereTempDispositivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereTipoMedida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereTipoPreparacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereVelocidadCalculada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraPTA_pza whereVl($value)
 */
	class SoldaduraPTA_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $bote_id
 * @property int $recibido_por
 * @property \Illuminate\Support\Carbon $fecha_hora_recepcion
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SoldaduraBote $bote
 * @property-read \App\Models\User $recibidor
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraRecepcionPlanta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraRecepcionPlanta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraRecepcionPlanta query()
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraRecepcionPlanta whereBoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraRecepcionPlanta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraRecepcionPlanta whereFechaHoraRecepcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraRecepcionPlanta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraRecepcionPlanta whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraRecepcionPlanta whereRecibidoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoldaduraRecepcionPlanta whereUpdatedAt($value)
 */
	class SoldaduraRecepcionPlanta extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int $estado
 * @property string $n_juego
 * @property string|null $pesoxpieza
 * @property int|null $temperatura_precalentado
 * @property int|null $tiempo_aplicacion
 * @property string|null $tipo_soldadura
 * @property string|null $material_soldadura
 * @property string|null $lote
 * @property string|null $error
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereLote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereMaterialSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza wherePesoxpieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereTemperaturaPrecalentado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereTiempoAplicacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereTipoSoldadura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Soldadura_pza whereUpdatedAt($value)
 */
	class Soldadura_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $user_matricula
 * @property string $action
 * @property string|null $details
 * @property string|null $ot
 * @property string|null $clase
 * @property string|null $proceso
 * @property string|null $maquina
 * @property string|null $n_pieza
 * @property string|null $h_inicio
 * @property string|null $h_termino
 * @property int|null $id_ot
 * @property int|null $id_clase
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereHInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereHTermino($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereIdClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereMaquina($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLog whereUserMatricula($value)
 */
	class SystemLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $matricula
 * @property string $nombre
 * @property string $a_paterno
 * @property string $a_materno
 * @property mixed $contrasena
 * @property string $perfil
 * @property \Illuminate\Support\Carbon|null $matricula_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $prod_standard_min
 * @property string $prod_status
 * @property string|null $prod_start_at
 * @property string|null $prod_locked_type
 * @property int $estatus
 * @property-read mixed $name
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-write mixed $password
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAMaterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAPaterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereContrasena($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMatricula($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMatriculaVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePerfil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProdLockedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProdStandardMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProdStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProdStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string $id_ot
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado query()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado whereIdOt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado whereUpdatedAt($value)
 */
	class revCalificado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro_ceja
 * @property string|null $diametro_sufridera
 * @property string|null $altura_sufridera
 * @property string|null $diametro_conexion
 * @property string|null $altura_conexion
 * @property string|null $diametro_caja
 * @property string|null $altura_caja
 * @property string|null $altura_total
 * @property string|null $simetria
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal query()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereAlturaCaja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereAlturaConexion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereAlturaSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereAlturaTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereDiametroCaja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereDiametroCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereDiametroConexion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereDiametroSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereSimetria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_cnominal whereUpdatedAt($value)
 */
	class revCalificado_cnominal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_pza
 * @property int|null $id_meta
 * @property int $id_proceso
 * @property int|null $correcto
 * @property int $estado
 * @property string|null $n_juego
 * @property string|null $diametro_ceja
 * @property string|null $diametro_sufridera
 * @property string|null $altura_sufridera
 * @property string|null $diametro_conexion
 * @property string|null $altura_conexion
 * @property string|null $diametro_caja
 * @property string|null $altura_caja
 * @property string|null $altura_total
 * @property string|null $simetria
 * @property string|null $observaciones
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $n_pieza
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza query()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereAlturaCaja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereAlturaConexion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereAlturaSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereAlturaTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereCorrecto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereDiametroCaja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereDiametroCeja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereDiametroConexion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereDiametroSufridera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereIdMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereIdPza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereNJuego($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereNPieza($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereSimetria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_pza whereUpdatedAt($value)
 */
	class revCalificado_pza extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_proceso
 * @property string|null $diametro_ceja1
 * @property string|null $diametro_ceja2
 * @property string|null $diametro_sufridera1
 * @property string|null $diametro_sufridera2
 * @property string|null $altura_sufridera1
 * @property string|null $altura_sufridera2
 * @property string|null $diametro_conexion1
 * @property string|null $diametro_conexion2
 * @property string|null $altura_conexion1
 * @property string|null $altura_conexion2
 * @property string|null $diametro_caja1
 * @property string|null $diametro_caja2
 * @property string|null $altura_caja1
 * @property string|null $altura_caja2
 * @property string|null $altura_total1
 * @property string|null $altura_total2
 * @property string|null $simetria1
 * @property string|null $simetria2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia query()
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereAlturaCaja1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereAlturaCaja2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereAlturaConexion1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereAlturaConexion2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereAlturaSufridera1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereAlturaSufridera2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereAlturaTotal1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereAlturaTotal2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereDiametroCaja1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereDiametroCaja2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereDiametroCeja1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereDiametroCeja2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereDiametroConexion1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereDiametroConexion2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereDiametroSufridera1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereDiametroSufridera2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereIdProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereSimetria1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereSimetria2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|revCalificado_tolerancia whereUpdatedAt($value)
 */
	class revCalificado_tolerancia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $id_clase
 * @property string $clase
 * @property string $tamanio
 * @property string $proceso
 * @property string $tiempo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|tiempoproduccion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|tiempoproduccion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|tiempoproduccion query()
 * @method static \Illuminate\Database\Eloquent\Builder|tiempoproduccion whereClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tiempoproduccion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tiempoproduccion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tiempoproduccion whereIdClase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tiempoproduccion whereProceso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tiempoproduccion whereTamanio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tiempoproduccion whereTiempo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|tiempoproduccion whereUpdatedAt($value)
 */
	class tiempoproduccion extends \Eloquent {}
}

