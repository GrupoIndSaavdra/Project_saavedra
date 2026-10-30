<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $dbName = config('database.connections.mysql.database');
    echo "Conectando a base de datos: $dbName...\n";

    $tables = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?", [$dbName]);

    if (empty($tables)) {
        echo "No se encontraron tablas en $dbName.\n";
        exit(0);
    }

    echo "Tablas encontradas: " . count($tables) . "\n";

    $prismaOutput = "// Prisma Schema generado automáticamente desde MySQL: $dbName\n";
    $prismaOutput .= "// Creado para navegación rápida e introspección de tokens de agentes de IA.\n\n";
    $prismaOutput .= "datasource db {\n  provider = \"mysql\"\n  url      = env(\"DATABASE_URL\")\n}\n\n";

    foreach ($tables as $t) {
        $tableName = $t->TABLE_NAME;
        $columns = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY, EXTRA, COLUMN_COMMENT
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [$dbName, $tableName]);

        // Model name in PascalCase
        $modelName = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $tableName)));

        $prismaOutput .= "model $modelName {\n";
        $prismaOutput .= "  // Mapea a la tabla: $tableName\n";

        foreach ($columns as $c) {
            $colName = $c->COLUMN_NAME;
            $dataType = strtolower($c->DATA_TYPE);
            $nullable = ($c->IS_NULLABLE === 'YES');
            $isPk = ($c->COLUMN_KEY === 'PRI');
            $extra = $c->EXTRA;

            // Map MySQL to Prisma types
            $prismaType = match($dataType) {
                'int', 'tinyint', 'smallint', 'mediumint', 'bigint' => 'Int',
                'varchar', 'char', 'text', 'mediumtext', 'longtext', 'enum' => 'String',
                'decimal', 'double', 'float' => 'Float',
                'datetime', 'timestamp', 'date', 'time' => 'DateTime',
                'boolean' => 'Boolean',
                'json' => 'Json',
                default => 'String'
            };

            if ($nullable && !$isPk) {
                $prismaType .= '?';
            }

            $attributes = [];
            if ($isPk) {
                $attributes[] = '@id';
                if (str_contains(strtolower($extra), 'auto_increment')) {
                    $attributes[] = '@default(autoincrement())';
                }
            }
            if ($c->COLUMN_KEY === 'UNI') {
                $attributes[] = '@unique';
            }

            $attrStr = count($attributes) > 0 ? ' ' . implode(' ', $attributes) : '';
            $prismaOutput .= "  $colName $prismaType$attrStr\n";
        }

        $prismaOutput .= "  @@map(\"$tableName\")\n";
        $prismaOutput .= "}\n\n";
    }

    if (!is_dir(__DIR__ . '/../prisma')) {
        mkdir(__DIR__ . '/../prisma', 0755, true);
    }

    file_put_contents(__DIR__ . '/../prisma/schema.prisma', $prismaOutput);

    echo "✅ Éxito: Generado prisma/schema.prisma con " . count($tables) . " tablas.\n";

} catch (\Throwable $e) {
    echo "❌ Error al introspeccionar BD: " . $e->getMessage() . "\n";
    exit(1);
}

