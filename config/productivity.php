<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tiempos de Monitoreo de Productividad
    |--------------------------------------------------------------------------
    |
    | Define los minutos de inactividad permitidos antes de bloquear la interfaz.
    |
    */

    'idle_mins' => env('PRODUCTIVITY_IDLE_MINS', 3),

    'machining_threshold' => env('PRODUCTIVITY_MACHINING_THRESHOLD', 1.10), // 110% por defecto
];
