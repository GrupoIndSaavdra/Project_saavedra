<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PerfilesPuestosSeeder extends Seeder
{
    /**
     * Seed de catálogos oficiales de perfiles (roles) y puestos.
     * Seguro para producción (no destructivo, sin dependencia de áreas).
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

        // ── 2. Catálogo Oficial de Puestos (Independiente de áreas) ──
        if (Schema::hasTable('puestos')) {
            $puestos = [
                ['id' => 1, 'nombre' => 'Director / Master', 'codigo_nivel' => '1'],
                ['id' => 2, 'nombre' => 'Jefe de Departamento', 'codigo_nivel' => '1'],
                ['id' => 3, 'nombre' => 'Auxiliar', 'codigo_nivel' => '2'],
                ['id' => 4, 'nombre' => 'Becario', 'codigo_nivel' => '3'],
                ['id' => 5, 'nombre' => 'Jefe de Departamento', 'codigo_nivel' => '1'],
                ['id' => 6, 'nombre' => 'Auxiliar', 'codigo_nivel' => '2'],
                ['id' => 7, 'nombre' => 'Becario', 'codigo_nivel' => '3'],
                ['id' => 8, 'nombre' => 'Jefe de Departamento', 'codigo_nivel' => '1'],
                ['id' => 9, 'nombre' => 'Auxiliar', 'codigo_nivel' => '2'],
                ['id' => 10, 'nombre' => 'Becario', 'codigo_nivel' => '3'],
                ['id' => 11, 'nombre' => 'Jefe de Departamento', 'codigo_nivel' => '1'],
                ['id' => 12, 'nombre' => 'Auxiliar', 'codigo_nivel' => '2'],
                ['id' => 13, 'nombre' => 'Becario', 'codigo_nivel' => '3'],
                ['id' => 14, 'nombre' => 'Jefe de Taller', 'codigo_nivel' => '1'],
                ['id' => 15, 'nombre' => 'Auxiliar de Supervisión', 'codigo_nivel' => '2'],
                ['id' => 16, 'nombre' => 'Becario', 'codigo_nivel' => '3'],
                ['id' => 17, 'nombre' => 'Operador Torno CNC', 'codigo_nivel' => '4'],
                ['id' => 18, 'nombre' => 'Operador Centro de Maquinados', 'codigo_nivel' => '5'],
                ['id' => 19, 'nombre' => 'Ayudante General', 'codigo_nivel' => '6'],
                ['id' => 20, 'nombre' => 'Jefe de Acabados', 'codigo_nivel' => '1'],
                ['id' => 21, 'nombre' => 'Auxiliar de Supervisión', 'codigo_nivel' => '2'],
                ['id' => 22, 'nombre' => 'Becario', 'codigo_nivel' => '3'],
                ['id' => 23, 'nombre' => 'Operador de Acabados', 'codigo_nivel' => '4'],
                ['id' => 24, 'nombre' => 'Ayudante General', 'codigo_nivel' => '5'],
                ['id' => 25, 'nombre' => 'Jefe de Departamento', 'codigo_nivel' => '1'],
                ['id' => 26, 'nombre' => 'Auxiliar', 'codigo_nivel' => '2'],
                ['id' => 27, 'nombre' => 'Becario', 'codigo_nivel' => '3'],
                ['id' => 28, 'nombre' => 'Jefe de Turno', 'codigo_nivel' => '1'],
                ['id' => 29, 'nombre' => 'Auxiliar de Supervisión', 'codigo_nivel' => '2'],
                ['id' => 30, 'nombre' => 'Becario', 'codigo_nivel' => '3'],
                ['id' => 31, 'nombre' => 'Operador Torno CNC', 'codigo_nivel' => '4'],
                ['id' => 32, 'nombre' => 'Soldador PTA', 'codigo_nivel' => '5'],
                ['id' => 33, 'nombre' => 'Operador Centro de Maquinados', 'codigo_nivel' => '6'],
                ['id' => 34, 'nombre' => 'Ayudante General', 'codigo_nivel' => '7'],
                ['id' => 35, 'nombre' => 'Jefe de Departamento', 'codigo_nivel' => '1'],
                ['id' => 36, 'nombre' => 'Auxiliar', 'codigo_nivel' => '2'],
                ['id' => 37, 'nombre' => 'Becario', 'codigo_nivel' => '3'],
                ['id' => 38, 'nombre' => 'Jefe de Sistemas', 'codigo_nivel' => '1'],
                ['id' => 39, 'nombre' => 'Auxiliar de Sistemas', 'codigo_nivel' => '2'],
                ['id' => 40, 'nombre' => 'Becario', 'codigo_nivel' => '3'],
            ];

            foreach ($puestos as $p) {
                DB::table('puestos')->updateOrInsert(
                    ['id' => $p['id']],
                    [
                        'nombre' => $p['nombre'],
                        'codigo_nivel' => $p['codigo_nivel'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
