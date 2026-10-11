<?php

/*
|--------------------------------------------------------------------------
| Lista Maestra Única de Clases de Fundición
|--------------------------------------------------------------------------
|
| Este archivo contiene la ÚNICA declaración oficial de todas las clases.
| Las categorías, reglas de procesos, alias de búsqueda y prefijos de código
| se generan automáticamente a partir de la lista maestra `$clases`.
|
*/

$clases = [
    1 => 'MOLDE',
    2 => 'FONDO',
    3 => 'BOMBILLO',
    4 => 'OBTURADOR',
    5 => 'EMBUDO',
    6 => 'CORONA',
    7 => 'GUÍA VIAJERA',
    8 => 'PISTÓN SS',
    9 => 'CABEZA DE SOPLO',
    10 => 'GUÍA LIMITADORA',
    11 => 'CUERPO TEMPLADO',
    12 => 'PISTÓN PS',
    13 => 'ENFRIADOR',
    14 => 'PLACA VERTY FLOW',
    15 => 'GAUGE CENTRADOR DE FONDO',
    16 => 'PIPETAS',
    17 => 'BASE PARA OBTURADOR PS',
    18 => 'TIP PARA OBTURADOR PS',
    30 => 'MOLDE SEMIAUTOMÁTICO',
    31 => 'PLATO PARA MOLDE SEMIAUTOMÁTICO',
    32 => 'BOMBILLO SEMIAUTOMÁTICO',
    33 => 'PLATO PARA BOMBILLO SEMIAUTOMÁTICO',
    34 => 'PORTA CORONAS SEMIAUTOMÁTICOS',
    35 => 'CORONAS SEMIAUTOMÁTICOS',
    36 => 'GUÍA SEMIAUTOMÁTICA',
    37 => 'PISTÓN SEMIAUTOMÁTICO',
    38 => 'PLATO',
    40 => 'MOLDE PRENSA DIRECTA',
    41 => 'ANILLO PARA MOLDE DE PRENSA DIRECTA',
    42 => 'PISTÓN PARA MOLDE DE PRENSA DIRECTA',
    43 => 'FONDO PARA MOLDE DE PRENSA DIRECTA',
    44 => 'ANILLO CENTRADOR PARA PRENSA DIRECTA',
    50 => 'CUERPO PARA OBTURADOR CON RESORTE',
    51 => 'PLATO PARA FONDO-BIPARTIDO',
    52 => 'CANDADO PARA OBTURADOR-BIPARTIDO',
    53 => 'RONDANA DE ALUMINIO',
    54 => 'VÁLVULA HEXAGONAL',
    55 => 'CASQUILLO CANDADO SUPERIOR',
    56 => 'CASQUILLO DE ALTURA'
];

// IDs asignados a procesos de soldadura (MOLDE, FONDO, BOMBILLO, OBTURADOR, CORONA, etc.)
$weldingIds = [1, 2, 3, 4, 6, 30, 32, 35, 40, 43];

// IDs asignados a programación de casillas de procesos / máquinas
$processIds = [1, 2, 3, 4, 5, 6, 9, 38, 52, 30, 31, 32, 33, 34, 35, 36, 37, 40, 41, 42, 43, 44, 50, 51];

// Mapeo completo de prefijos por ID de clase de la lista maestra
$prefixesById = [
    1  => 'M',     // MOLDE
    2  => 'F',     // FONDO
    3  => 'B',     // BOMBILLO
    4  => 'O',     // OBTURADOR
    5  => 'E',     // EMBUDO
    6  => 'C',     // CORONA
    7  => 'G',     // GUÍA VIAJERA
    8  => 'P',     // PISTÓN SS
    9  => 'CS',    // CABEZA DE SOPLO
    10 => 'G',     // GUÍA LIMITADORA
    11 => 'CT',    // CUERPO TEMPLADO
    12 => 'P',     // PISTÓN PS
    13 => 'EN',    // ENFRIADOR
    14 => 'PVF',   // PLACA VERTY FLOW
    15 => 'GCF',   // GAUGE CENTRADOR DE FONDO
    16 => 'PI',    // PIPETAS
    17 => 'BOPS',  // BASE PARA OBTURADOR PS
    18 => 'TOPS',  // TIP PARA OBTURADOR PS
    30 => 'MSA',   // MOLDE SEMIAUTOMÁTICO
    31 => 'PMSA',  // PLATO PARA MOLDE SEMIAUTOMÁTICO
    32 => 'BSA',   // BOMBILLO SEMIAUTOMÁTICO
    33 => 'PBSA',  // PLATO PARA BOMBILLO SEMIAUTOMÁTICO
    34 => 'PCSA',  // PORTA CORONAS SEMIAUTOMÁTICOS
    35 => 'CSA',   // CORONAS SEMIAUTOMÁTICOS
    36 => 'GSA',   // GUÍA SEMIAUTOMÁTICA
    37 => 'PSA',   // PISTÓN SEMIAUTOMÁTICO
    38 => 'PL',    // PLATO
    40 => 'MPD',   // MOLDE PRENSA DIRECTA
    41 => 'AMPD',  // ANILLO PARA MOLDE DE PRENSA DIRECTA
    42 => 'PMPD',  // PISTÓN PARA MOLDE DE PRENSA DIRECTA
    43 => 'FMPD',  // FONDO PARA MOLDE DE PRENSA DIRECTA
    44 => 'ACPD',  // ANILLO CENTRADOR PARA PRENSA DIRECTA
    50 => 'COR',   // CUERPO PARA OBTURADOR CON RESORTE
    51 => 'PFB',   // PLATO PARA FONDO-BIPARTIDO
    52 => 'CO',    // CANDADO PARA OBTURADOR-BIPARTIDO
    53 => 'RA',    // RONDANA DE ALUMINIO
    54 => 'VH',    // VÁLVULA HEXAGONAL
    55 => 'CCS',   // CASQUILLO CANDADO SUPERIOR
    56 => 'CA',    // CASQUILLO DE ALTURA
];

// Construir categorías dinámicamente desde la lista maestra $clases
$weldingClasses = [];
foreach ($weldingIds as $id) {
    if (isset($clases[$id])) {
        $weldingClasses[] = "$id - " . $clases[$id];
        $weldingClasses[] = $clases[$id];
        $weldingClasses[] = strtolower($clases[$id]);
    }
}

$processClasses = [];
foreach ($processIds as $id) {
    if (isset($clases[$id])) {
        $processClasses[] = "$id - " . $clases[$id];
        $processClasses[] = $clases[$id];
        $processClasses[] = strtolower($clases[$id]);
    }
}

$classPrefixes = [
    'templadera obturador' => 'TO',
    'templadera molde'     => 'TM',
    'templadera fondo'     => 'TF',
    'templadera bombillo'  => 'TB',
    'templadera'           => 'T',
    'candado obturador'    => 'CO',
    'cabeza de soplo'      => 'CS',
    'pistones'             => 'P',
    'guías'                => 'G',
    'guias'                => 'G',
];

$searchableAliases = [
    'candado obturador',
    'cabeza de soplo',
    'pistones',
    'guías',
    'guias'
];

foreach ($clases as $id => $nombre) {
    $prefix = $prefixesById[$id] ?? strtoupper(substr($nombre, 0, 1));
    $nameLower = strtolower($nombre);
    $nameUpper = strtoupper($nombre);
    $oficial = "$id - $nameUpper";

    $classPrefixes[$nameLower] = $prefix;
    $classPrefixes[$oficial] = $prefix;
    $classPrefixes[$nameUpper] = $prefix;

    $searchableAliases[] = $nameLower;
    $searchableAliases[] = $oficial;

    // Versión sin tildes para búsqueda flexible
    $sinTilde = str_replace(
        ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'á', 'é', 'í', 'ó', 'ú', 'ü'],
        ['A', 'E', 'I', 'O', 'U', 'U', 'a', 'e', 'i', 'o', 'u', 'u'],
        $nombre
    );
    if (strtolower($sinTilde) !== $nameLower) {
        $classPrefixes[strtolower($sinTilde)] = $prefix;
        $searchableAliases[] = strtolower($sinTilde);
    }
}

return [
    'clases' => $clases,
    'welding_classes' => array_values(array_unique($weldingClasses)),
    'process_classes' => array_values(array_unique($processClasses)),
    'process_excluded_classes' => [
        'base', 'tip', 'roll pin', 'porta', 'pastilla', 'canastilla'
    ],
    'searchable_aliases' => array_values(array_unique($searchableAliases)),
    'class_prefixes' => $classPrefixes
];
