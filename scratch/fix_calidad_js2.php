<?php
$file = 'resources/js/almacen_views/calidad_fundicion.js';
$content = file_get_contents($file);

// 3. n.includes filter logic (occurs twice)
$oldFilterLine = 'return clasesFaltantes.some((clase) => n.includes(clase.toLowerCase()));';

$newFilterBlock = '
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
                            return false;';

$content = str_replace($oldFilterLine, ltrim($newFilterBlock), $content);

file_put_contents($file, $content);
echo "Done replacing JS includes logic.\n";
