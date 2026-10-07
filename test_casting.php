<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Auth::loginUsingId(1);

$vm = new \App\ViewModels\AlmacenTableRowViewModel(
    \App\Models\FundicionHistory::where('ot', 'OT 9993 - SALIME 60 ML')->first(),
    'activa',
    'almacen'
);

var_dump(array_map(function($x) { return $x['nombre']; }, $vm->almacenPreordenesFabEnCasting));
