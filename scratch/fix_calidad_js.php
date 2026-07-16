<?php
$file = 'resources/js/almacen_views/calidad_fundicion.js';
$content = file_get_contents($file);

// 1. Hardcoded Arrays
$content = str_replace(
    '["fondo", "bombillo", "molde", "obturador"]',
    '["candado obturador", "cabeza de soplo", "obturador", "bombillo", "embudo", "corona", "plato", "molde", "fondo"]',
    $content
);
$content = str_replace(
    '["bombillo", "fondo", "obturador", "molde"]',
    '["candado obturador", "cabeza de soplo", "obturador", "bombillo", "embudo", "corona", "plato", "molde", "fondo"]',
    $content
);

// 2. esFaltante check (around line 3082 or 6163)
$oldEsFaltante = 'const esFaltante = clasesFaltantes.some((f) => f.toLowerCase().includes(nombreNorm) || nombreNorm.includes(f.toLowerCase()));';
$newEsFaltante = 'const esFaltante = clasesFaltantes.some((f) => f.toLowerCase() === nombreNorm);';
$content = str_replace($oldEsFaltante, $newEsFaltante, $content);

// 3. n.includes filter logic (occurs twice)
$oldFilter = 'archivosAMostrar = archivosAMostrar.filter((f) => {
                            const n = (f.nombre || "").toLowerCase();
                            if (n.includes("documentos_aprobados") || n.includes("documentos_rechazados") || n.includes("pre-orden")) return true;
                            return clasesFaltantes.some((clase) => n.includes(clase.toLowerCase()));
                        });';

$newFilter = 'archivosAMostrar = archivosAMostrar.filter((f) => {
                            const n = (f.nombre || "").toLowerCase();
                            if (n.includes("documentos_aprobados") || n.includes("documentos_rechazados") || n.includes("pre-orden")) return true;
                            
                            const knownClasses = ["candado obturador", "cabeza de soplo", "obturador", "bombillo", "embudo", "corona", "plato", "molde", "fondo"];
                            let foundClass = null;
                            for (let kc of knownClasses) {
                                if (n.includes(kc)) {
                                    foundClass = kc;
                                    break;
                                }
                            }
                            
                            if (foundClass) {
                                return clasesFaltantes.some((clase) => {
                                    let c = clase.toLowerCase().trim().replace(/^modelo\s+/i, "").replace(/^casting\s+/i, "").trim();
                                    return foundClass === c;
                                });
                            }
                            return false;
                        });';
$content = str_replace($oldFilter, $newFilter, $content);


// 4. clLow.includes fallback (around line 9157-9163)
//     if (clLow.includes("fondo")) return "Fondo";
//     if (clLow.includes("obturador")) return "Obturador";
//     if (clLow.includes("molde")) return "Molde";
//     if (clLow.includes("bombillo")) return "Bombillo";
$oldFallback = 'if (clLow.includes("fondo")) return "Fondo";
    if (clLow.includes("obturador")) return "Obturador";
    if (clLow.includes("molde")) return "Molde";
    if (clLow.includes("bombillo")) return "Bombillo";';
    
$newFallback = 'if (clLow.includes("candado obturador")) return "Candado obturador";
    if (clLow.includes("cabeza de soplo")) return "Cabeza de soplo";
    if (clLow.includes("embudo")) return "Embudo";
    if (clLow.includes("corona")) return "Corona";
    if (clLow.includes("plato")) return "Plato";
    if (clLow.includes("fondo")) return "Fondo";
    if (clLow.includes("obturador")) return "Obturador";
    if (clLow.includes("molde")) return "Molde";
    if (clLow.includes("bombillo")) return "Bombillo";';
$content = str_replace($oldFallback, $newFallback, $content);

file_put_contents($file, $content);
echo "Done replacing JS.\n";
