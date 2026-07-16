<?php
$file = 'resources/views/calidad/calidad_fundicion.blade.php';
$content = file_get_contents($file);

// 1. Replace hardcoded arrays
$content = str_replace(
    '["fondo", "obturador", "bombillo", "molde"]',
    '["candado obturador", "cabeza de soplo", "obturador", "bombillo", "embudo", "corona", "plato", "molde", "fondo"]',
    $content
);
$content = str_replace(
    '["fondo", "bombillo", "molde", "obturador"]',
    '["candado obturador", "cabeza de soplo", "obturador", "bombillo", "embudo", "corona", "plato", "molde", "fondo"]',
    $content
);
$content = str_replace(
    '["Bombillo", "Fondo", "Obturador", "Molde"]',
    '["Candado obturador", "Cabeza de soplo", "Obturador", "Bombillo", "Embudo", "Corona", "Plato", "Molde", "Fondo"]',
    $content
);

// 2. Fix strpos bugs: The issue is that strpos($clLow, "obturador") is checked before "candado obturador".
// I will replace the entire strpos chain with a safe check.
// Since the exact whitespace is unknown, I will use regex.

$pattern = '/if\s*\(\s*strpos\(\$clLow,\s*"fondo"\)\s*!==\s*false\s*\)\s*\{\s*\$tipo\s*=\s*"Fondo";\s*\}\s*elseif\s*\(\s*strpos\(\$clLow,\s*"obturador"\)\s*!==\s*false\s*\)\s*\{\s*\$tipo\s*=\s*"Obturador";\s*\}\s*elseif\s*\(\s*strpos\(\$clLow,\s*"molde"\)\s*!==\s*false\s*\)\s*\{\s*\$tipo\s*=\s*"Molde";\s*\}\s*elseif\s*\(\s*strpos\(\$clLow,\s*"corona"\)\s*!==\s*false\s*\)\s*\{\s*\$tipo\s*=\s*"Corona";\s*\}\s*elseif\s*\(\s*strpos\(\$clLow,\s*"plato"\)\s*!==\s*false\s*\)\s*\{\s*\$tipo\s*=\s*"Plato";\s*\}\s*elseif\s*\(\s*strpos\(\$clLow,\s*"embudo"\)\s*!==\s*false\s*\)\s*\{\s*\$tipo\s*=\s*"Embudo";\s*\}\s*elseif\s*\(\s*strpos\(\$clLow,\s*"cabeza\s+de\s+soplo"\)\s*!==\s*false\s*\)\s*\{\s*\$tipo\s*=\s*"Cabeza\s+de\s+Soplo";\s*\}\s*elseif\s*\(\s*strpos\(\$clLow,\s*"candado\s+obturador"\)\s*!==\s*false\s*\)\s*\{\s*\$tipo\s*=\s*"Candado\s+Obturador";\s*\}\s*elseif\s*\(\s*strpos\(\$clLow,\s*"bombillo"\)\s*!==\s*false\s*\)\s*\{\s*\$tipo\s*=\s*"Bombillo";\s*\}/i';

$replacement = 'if (strpos($clLow, "candado obturador") !== false) { $tipo = "Candado obturador"; }
elseif (strpos($clLow, "cabeza de soplo") !== false) { $tipo = "Cabeza de soplo"; }
elseif (strpos($clLow, "embudo") !== false) { $tipo = "Embudo"; }
elseif (strpos($clLow, "corona") !== false) { $tipo = "Corona"; }
elseif (strpos($clLow, "plato") !== false) { $tipo = "Plato"; }
elseif (strpos($clLow, "fondo") !== false) { $tipo = "Fondo"; }
elseif (strpos($clLow, "obturador") !== false) { $tipo = "Obturador"; }
elseif (strpos($clLow, "molde") !== false) { $tipo = "Molde"; }
elseif (strpos($clLow, "bombillo") !== false) { $tipo = "Bombillo"; }';

$content = preg_replace($pattern, $replacement, $content);

file_put_contents($file, $content);
echo "Done replacing blade.\n";
