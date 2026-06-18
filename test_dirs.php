<?php
use Illuminate\Support\Facades\Storage;

echo "\n--- Test Calidad Directories ---\n";
print_r(Storage::disk('local')->directories('DOCUMENTACION_GIS/CALIDAD_FUNDICION'));
