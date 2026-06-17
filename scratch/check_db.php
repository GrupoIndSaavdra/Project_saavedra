<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "tipo_preparacion: " . Schema::getColumnType('soldaduraPTA_pza', 'tipo_preparacion') . "\n";
echo "p2_tipo_preparacion: " . Schema::getColumnType('soldaduraPTA_pza', 'p2_tipo_preparacion') . "\n";
echo "material_soldadura: " . Schema::getColumnType('soldaduraPTA_pza', 'material_soldadura') . "\n";
