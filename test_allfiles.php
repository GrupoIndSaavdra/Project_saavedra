<?php
use Illuminate\Support\Facades\Storage;

echo "\n--- Test allFiles ---\n";
$files = Storage::disk('local')->allFiles('Almacen_Fundicion/OT_1098_-_PRUEBADDF/ayudas_visuales');
print_r($files);

$calidadAttachments = [];
foreach ($files as $filePath) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($ext === 'pdf') {
        if (str_contains(strtolower($filePath), '/dibujos/') || str_contains(strtolower($filePath), '/ayudas_visuales/')) {
            $calidadAttachments[] = [
                'path' => storage_path('app/' . $filePath),
                'name' => basename($filePath),
                'mime' => 'application/pdf'
            ];
        }
    }
}
echo "\n--- Matched Attachments ---\n";
print_r($calidadAttachments);
