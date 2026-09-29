<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Clases de Fundición
    |--------------------------------------------------------------------------
    |
    | Aquí se define el listado oficial y global de todas las clases
    | manejadas en los procesos de Fundición, Ingeniería, Calidad y Almacén.
    |
    */

    'clases' => [
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
    ],

    /*
    |--------------------------------------------------------------------------
    | Categorías y Reglas Dinámicas de Clases
    |--------------------------------------------------------------------------
    |
    | Define qué clases son aptas para procesos específicos.
    |
    */
    
    // Clases que llevan procesos de soldadura (PTA o manual)
    'welding_classes' => [
        'molde', 'fondo', 'bombillo', 'obturador', 'corona'
    ],

    // Clases que requieren programación de casillas de procesos (Máquinas)
    'process_classes' => [
        'bombillo', 'molde', 'fondo', 'obturador', 'corona', 'plato', 'embudo', 'cabeza de soplo', 'candado'
    ],

    // Clases excluidas explícitamente de tener procesos
    'process_excluded_classes' => [
        'base', 'tip', 'roll pin', 'porta', 'pastilla', 'canastilla'
    ]
];
