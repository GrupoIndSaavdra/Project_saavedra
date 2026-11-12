<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PerfilesPuestosSeeder extends Seeder
{
    /**
     * Seed de catálogos oficiales de perfiles (roles) y puestos.
     * Seguro para producción (no destructivo, usa updateOrInsert).
     */
    public function run(): void
    {
        $now = now();

        // ── 1. Catálogo Oficial de Perfiles ──
        $perfiles = [
            ['id' => 1, 'nombre' => 'Master', 'codigo_perfil' => '1'],
            ['id' => 2, 'nombre' => 'Administrador', 'codigo_perfil' => '2'],
            ['id' => 3, 'nombre' => 'Supervisor', 'codigo_perfil' => '3'],
            ['id' => 4, 'nombre' => 'Ingeniería', 'codigo_perfil' => '4'],
            ['id' => 5, 'nombre' => 'Diseño y programación', 'codigo_perfil' => '5'],
            ['id' => 6, 'nombre' => 'Ventas', 'codigo_perfil' => '6'],
            ['id' => 7, 'nombre' => 'Almacén', 'codigo_perfil' => '7'],
            ['id' => 8, 'nombre' => 'Calidad', 'codigo_perfil' => '8'],
            ['id' => 9, 'nombre' => 'Operador', 'codigo_perfil' => '9'],
            ['id' => 10, 'nombre' => 'Sistemas', 'codigo_perfil' => '0'],
        ];

        if (Schema::hasTable('perfiles')) {
            foreach ($perfiles as $p) {
                DB::table('perfiles')->updateOrInsert(
                    ['id' => $p['id']],
                    [
                        'nombre' => $p['nombre'],
                        'codigo_perfil' => $p['codigo_perfil'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        // ── 2. Catálogo Oficial de Puestos ──
        if (Schema::hasTable('puestos')) {
            $estandar = [
                ['1', 'Jefe de Departamento'],
                ['2', 'Auxiliar'],
                ['3', 'Becario'],
            ];

            $mapa = [
                1 => [['1', 'Director / Master']],
                2 => $estandar,
                3 => $estandar,
                4 => $estandar,
                5 => $estandar,
                6 => [
                    ['1', 'Jefe de Taller'],
                    ['2', 'Auxiliar de Supervisión'],
                    ['3', 'Becario'],
                    ['4', 'Operador Torno CNC'],
                    ['5', 'Operador Centro de Maquinados'],
                    ['6', 'Ayudante General'],
                ],
                7 => [
                    ['1', 'Jefe de Acabados'],
                    ['2', 'Auxiliar de Supervisión'],
                    ['3', 'Becario'],
                    ['4', 'Operador de Acabados'],
                    ['5', 'Ayudante General'],
                ],
                8 => $estandar,
                9 => [
                    ['1', 'Jefe de Turno'],
                    ['2', 'Auxiliar de Supervisión'],
                    ['3', 'Becario'],
                    ['4', 'Operador Torno CNC'],
                    ['5', 'Soldador PTA'],
                    ['6', 'Operador Centro de Maquinados'],
                    ['7', 'Ayudante General'],
                ],
                10 => $estandar,
                11 => [
                    ['1', 'Jefe de Sistemas'],
                    ['2', 'Auxiliar de Sistemas'],
                    ['3', 'Becario'],
                ],
            ];

            foreach ($mapa as $areaId => $lista) {
                foreach ($lista as [$nivel, $nombre]) {
                    DB::table('puestos')->updateOrInsert(
                        ['area_id' => $areaId, 'codigo_nivel' => $nivel],
                        [
                            'nombre' => $nombre,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        }
    }
}

