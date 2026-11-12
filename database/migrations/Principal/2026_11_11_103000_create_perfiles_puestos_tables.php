<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reestructuración de catálogos: perfiles (roles) y puestos.
 *
 * - puestos.codigo_nivel (CHAR 1) define el segundo dígito del prefijo de matrícula.
 * - perfiles.codigo_perfil (CHAR 1) define el primer dígito del prefijo.
 * - users: se eliminan las columnas redundantes `area` y `puesto`; se usan FKs hacia perfiles y puestos.
 * - users.planta se normaliza a ENUM('TECAMAC','CDMX').
 */
return new class extends Migration {
    public function up(): void
    {
        $now = now();

        // ── 1. Recrear catálogos perfiles y puestos ──
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

        Schema::create('puestos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('area_id')->default(1);
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

        // ── 3. Seed: Puestos ──
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

        // ── 4. Columnas FK en users ──
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'perfil_id')) {
                $table->unsignedBigInteger('perfil_id')->nullable()->after('perfil');
            }
            if (!Schema::hasColumn('users', 'puesto_id')) {
                $table->unsignedBigInteger('puesto_id')->nullable()->after('perfil_id');
            }
        });

        // ── 5. Normalizar planta -> ENUM('TECAMAC','CDMX') ──
        DB::table('users')->whereRaw("UPPER(planta) LIKE '%CDMX%' OR UPPER(planta) LIKE '%RAZA%'")->update(['planta' => 'CDMX']);
        DB::table('users')->whereRaw("UPPER(planta) LIKE 'TEC%'")->update(['planta' => 'TECAMAC']);
        DB::table('users')->whereNotIn('planta', ['TECAMAC', 'CDMX'])->update(['planta' => null]);
        DB::statement("ALTER TABLE users MODIFY planta ENUM('TECAMAC','CDMX') NULL");

        // ── 6. Actualizar usuarios existentes por grupos mapeados ──
        $updates = [
            ['perfil' => '1', 'perfil_id' => 1, 'puesto_id' => 1, 'ids' => [1, 4, 63, 82, 171, 178]],
            ['perfil' => '2', 'perfil_id' => 2, 'puesto_id' => 35, 'ids' => [12, 32, 34, 76, 90, 91, 109]],
            ['perfil' => '2', 'perfil_id' => 2, 'puesto_id' => 37, 'ids' => [169]],
            ['perfil' => '3', 'perfil_id' => 3, 'puesto_id' => 28, 'ids' => [164, 165, 174]],
            ['perfil' => '5', 'perfil_id' => 5, 'puesto_id' => 5, 'ids' => [33]],
            ['perfil' => '7', 'perfil_id' => 7, 'puesto_id' => 25, 'ids' => [94]],
            ['perfil' => '7', 'perfil_id' => 7, 'puesto_id' => 26, 'ids' => [114, 167]],
            ['perfil' => '8', 'perfil_id' => 8, 'puesto_id' => 11, 'ids' => [103, 108]],
            ['perfil' => '8', 'perfil_id' => 8, 'puesto_id' => 12, 'ids' => [26, 27, 68, 87, 95, 105, 106, 107, 111]],
            ['perfil' => '9', 'perfil_id' => 9, 'puesto_id' => 30, 'ids' => [172]],
            ['perfil' => '9', 'perfil_id' => 9, 'puesto_id' => 31, 'ids' => [13, 14, 18, 19, 22, 23, 24, 31, 38, 42, 43, 48, 50, 52, 55, 59, 61, 62, 66, 71, 72, 74, 78, 97, 99, 100, 101, 102, 104, 110]],
            ['perfil' => '9', 'perfil_id' => 9, 'puesto_id' => 32, 'ids' => [39, 40, 89, 112, 170, 179, 180]],
            ['perfil' => '9', 'perfil_id' => 9, 'puesto_id' => 33, 'ids' => [5, 6, 20, 29, 45, 54, 67, 96, 163, 168]],
            ['perfil' => '9', 'perfil_id' => 9, 'puesto_id' => 34, 'ids' => [46, 49, 77, 83, 86, 166]],
            ['perfil' => '10', 'perfil_id' => 10, 'puesto_id' => 38, 'ids' => [175]],
        ];

        foreach ($updates as $up) {
            DB::table('users')->whereIn('id', $up['ids'])->update([
                'perfil' => $up['perfil'],
                'perfil_id' => $up['perfil_id'],
                'puesto_id' => $up['puesto_id'],
                'planta' => 'TECAMAC',
            ]);
        }

        // ── 7. Eliminar columnas redundantes y crear llaves foráneas ──
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'area')) {
                $table->dropColumn('area');
            }
            if (Schema::hasColumn('users', 'puesto')) {
                $table->dropColumn('puesto');
            }
            if (Schema::hasColumn('users', 'area_id')) {
                $table->dropColumn('area_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('perfil_id')->references('id')->on('perfiles')->nullOnDelete();
            $table->foreign('puesto_id')->references('id')->on('puestos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['perfil_id']);
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
            $table->dropColumn(['perfil_id', 'puesto_id']);
        });

        Schema::dropIfExists('puestos');
        Schema::dropIfExists('perfiles');
    }
};
