<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'almacen' => [
        'email' => env('ALMACEN_EMAIL', env('EMAIL_ALMACEN_BASE', 'almacentec@grupoindsaavedra.com')),
    ],

    'pta' => [
        'email' => env('EMAIL_PTA', env('EMAIL_ACABADOS_MEX', 'acabadosmex@grupoindsaavedra.com') . ',' . env('EMAIL_ALEJANDRO', 'alejandross@grupoindsaavedra.com')),
    ],

    'fundicion' => [
        'almacen' => env('EMAIL_ALMACEN', env('EMAIL_ALMACEN_BASE', 'almacentec@grupoindsaavedra.com') . ',' . env('EMAIL_REQUISICIONES', 'requisicionestec@grupoindsaavedra.com')),
        'almacen_completo' => env('ALMACEN_EMAIL', env('EMAIL_ALMACEN_BASE', 'almacentec@grupoindsaavedra.com') . ',' . env('EMAIL_INSPECCION', 'inspecciontec@grupoindsaavedra.com') . ',' . env('EMAIL_REQUISICIONES', 'requisicionestec@grupoindsaavedra.com')),
        'calidad' => env('EMAIL_CALIDAD', env('EMAIL_INSPECCION', 'inspecciontec@grupoindsaavedra.com') . ',' . env('EMAIL_REQUISICIONES', 'requisicionestec@grupoindsaavedra.com')),
        'proveedor_modelos' => env('EMAIL_PROVEEDOR_MODELOS', env('EMAIL_INGENIERIA_STEELFS', 'ingenieria3@steelfs.com.mx') . ',' . env('EMAIL_AUX_INGENIERIA_SS', 'auxingenieria@ssmetalf.mx') . ',' . env('EMAIL_REQUISICIONES', 'requisicionestec@grupoindsaavedra.com')),
        'produccion_ss' => env('EMAIL_PRODUCCION_SS', env('EMAIL_PRODUCCION_SS_BASE', 'produccion@ssmetalf.mx') . ',' . env('EMAIL_ASISTENTE_PROD_SS', 'asistenteprod@ssmetalf.mx') . ',' . env('EMAIL_LABORATORIO_SS', 'laboratorio@ssmetalf.mx') . ',' . env('EMAIL_REQUISICIONES', 'requisicionestec@grupoindsaavedra.com')),
        'produccion_jacarandas' => env('EMAIL_PRODUCCION_JACARANDAS', env('EMAIL_VENTAS_JACARANDAS_BASE', 'ventas_jacarandas@prodigy.net.mx') . ',' . env('EMAIL_REQUISICIONES', 'requisicionestec@grupoindsaavedra.com')),
        'cc_general' => env('EMAIL_CC_GENERAL', implode(',', [
            env('EMAIL_ALEJANDRO', 'alejandross@grupoindsaavedra.com'),
            env('EMAIL_JUAN', 'juanss@grupoindsaavedra.com'),
            env('EMAIL_ABRAHAM', 'abraham@grupoindsaavedra.com'),
            env('EMAIL_INSPECCION', 'inspecciontec@grupoindsaavedra.com'),
            env('EMAIL_REQUISICIONES', 'requisicionestec@grupoindsaavedra.com'),
            env('EMAIL_AUX_ADM', 'auxadmtec@grupoindsaavedra.com'),
            env('EMAIL_PRODUCCION', 'producciontec@grupoindsaavedra.com')
        ])),
        'compras' => env('EMAIL_COMPRAS', env('EMAIL_ANALILIA', 'analilia@grupoindsaavedra.com')),
    ],

];
