<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reestructuración de catálogos: perfiles (roles), áreas (departamentos) y puestos.
 *
 * - areas.codigo_prefijo (CHAR 1) desacopla el ID autoincremental del prefijo de matrícula.
 * - puestos.codigo_nivel (CHAR 1) define el tercer dígito del prefijo.
 * - perfiles.codigo_perfil (CHAR 1) define el primer dígito del prefijo.
 * - users: se eliminan las columnas VARCHAR redundantes `area` y `puesto`; se usan FKs.
 * - users.planta se normaliza a ENUM('TECAMAC','CDMX').
 *
 * Es segura tanto para servidores con datos legacy (columnas area/puesto presentes)
 * como para entornos donde una versión previa de esta migración ya se ejecutó.
 */
return new class extends Migration {
    public function up(): void
    {
        $now = now();

        // ── 0. Snapshot del catálogo previo de puestos (si existía) para remapear usuarios ──
        $oldPuestos = [];
        if (Schema::hasTable('puestos')) {
            $codigoCol = Schema::hasColumn('puestos', 'codigo_nivel') ? 'codigo_nivel' : (Schema::hasColumn('puestos', 'codigo_puesto') ? 'codigo_puesto' : null);
            foreach (DB::table('puestos')->get() as $p) {
                $oldPuestos[$p->id] = [
                    'area_id' => (int) $p->area_id,
                    'codigo' => $codigoCol ? (int) $p->{$codigoCol} : 1,
                ];
            }
        }

        // ── 1. Recrear catálogos desde cero ──
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('puestos');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('perfiles');
        Schema::enableForeignKeyConstraints();

        Schema::create('perfiles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->char('codigo_perfil', 1);
            $table->timestamps();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('planta', ['TECAMAC', 'CDMX', 'AMBAS'])->default('AMBAS');
            $table->char('codigo_prefijo', 1);
            $table->timestamps();
        });

        Schema::create('puestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->char('codigo_nivel', 1);
            $table->timestamps();
            $table->unique(['area_id', 'codigo_nivel']);
        });

        // ── 2. Seed: Perfiles (roles puros del sistema) ──
        $perfiles = [
            [1, 'Master', '1'],
            [2, 'Administrador', '2'],
            [3, 'Supervisor', '3'],
            [4, 'Ingeniería', '4'],
            [5, 'Diseño y programación', '5'],
            [6, 'Ventas', '6'],
            [7, 'Almacén', '7'],
            [8, 'Calidad', '8'],
            [9, 'Operador', '9'],
            [10, 'Sistemas', '0'],
        ];
        DB::table('perfiles')->insert(array_map(fn ($p) => [
            'id' => $p[0], 'nombre' => $p[1], 'codigo_perfil' => $p[2], 'created_at' => $now, 'updated_at' => $now,
        ], $perfiles));

        // ── 3. Seed: Áreas (departamentos; "Master" NO es un área) ──
        $areas = [
            ['id' => 1,  'nombre' => 'Dirección General',     'planta' => 'AMBAS',   'codigo_prefijo' => '1'],
            ['id' => 2,  'nombre' => 'Ventas',                'planta' => 'CDMX',    'codigo_prefijo' => '2'],
            ['id' => 3,  'nombre' => 'Diseño y Programación', 'planta' => 'TECAMAC', 'codigo_prefijo' => '3'],
            ['id' => 4,  'nombre' => 'Ingeniería',            'planta' => 'CDMX',    'codigo_prefijo' => '4'],
            ['id' => 5,  'nombre' => 'Calidad',               'planta' => 'AMBAS',   'codigo_prefijo' => '5'],
            ['id' => 6,  'nombre' => 'Taller de Maquinados',  'planta' => 'CDMX',    'codigo_prefijo' => '6'],
            ['id' => 7,  'nombre' => 'Acabados',              'planta' => 'CDMX',    'codigo_prefijo' => '7'],
            ['id' => 8,  'nombre' => 'Almacén',               'planta' => 'TECAMAC', 'codigo_prefijo' => '8'],
            ['id' => 9,  'nombre' => 'Producción',            'planta' => 'TECAMAC', 'codigo_prefijo' => '9'],
            ['id' => 10, 'nombre' => 'Administración',        'planta' => 'AMBAS',   'codigo_prefijo' => '0'], // 0 evita desborde a 2 dígitos
            ['id' => 11, 'nombre' => 'Sistemas / TI',         'planta' => 'AMBAS',   'codigo_prefijo' => '5'],
        ];
        DB::table('areas')->insert(array_map(fn ($a) => $a + ['created_at' => $now, 'updated_at' => $now], $areas));

        // ── 4. Seed: Puestos por área (codigo_nivel = 3er dígito del prefijo) ──
        $estandar = [['1', 'Jefe de Departamento'], ['2', 'Auxiliar'], ['3', 'Becario']];
        $puestos = [
            1 => [['1', 'Director / Master']],
            2 => $estandar,
            3 => $estandar,
            4 => $estandar,
            5 => $estandar,
            6 => [['1', 'Jefe de Taller'], ['2', 'Auxiliar de Supervisión'], ['3', 'Becario'], ['4', 'Operador Torno CNC'], ['5', 'Operador Centro de Maquinados'], ['6', 'Ayudante General']],
            7 => [['1', 'Jefe de Acabados'], ['2', 'Auxiliar de Supervisión'], ['3', 'Becario'], ['4', 'Operador de Acabados'], ['5', 'Ayudante General']],
            8 => $estandar,
            9 => [['1', 'Jefe de Turno'], ['2', 'Auxiliar de Supervisión'], ['3', 'Becario'], ['4', 'Operador Torno CNC'], ['5', 'Soldador PTA'], ['6', 'Operador Centro de Maquinados'], ['7', 'Ayudante General']],
            10 => $estandar,
            11 => [['1', 'Jefe de Sistemas'], ['2', 'Auxiliar de Sistemas'], ['3', 'Becario']],
        ];
        $rows = [];
        foreach ($puestos as $areaId => $lista) {
            foreach ($lista as [$nivel, $nombre]) {
                $rows[] = ['area_id' => $areaId, 'nombre' => $nombre, 'codigo_nivel' => $nivel, 'created_at' => $now, 'updated_at' => $now];
            }
        }
        DB::table('puestos')->insert($rows);

        // Índice (area_id, nivel) -> puesto_id
        $puestoIdx = [];
        foreach (DB::table('puestos')->get() as $p) {
            $puestoIdx[$p->area_id][(int) $p->codigo_nivel] = $p->id;
        }

        // ── 5. Columnas FK en users ──
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'perfil_id')) {
                $table->unsignedBigInteger('perfil_id')->nullable()->after('perfil');
            }
            if (!Schema::hasColumn('users', 'area_id')) {
                $table->unsignedBigInteger('area_id')->nullable()->after('perfil_id');
            }
            if (!Schema::hasColumn('users', 'puesto_id')) {
                $table->unsignedBigInteger('puesto_id')->nullable()->after('area_id');
            }
        });

        // ── 6. Normalizar planta -> ENUM('TECAMAC','CDMX') ──
        DB::table('users')->whereRaw("UPPER(planta) LIKE '%CDMX%' OR UPPER(planta) LIKE '%RAZA%'")->update(['planta' => 'CDMX']);
        DB::table('users')->whereRaw("UPPER(planta) LIKE 'TEC%'")->update(['planta' => 'TECAMAC']);
        DB::table('users')->whereNotIn('planta', ['TECAMAC', 'CDMX'])->update(['planta' => null]);
        DB::statement("ALTER TABLE users MODIFY planta ENUM('TECAMAC','CDMX') NULL");

        // ── 7. Remapear usuarios existentes (sin tocar matrícula ni contraseña) ──
        $hasLegacy = Schema::hasColumn('users', 'area') && Schema::hasColumn('users', 'puesto');

        $resolverPuesto = function (int $areaId, int $nivel, int $perfilId) use ($puestoIdx): ?int {
            if (isset($puestoIdx[$areaId][$nivel])) {
                return $puestoIdx[$areaId][$nivel];
            }
            $niveles = array_keys($puestoIdx[$areaId] ?? []);
            if (!$niveles) {
                return null;
            }
            sort($niveles);
            // Operador -> primer puesto operativo (>=4); resto -> Auxiliar (2) o el primero disponible
            $preferido = $perfilId === 9 ? (in_array(4, $niveles, true) ? 4 : end($niveles)) : (in_array(2, $niveles, true) ? 2 : $niveles[0]);
            return $puestoIdx[$areaId][$preferido];
        };

        $nivelPorTexto = function (string $puesto, int $default): int {
            if (str_contains($puesto, 'BECARI')) return 3;
            if (str_contains($puesto, 'AUX')) return 2;
            if (str_contains($puesto, 'JEFE') || str_contains($puesto, 'SUPERVISOR') || str_contains($puesto, 'ENCARGADO')) return 1;
            return $default;
        };

        // Remap de niveles del catálogo previo de Producción (área 9) al nuevo
        $remapProduccion = [1 => 1, 2 => 2, 3 => 4, 4 => 6, 5 => 5, 6 => 7, 7 => 3];

        foreach (DB::table('users')->orderBy('id')->cursor() as $u) {
            $planta = $u->planta ?: 'TECAMAC';
            $esCdmx = $planta === 'CDMX';

            if ($hasLegacy) {
                // Códigos legacy: 1 Admin, 2 Operador, 3 Master, 4 Calidad, 5 Almacén, 6 Supervisor, 8 Calidad PTA
                $old = trim((string) $u->perfil);
                $area = mb_strtoupper(trim((string) $u->area), 'UTF-8');
                $puesto = mb_strtoupper(trim((string) $u->puesto), 'UTF-8');
                $nombre = mb_strtoupper(trim(($u->nombre ?? '') . ' ' . ($u->a_paterno ?? '')), 'UTF-8');

                if (str_contains($puesto, 'SISTEMAS') || str_contains($nombre, 'SISTEMAS')) {
                    [$perfilId, $areaId, $nivel] = [10, 11, 1];
                } elseif (in_array($old, ['3', 'Master', 'Maestro'], true) || str_contains($area, 'MASTER') || str_contains($puesto, 'MASTER')) {
                    [$perfilId, $areaId, $nivel] = [1, 1, 1];
                } elseif (in_array($old, ['1', 'Administrador', 'Admin'], true)) {
                    [$perfilId, $areaId, $nivel] = [2, 10, $nivelPorTexto($puesto, 1)];
                } elseif (in_array($old, ['6', 'Supervisor'], true)) {
                    $areaId = $esCdmx ? (str_contains($area, 'ACABADO') ? 7 : 6) : 9;
                    [$perfilId, $nivel] = [3, str_contains($puesto, 'AUX') ? 2 : 1];
                } elseif (in_array($old, ['4', '8'], true) || str_contains($area, 'CALIDAD')) {
                    [$perfilId, $areaId, $nivel] = [8, 5, $nivelPorTexto($puesto, 2)];
                } elseif ($old === '5' || str_contains($area, 'ALMAC')) {
                    [$perfilId, $areaId, $nivel] = [7, 8, $nivelPorTexto($puesto, 2)];
                } elseif (str_contains($area, 'INGENIER')) {
                    [$perfilId, $areaId, $nivel] = [4, 4, $nivelPorTexto($puesto, 2)];
                } elseif (str_contains($area, 'PROGRAMA') || str_contains($area, 'DISE') || str_contains($puesto, 'CAD')) {
                    [$perfilId, $areaId, $nivel] = [5, 3, $nivelPorTexto($puesto, 2)];
                } elseif (str_contains($area, 'VENTA')) {
                    [$perfilId, $areaId, $nivel] = [6, 2, $nivelPorTexto($puesto, 2)];
                } else {
                    // Operador de piso
                    $perfilId = 9;
                    $areaId = $esCdmx ? (str_contains($area, 'ACABADO') ? 7 : 6) : 9;
                    $nivel = match (true) {
                        str_contains($puesto, 'BECARI') => 3,
                        str_contains($puesto, 'SOLDA') && $areaId === 9 => 5,
                        str_contains($puesto, 'CENTRO') => $areaId === 9 ? 6 : 5,
                        str_contains($puesto, 'AYUDANTE') => $areaId === 9 ? 7 : ($areaId === 6 ? 6 : 5),
                        default => 4,
                    };
                }
            } else {
                // Entorno donde ya corrió una versión previa: perfil_id ya está en el esquema nuevo
                $perfilId = (int) ($u->perfil_id ?: $u->perfil ?: 9);
                $areaId = (int) ($u->area_id ?: 0);
                $oldInfo = $oldPuestos[$u->puesto_id] ?? null;
                $nivel = $oldInfo['codigo'] ?? 2;
                if ($areaId === 9) {
                    $nivel = $remapProduccion[$nivel] ?? 4;
                }
                if (!isset($puestoIdx[$areaId])) {
                    $areaId = match ($perfilId) {
                        2 => 10, 3 => ($esCdmx ? 6 : 9), 4 => 4, 5 => 3, 6 => 2, 7 => 8, 8 => 5,
                        default => $esCdmx ? 6 : 9,
                    };
                }
            }

            // Reglas duras de rol
            if ($perfilId === 1) {
                [$areaId, $nivel] = [1, 1];
            } elseif ($perfilId === 10) {
                $areaId = 11;
            }

            DB::table('users')->where('id', $u->id)->update([
                'perfil' => (string) $perfilId,
                'perfil_id' => $perfilId,
                'area_id' => $areaId,
                'puesto_id' => $resolverPuesto($areaId, (int) $nivel, $perfilId),
                'planta' => $u->planta ?: ($areaId === 6 || $areaId === 7 ? 'CDMX' : 'TECAMAC'),
            ]);
        }

        // ── 8. Eliminar columnas redundantes y crear llaves foráneas ──
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'area')) {
                $table->dropColumn('area');
            }
            if (Schema::hasColumn('users', 'puesto')) {
                $table->dropColumn('puesto');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('perfil_id')->references('id')->on('perfiles')->nullOnDelete();
            $table->foreign('area_id')->references('id')->on('areas')->nullOnDelete();
            $table->foreign('puesto_id')->references('id')->on('puestos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['perfil_id']);
            $table->dropForeign(['area_id']);
            $table->dropForeign(['puesto_id']);
        });

        DB::statement('ALTER TABLE users MODIFY planta VARCHAR(50) NULL');
        DB::table('users')->where('planta', 'TECAMAC')->update(['planta' => 'TECÁMAC']);

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'area')) {
                $table->string('area', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'puesto')) {
                $table->string('puesto', 100)->nullable();
            }
            $table->dropColumn(['perfil_id', 'area_id', 'puesto_id']);
        });

        Schema::dropIfExists('puestos');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('perfiles');
    }
};
