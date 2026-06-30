<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create("/api/fake", "GET");
$app->instance("request", $request);
Illuminate\Support\Facades\Auth::loginUsingId(1); // Admin
$controller = new \App\Http\Controllers\AlmacenFundicionController();
$res = $controller->getFiles(Illuminate\Http\Request::create("/?ot=OT 1091 - PRUEBADDF", "GET"));
echo json_encode(json_decode($res->getContent()), JSON_PRETTY_PRINT);

