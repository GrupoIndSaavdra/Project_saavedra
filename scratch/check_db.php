<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $tables = DB::select('SHOW TABLES');
    echo "Tablas encontradas:\n";
    foreach ($tables as $table) {
        echo "- " . current((array)$table) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
