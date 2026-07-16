<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = new \Illuminate\Http\Request();
$req->setMethod('GET');
$req->merge(['status' => 'Todos']);
$c = new \App\Http\Controllers\PzasGeneralesController;
$res = $c->getPiecesRequest($req);
$data = $res->getData();
file_put_contents(__DIR__.'/pieces_full.json', json_encode(['pieces' => $data['pieces'], 'infoPiezas' => $data['infoPieces']]));
