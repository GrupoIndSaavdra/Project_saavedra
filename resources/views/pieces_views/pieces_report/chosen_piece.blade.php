@extends('layouts.appMenu')

@section('head')
    <title>Visualizacion de pieza</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite('resources/css/pieces_views/pieces_report/chosen_piece.css')
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')
<!--Body background Image-->
@section('content')
    <script>
        class Proceso {
            constructor(nameProceso, valoresCnomi, valoresTole, valorPieza) { // Constructor
                this.nameProceso = nameProceso; // Nombre del proceso
                this.valoresCnomi = valoresCnomi; // Valores de c.nominal
                this.valoresTole = valoresTole; // Valores de tolerancias
                this.valoresPieza = valorPieza;
            }

            crearProceso() { // Crear proceso
                let titulos = []; // Titulos de la tabla
                let cNomiPosiciones = []; // Posiciones de los inputs de c.nominal
                let tolePosiciones = []; // Posiciones de los inputs de tolerancias
                let piezaPosiciones = []; // Posiciones de los inputs de tolerancias
                let valoresCnomi = []; // Valores de c.nominal
                let valoresTole = []; // Valores de tolerancias
                let valoresPieza = []; // Valores de la pieza
                let nombres = []; // Nombres de los inputs de pieza
                let nombresCnomi = []; // Nombres de los inputs de c.nominal
                let nombresTole = []; // Nombres de los inputs de tolerancias

                switch (this.nameProceso) { // Segun el proceso
                    case "Cepillado": //Proceso de cepillado
                        titulos = ['No.Pieza', 'Radio final de mordaza', 'Radio final mayor',
                            'Radio final de sufridera', 'Profundidad final conexión Fondo/Corona',
                            'Profundidad final mitad de Molde/Bombillo',
                            'Profundidad final Pico/Conexión de obturador', 'Ensamble',
                            'Distancia de barreno de alineación', 'Profundidad de barreno de alineación Hembra',
                            'Profundidad de barreno de alineación Macho', 'Altura de vena Hembra',
                            'Altura de vena Macho', 'Ancho de vena', 'Laterales', 'PIN', 'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [15]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14,
                            15
                        ]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [15];

                        nombresCnomi = ['id', 'radiof_mordaza', 'radiof_mayor', 'radiof_sufridera', 'profuFinal_CFC',
                            'profuFinal_mitadMB', 'profuFinal_PCO', 'ensamble', 'distancia_barrenoAli',
                            'profu_barrenoAliHembra', 'profu_barrenoAliMacho', 'altura_venaHembra',
                            'altura_venaMacho', 'ancho_vena', 'laterales', 'pin1', 'pin2'
                        ];

                        nombresTole = ['id', 'radiof_mordaza1', 'radiof_mordaza2', 'radiof_mayor1', 'radiof_mayor2',
                            'radiof_sufridera1', 'radiof_sufridera2', 'profuFinal_CFC1', 'profuFinal_CFC2',
                            'profuFinal_mitadMB1', 'profuFinal_mitadMB2', 'profuFinal_PCO1', 'profuFinal_PCO2',
                            'ensamble1', 'ensamble2', 'distancia_barrenoAli1', 'distancia_barrenoAli2',
                            'profu_barrenoAliHembra1', 'profu_barrenoAliHembra2', 'profu_barrenoAliMacho1',
                            'profu_barrenoAliMacho2', 'altura_venaHembra1', 'altura_venaHembra2',
                            'altura_venaMacho1', 'altura_venaMacho2', 'ancho_vena1', 'ancho_vena2', 'laterales1',
                            'laterales2', 'pin1', 'pin2'
                        ];

                        nombres = ['n_pieza', 'radiof_mordaza', 'radiof_mayor', 'radiof_sufridera', 'profuFinal_CFC',
                            'profuFinal_mitadMB', 'profuFinal_PCO', 'ensamble', 'distancia_barrenoAli',
                            'profu_barrenoAliHembra', 'profu_barrenoAliMacho', 'altura_venaHembra',
                            'altura_venaMacho', 'ancho_vena', 'laterales', 'pin1', 'pin2', 'error', 'observaciones'
                        ];
                        break;

                    case "Desbaste Exterior": //Proceso de desbaste Exterior
                        titulos = ['No.Pieza', 'Diametro de mordaza', 'Diametro de ceja', 'Diametro de sufridera/Extra',
                            'Simetría ceja', 'Simetría Mordaza', 'Altura de ceja', 'Altura sufridera', 'Error',
                            'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [1, 2, 3, 4, 5, 6, 7]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufrideraExtra',
                            'simetria_ceja', 'simetria_mordaza', 'altura_ceja', 'altura_sufridera'
                        ];
                        nombresTole = ['id', 'diametro_mordaza1', 'diametro_mordaza2', 'diametro_ceja1',
                            'diametro_ceja2', 'diametro_sufrideraExtra1', 'diametro_sufrideraExtra2',
                            'simetria_ceja1', 'simetria_ceja2', 'simetria_mordaza1', 'simetria_mordaza2',
                            'altura_ceja1', 'altura_ceja2', 'altura_sufridera1', 'altura_sufridera2'
                        ];
                        nombres = ['n_pieza', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufrideraExtra',
                            'simetria_ceja', 'simetria_mordaza', 'altura_ceja', 'altura_sufridera', 'error',
                            'observaciones'
                        ];
                        break;
                    case "Revision Laterales": //Proceso de revision laterales
                        titulos = ['No.Pieza', 'Desfasamiento Entrada', 'Desfasamiento Salida',
                            'Ancho de simetria Entrada', 'Ancho de simetria Salida', 'Angulo de corte', 'Error',
                            'Observaciones'
                        ];

                        cNomiPosiciones = [null];
                        tolePosiciones = [1, 2, 3, 4, 5]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'desfasamiento_entrada', 'desfasamiento_salida', 'ancho_simetriaEntrada',
                            'ancho_simetriaSalida', 'angulo_corte'
                        ];

                        nombresTole = ['id', 'desfasamiento_entrada1', 'desfasamiento_entrada2',
                            'desfasamiento_salida1', 'desfasamiento_salida2', 'ancho_simetriaEntrada1',
                            'ancho_simetriaEntrada2', 'ancho_simetriaSalida1', 'ancho_simetriaSalida2',
                            'angulo_corte1', 'angulo_corte2'
                        ];

                        nombres = ['n_pieza', 'desfasamiento_entrada', 'desfasamiento_salida', 'ancho_simetriaEntrada',
                            'ancho_simetriaSalida', 'angulo_corte', 'error', 'observaciones'
                        ];
                        break;

                    case "Primera Operacion": //Proceso de primera operacion
                        titulos = ['No.Pieza', 'Diametro 1', 'Profundidad 1 ', 'Diametro 2', 'Profundidad 2',
                            'Diametro 3', 'Profunfidad 3', 'Diametro de soldadura', 'Profundidad de soldadura',
                            'Diametro de barreno', 'Simetria línea de partida', 'Perno de alineación',
                            'Simetría a 90°', 'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [9, 10]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3',
                            'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'diametroBarreno',
                            'simetriaLinea_partida', 'pernoAlineacion', 'Simetria90G'
                        ];

                        nombresTole = ['id', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3',
                            'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'diametroBarreno1',
                            'diametroBarreno2', 'simetriaLinea_partida1', 'simetriaLinea_partida2',
                            'pernoAlineacion', 'Simetria90G'
                        ];

                        nombres = ['n_pieza', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3',
                            'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'diametroBarreno',
                            'simetriaLinea_partida', 'pernoAlineacion', 'Simetria90G', 'error', 'observaciones'
                        ];
                        break;

                    case "Barreno Maniobra": //Proceso de barreno maniobra
                        titulos = ['No. Pieza', 'Profundidad de Barreno', 'Diametro de machuelo', 'Acetato B/M',
                            'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [1, 2]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'profundidad_barreno', 'diametro_machuelo', ''];
                        nombresTole = ['id', 'profundidad_barreno1', 'profundidad_barreno2', 'diametro_machuelo1',
                            'diametro_machuelo2', ''
                        ];
                        nombres = ['n_juego', 'profundidad_barreno', 'diametro_machuelo', 'acetatoBM', 'error',
                            'observaciones'
                        ];
                        break;

                    case "Segunda Operacion": //Proceso de segunda operacion
                        titulos = ['No. Pieza', 'Diametro 1', 'Profundidad 1 ', 'Diametro 2', 'Profundidad 2',
                            'Diametro 3', 'Profunfidad 3', 'Diametro de soldadura', 'Profundidad de soldadura',
                            'Altura total', 'Simetría a 90°', 'Simetria línea de partida', 'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [9, 10]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3',
                            'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'alturaTotal',
                            'simetria90G', 'simetriaLinea_Partida'
                        ];

                        nombresTole = ['id', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3',
                            'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'alturaTotal1',
                            'alturaTotal2', 'simetria90G1', 'simetria90G2', 'simetriaLinea_Partida'
                        ];

                        nombres = ['n_pieza', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3',
                            'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'alturaTotal',
                            'simetria90G', 'simetriaLinea_Partida', 'error', 'observaciones'
                        ];
                        break;

                    case 'Soldadura':
                        titulos = ['No. Pieza', 'Peso x Pieza', 'Temperatura precalentado ', 'Tiempo de aplicación',
                            'Tipo de Preparación (1 Y 2)', 'Soldadura', 'Lote', 'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [null]; // Posiciones de los inputs de tolerancias (todas simétricas)
                        piezaPosiciones = [null];

                        valoresCnomi = null;

                        valoresTole = null;

                        nombres = ['n_juego', 'pesoxpieza', 'temperatura_precalentado', 'tiempo_aplicacion',
                            'tipo_soldadura', 'material_soldadura', 'lote', 'error', 'observaciones'
                        ];
                        break;

                    case 'Soldadura PTA':
                        titulos = ['No. Pieza', 'Temperatura de calentado', 'Temperatura en dispositivo ', 'Limpieza',
                            'Soldadura', 'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [null]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        valoresCnomi = null;

                        valoresTole = null;

                        nombres = ['n_juego', 'temp_calentado', 'temp_dispositivo', 'limpieza',
                            'material_soldadura', 'error', 'observaciones'
                        ];
                        break;

                    case 'Rectificado':
                        titulos = ['No. Pieza', 'cumple', 'Error', 'Observaciones'];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [null]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        valoresCnomi = null;

                        valoresTole = null;

                        nombres = ['n_juego', 'cumple', 'error', 'observaciones'];
                        break;

                    case "Calificado": //Proceso de calificado
                        titulos = ['No. Pieza', 'Diametro de ceja', 'Diametro de sufridera', 'Altura de sufridera',
                            'Diametro de conexion', 'Altura de conexion', 'Diametro de caja', 'Altura de caja',
                            'Altura total', 'Simetria', 'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [1, 2, 3, 4, 5, 6, 7, 8, 9]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'diametro_ceja', 'diametro_sufridera', 'altura_sufridera',
                            'diametro_conexion', 'altura_conexion', 'diametro_caja', 'altura_caja', 'altura_total',
                            'simetria'
                        ];

                        nombresTole = ['id', 'diametro_ceja1', 'diametro_ceja2', 'diametro_sufridera1',
                            'diametro_sufridera2', 'altura_sufridera1', 'altura_sufridera2', 'diametro_conexion1',
                            'diametro_conexion2', 'altura_conexion1', 'altura_conexion2', 'diametro_caja1',
                            'diametro_caja2', 'altura_caja1', 'altura_caja2', 'altura_total1', 'altura_total2',
                            'simetria1', 'simetria2'
                        ];

                        nombres = ['n_pieza', 'diametro_ceja', 'diametro_sufridera', 'altura_sufridera',
                            'diametro_conexion', 'altura_conexion', 'diametro_caja', 'altura_caja', 'altura_total',
                            'simetria', 'error', 'observaciones'
                        ];
                        break;

                    case "Acabado Bombillo": //Proceso de acabado Bombillo
                        titulos = ['No. Pieza', 'Diametro de mordaza', 'Diametro de ceja', 'Diametro de sufridera',
                            'Altura de mordaza', 'Altura de ceja', 'Altura de sufridera', 'Guage Ceja',
                            'Guage Corona', 'Guage Llanta', 'Altura total', 'Diametro Boca',
                            'Diametro Asiento Corona', 'Diametro llanta', 'Diametro caja corona',
                            'Profundidad corona', 'Angulo de 30', 'Profundidad caja corona', 'Simetria', 'Error',
                            'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [1, 2, 3, 4, 5, 6, 11, 12, 13, 14, 15, 16, 17,
                            18
                        ]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufridera',
                            'altura_mordaza', 'altura_ceja', 'altura_sufridera', '', '', '', 'altura_total',
                            'diametro_boca', 'diametro_asiento_corona', 'diametro_llanta', 'diametro_caja_corona',
                            'profundidad_corona', 'angulo_30', 'profundidad_caja_corona', 'simetria'
                        ];

                        nombresTole = ['id', 'diametro_mordaza1', 'diametro_mordaza2', 'diametro_ceja1',
                            'diametro_ceja2', 'diametro_sufridera1', 'diametro_sufridera2', 'altura_mordaza1',
                            'altura_mordaza2', 'altura_ceja1', 'altura_ceja2', 'altura_sufridera1',
                            'altura_sufridera2', '', '', '', '', 'diametro_boca1', 'diametro_boca2',
                            'diametro_asiento_corona1', 'diametro_asiento_corona2', 'diametro_llanta1',
                            'diametro_llanta2', 'diametro_caja_corona1', 'diametro_caja_corona2',
                            'profundidad_corona1', 'profundidad_corona2', 'angulo_301', 'angulo_302',
                            'profundidad_caja_corona1', 'profundidad_caja_corona2', 'simetria1', 'simetria2'
                        ];

                        nombres = ['n_pieza', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufridera',
                            'altura_mordaza', 'altura_ceja', 'altura_sufridera', 'gauge_ceja', 'gauge_corona',
                            'gauge_llanta', 'altura_total', 'diametro_boca', 'diametro_asiento_corona',
                            'diametro_llanta', 'diametro_caja_corona', 'profundidad_corona', 'angulo_30',
                            'profundidad_caja_corona', 'simetria', 'error', 'observaciones'
                        ];
                        break;

                    case "Acabado Molde": //Proceso de acabado molde
                        titulos = ['No. Pieza', 'Diametro de mordaza', 'Diametro de ceja', 'Diametro de sufridera',
                            'Altura de mordaza', 'Altura de ceja', 'Altura de sufridera', 'gaugue_ceja',
                            'altura_total', 'Diametro Conexion Fondo', 'Diametro llanta', 'Diametro Caja Fondo',
                            'Altura Conexion Fondo', 'Profundidad Llanta', 'Profundidad Caja Fondo', 'Simetria',
                            'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [1, 2, 3, 4, 5, 6, 9, 10, 11, 12, 13, 14,
                            15
                        ]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufridera',
                            'altura_mordaza', 'altura_ceja', 'altura_sufridera', 'gauge_ceja', 'altura_total',
                            'diametro_conexion_fondo', 'diametro_llanta', 'diametro_caja_fondo',
                            'altura_conexion_fondo', 'profundidad_llanta', 'profundidad_caja_fondo', 'simetria'
                        ];

                        nombresTole = ['id', 'diametro_mordaza1', 'diametro_mordaza2', 'diametro_ceja1',
                            'diametro_ceja2', 'diametro_sufridera1', 'diametro_sufridera2', 'altura_mordaza1',
                            'altura_mordaza2', 'altura_ceja1', 'altura_ceja2', 'altura_sufridera1',
                            'altura_sufridera2', '', '', 'diametro_conexion_fondo1', 'diametro_conexion_fondo2',
                            'diametro_llanta1', 'diametro_llanta2', 'diametro_caja_fondo1', 'diametro_caja_fondo2',
                            'altura_conexion_fondo1', 'altura_conexion_fondo2', 'profundidad_llanta1',
                            'profundidad_llanta2', 'profundidad_caja_fondo1', 'profundidad_caja_fondo2',
                            'simetria1', 'simetria2'
                        ];

                        nombres = ['n_pieza', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufridera',
                            'altura_mordaza', 'altura_ceja', 'altura_sufridera', 'gauge_ceja', 'altura_total',
                            'diametro_conexion_fondo', 'diametro_llanta', 'diametro_caja_fondo',
                            'altura_conexion_fondo', 'profundidad_llanta', 'profundidad_caja_fondo', 'simetria',
                            'error', 'observaciones'
                        ];
                        break;

                    case 'Barreno Profundidad':
                        titulos = ['No. Pieza', 'Broca 1', 'Tiempo 1', 'Broca 2', 'Tiempo 2', 'Broca 3', 'Tiempo 3',
                            'Entrada / Salida', 'Diametro de arrastre 1', 'Diametro de arrastre 2',
                            'Diametro de arrastre 3', 'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [7]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [7];

                        nombresTole = ['id', 'broca1', 'tiempo1', 'broca2', 'tiempo2', 'broca3', 'tiempo3', 'entrada',
                            'salida', 'diametro_arrastre1', 'diametro_arrastre2', 'diametro_arrastre3'
                        ];
                        nombresCnomi = ['id', 'broca1', 'tiempo1', 'broca2', 'tiempo2', 'broca3', 'tiempo3',
                            'entradaSalida', 'diametro_arrastre1', 'diametro_arrastre2', 'diametro_arrastre3'
                        ];
                        nombres = ['n_juego', 'broca1', 'tiempo1', 'broca2', 'tiempo2', 'broca3', 'tiempo3', 'entrada',
                            'salida', 'diametro_arrastre1', 'diametro_arrastre2', 'diametro_arrastre3', 'error',
                            'observaciones'
                        ];
                        break;

                    case "Copiado": //Proceso de copiado
                        if (subproceso == 'Cilindrado') {
                            titulos = ['No. Pieza', 'Diametro 1', 'Profundidad 1', 'Diametro 2', 'Profundidad 2',
                                'Diametro de sufridera', 'Diametro Ranura', 'Profundidad Ranura',
                                'Profundidad de sufridera', 'ALTURA TOTAL', 'Error', 'Observaciones'
                            ];

                            cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                            tolePosiciones = []; // Posiciones de los inputs de tolerancias (todas simétricas)
                            piezaPosiciones = [null];

                            nombresCnomi = ['id', 'diametro1_cilindrado', 'profundidad1_cilindrado',
                                'diametro2_cilindrado', 'profundidad2_cilindrado', 'diametro_sufridera',
                                'diametro_ranura', 'profundidad_ranura', 'profundidad_sufridera', 'altura_total'
                            ];
                            nombresTole = ['id', 'diametro1_cilindrado', 'profundidad1_cilindrado',
                                'diametro2_cilindrado', 'profundidad2_cilindrado', 'diametro_sufridera',
                                'diametro_ranura', 'profundidad_ranura', 'profundidad_sufridera', 'altura_total'
                            ];
                            nombres = ['n_juego', 'diametro1_cilindrado', 'profundidad1_cilindrado',
                                'diametro2_cilindrado', 'profundidad2_cilindrado', 'diametro_sufridera',
                                'diametro_ranura', 'profundidad_ranura', 'profundidad_sufridera', 'altura_total',
                                'error', 'observaciones'
                            ];
                        } else {
                            titulos = ['No. Pieza', 'Diametro 1', 'Profundidad 1', 'Diametro 2', 'Profundidad 2',
                                'Diametro 3', 'Profundidad 3', 'Diametro 4', 'Profundidad 4', ' VOLUMEN ', 'Error',
                                'Observaciones'
                            ];

                            cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                            tolePosiciones = []; // Posiciones de los inputs de tolerancias (todas simétricas)
                            piezaPosiciones = [null];

                            nombresCnomi = ['id', 'diametro1_cavidades', 'profundidad1_cavidades',
                                'diametro2_cavidades', 'profundidad2_cavidades', 'diametro3', 'profundidad3',
                                'diametro4', 'profundidad4', 'volumen'
                            ];
                            nombresTole = ['id', 'diametro1_cavidades', 'profundidad1_cavidades', 'diametro2_cavidades',
                                'profundidad2_cavidades', 'diametro3', 'profundidad3', 'diametro4', 'profundidad4',
                                'volumen'
                            ];
                            nombres = ['n_juego', 'diametro1_cavidades', 'profundidad1_cavidades',
                                'diametro2_cavidades', 'profundidad2_cavidades', 'diametro3', 'profundidad3',
                                'diametro4', 'profundidad4', 'volumen', 'error', 'observaciones'
                            ];
                        }
                        break;

                    case "Palomas": //Proceso de palomas
                        titulos = ['No. Pieza', 'Ancho de Paloma', 'Grueso de Paloma', 'Profundidad de Paloma',
                            'Rebaje de llanta', 'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = []; // Posiciones de los inputs de tolerancias (todas simétricas)
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'anchoPaloma', 'gruesoPaloma', 'profundidadPaloma', 'rebajeLlanta'];
                        nombresTole = ['id', 'anchoPaloma', 'gruesoPaloma', 'profundidadPaloma', 'rebajeLlanta'];
                        nombres = ['n_juego', 'anchoPaloma', 'gruesoPaloma', 'profundidadPaloma', 'rebajeLlanta',
                            'error', 'observaciones'
                        ];
                        break;

                    case "Rebajes": //Proceso de Rebajes
                        titulos = ['No. Pieza', 'Rebaje 1', 'Rebaje 2', 'Rebaje 3', 'Profundidad de Bordonio', 'Vena 1',
                            'Vena 2', 'Simetria', 'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = []; // Posiciones de los inputs de tolerancias (todas simétricas)
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'rebaje1', 'rebaje2', 'rebaje3', 'profundidad_bordonio', 'vena1', 'vena2',
                            'simetria'
                        ];
                        nombresTole = ['id', 'rebaje1', 'rebaje2', 'rebaje3', 'profundidad_bordonio', 'vena1', 'vena2',
                            'simetria'
                        ];
                        nombres = ['n_juego', 'rebaje1', 'rebaje2', 'rebaje3', 'profundidad_bordonio', 'vena1', 'vena2',
                            'simetria', 'error', 'observaciones'
                        ];
                        break;

                    case "Acabado Bombillo": // Proceso de Acabado Bombillo
                        titulos = ['No.Pieza', 'Diametro de mordaza', 'Diametro de ceja', 'Diametro de sufridera',
                            'Altura de mordaza', 'Altura de ceja',
                            'Altura de sufridera', 'Diametro de boca', 'Diametro de asiento de corona',
                            'Diametro de llanta', 'Diametro de caja corona',
                            'Profundidad de corona', 'Angulo a 30°', 'Profundidad de caja corona', 'Simetria',
                            'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null];
                        tolePosiciones = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13,
                            14
                        ]; // Ajustar segun corresponda, asumo similar a otros procesos
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufridera',
                            'altura_mordaza', 'altura_ceja', 'altura_sufridera',
                            'diametro_boca', 'diametro_asiento_corona', 'diametro_llanta', 'diametro_caja_corona',
                            'profundidad_corona', 'angulo_30',
                            'profundidad_caja_corona', 'simetria'
                        ];

                        nombresTole = ['id', 'diametro_mordaza1', 'diametro_mordaza2', 'diametro_ceja1',
                            'diametro_ceja2', 'diametro_sufridera1', 'diametro_sufridera2',
                            'altura_mordaza1', 'altura_mordaza2', 'altura_ceja1', 'altura_ceja2',
                            'altura_sufridera1', 'altura_sufridera2',
                            'diametro_boca1', 'diametro_boca2', 'diametro_asiento_corona1',
                            'diametro_asiento_corona2', 'diametro_llanta1', 'diametro_llanta2',
                            'diametro_caja_corona1', 'diametro_caja_corona2', 'profundidad_corona1',
                            'profundidad_corona2', 'angulo_30_1', 'angulo_30_2',
                            'profundidad_caja_corona1', 'profundidad_caja_corona2', 'simetria1', 'simetria2'
                        ];

                        nombres = ['n_pieza', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufridera',
                            'altura_mordaza', 'altura_ceja', 'altura_sufridera',
                            'diametro_boca', 'diametro_asiento_corona', 'diametro_llanta', 'diametro_caja_corona',
                            'profundidad_corona', 'angulo_30',
                            'profundidad_caja_corona', 'simetria', 'error', 'observaciones'
                        ];
                        break;
                    case "Operacion Equipo_1 operacion": //Proceso de operacion equipo 1 operacion
                    case "Operacion Equipo_2 operacion": //Proceso de operacion equipo 2 operacion
                        titulos = ['No. Pieza', 'Altura', 'ø  Altura de candado', 'Altura asiento obturador',
                            'ø Profundidad Soldadura', 'ø de PushUp', 'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [2, 3, 4]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [2, 3, 4]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [2, 3, 4];

                        nombresCnomi = ['id', 'altura', 'alturaCandado1', 'alturaCandado2', 'alturaAsientoObturador1',
                            'alturaAsientoObturador2', 'profundidadSoldadura1', 'profundidadSoldadura2', 'pushUp'
                        ];
                        nombresTole = ['id', 'altura', 'alturaCandado1', 'alturaCandado2', 'alturaAsientoObturador1',
                            'alturaAsientoObturador2', 'profundidadSoldadura1', 'profundidadSoldadura2', 'pushUp'
                        ];
                        nombres = ['n_juego', 'altura', 'alturaCandado1', 'alturaCandado2', 'alturaAsientoObturador1',
                            'alturaAsientoObturador2', 'profundidadSoldadura1', 'profundidadSoldadura2', 'pushUp',
                            'error', 'observaciones'
                        ];
                        break;
                    case "Embudo CM": //Proceso de Rebajes
                        titulos = ['No. Pieza', 'Conexión línea de partida', 'Conexión a 90°', 'Altura de conexión',
                            'Diametro embudo', 'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = []; // Posiciones de los inputs de tolerancias (todas simétricas)
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'conexion_lineaPartida', 'conexion_90G', 'altura_conexion',
                            'diametro_embudo'
                        ];
                        nombresTole = ['id', 'conexion_lineaPartida', 'conexion_90G', 'altura_conexion',
                            'diametro_embudo'
                        ];
                        nombres = ['n_juego', 'conexion_lineaPartida', 'conexion_90G', 'altura_conexion',
                            'diametro_embudo', 'error', 'observaciones'
                        ];
                        break;
                    case "Primera Operacion Cabeza Soplo":
                    case "Segunda Operacion Cabeza Soplo":
                        titulos = ['No. Pieza', 'Diámetro Exterior', 'Longitud', 'Diámetro Candado', 'Longitud Candado',
                            'Error', 'Observaciones'
                        ];

                        cNomiPosiciones = [null];
                        tolePosiciones = [1, 2, 3, 4];
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'diametro_exterior', 'longitud', 'diametro_candado', 'longitud_candado',
                            null, null
                        ];
                        nombresTole = ['id', 'diametro_exterior1', 'diametro_exterior2', 'longitud1', 'longitud2',
                            'diametro_candado1', 'diametro_candado2', 'longitud_candado1', 'longitud_candado2',
                            null, null
                        ];
                        nombres = ['n_pieza', 'diametro_exterior', 'longitud', 'diametro_candado', 'longitud_candado',
                            'error', 'observaciones'
                        ];
                        break;
                    default:
                        return 'No se encontro el proceso'; //Retorna el mensaje de que el proceso no existe
                }
                //Almacenar valores
                valoresCnomi = this.almacenarCNomiAndTole(nombresCnomi, this.valoresCnomi);
                valoresTole = this.almacenarCNomiAndTole(nombresTole, this.valoresTole);
                valoresPieza = this.almacenarPieza(nombres);

                return this.crearTabla(titulos, cNomiPosiciones, tolePosiciones, piezaPosiciones, valoresCnomi,
                    valoresTole, valoresPieza); // Crear tabla
            }

            almacenarCNomiAndTole(nombres, valoresReales) {
                let valores = [];
                //Insertar valores
                if (valoresReales != null && nombres != null && nombres.length > 0) {
                    for (let i = 0; i < nombres.length; i++) {
                        if (valoresReales[nombres[i]] == undefined) {
                            valores.push('');
                        } else {
                            valores.push(valoresReales[nombres[i]]);
                        }
                    }
                    //Insertar espacios vacios
                    for (let i = 0; i < 2; i++) {
                        valores.push('');
                    }
                } else {
                    valores = null;
                }
                return valores;
            }

            almacenarPieza(nombres) {
                let valores = [];
                for (let i = 0; i < this.valoresPieza.length; i++) {
                    valores.push([]);
                    for (let j = 0; j < nombres.length; j++) {
                        let valueTest = this.valoresPieza[i]['correcto'] != null ? this.valoresPieza[i]['correcto'] :
                            this.valoresPieza[i]['error'];
                        if (valueTest == null && j != 0) {
                            valores[i].push('----');
                        } else {
                            valores[i].push(this.valoresPieza[i][nombres[j]]);
                        }
                    }
                }
                return valores;
            }

            crearTabla(titulos, cNomiPosiciones, tolePosiciones, piezaPosiciones, valoresCNomi, valoresTole,
                valoresPieza) { // Crear tabla
                const table = document.createElement('table'); // Crear tabla
                table.className = 'tabla3';

                for (let i = 0; i < 4; i++) { // Crear filas
                    const tr = document.createElement('tr'); // Crear fila
                    switch (i) { // Crear columnas
                        case 0: // Crear columnas de titulos
                            for (let j = 0; j < titulos.length; j++) { // Crear columnas
                                const th = document.createElement('th'); // Crear columna
                                th.className = 't-title'; // Agregar clase a la columna
                                if (j == 0) { // Si es la primera columna
                                    th.style = "width:150px;"; // Agregar estilo a la columna
                                }
                                if (titulos[j] == 'Observaciones') {
                                    th.style = "width:1050px;"; // Agregar estilo a la columna
                                }
                                th.innerHTML = titulos[j]; // Agregar texto a la columna
                                tr.appendChild(th); // Agregar columna a la fila
                            }
                            table.appendChild(tr); //Agregar fila a la tabla.
                            break;

                        case 1: // Crear columnas de cNominal
                            if (valoresCNomi != null) {
                                let nomiDataIndex = 0;
                                for (let j = 0; j < titulos.length; j++) { // Iteramos sobre las COLUMNAS reales
                                    const td = document.createElement('td');
                                    if (j != 0) {
                                        if (cNomiPosiciones != null && cNomiPosiciones.includes(j)) {
                                            for (let k = 0; k < 2; k++) {
                                                let inputMedio = this.crearInputs('input-medio', valoresCNomi[
                                                    nomiDataIndex]);
                                                td.appendChild(inputMedio);
                                                nomiDataIndex++;
                                            }
                                        } else {
                                            td.appendChild(this.crearInputs('input', valoresCNomi[
                                                nomiDataIndex])); // Crear inputs
                                            nomiDataIndex++;
                                        }
                                    } else {
                                        td.innerHTML = 'C.Nominal';
                                        nomiDataIndex++;
                                    }
                                    tr.appendChild(td);
                                }
                                table.appendChild(tr); //Agregar fila a la tabla.
                            }
                            break;

                        case 2: // Crear columnas de tolerancias
                            if (valoresTole != null) {
                                let toleDataIndex = 0;
                                for (let j = 0; j < titulos.length; j++) {
                                    const td = document.createElement('td');
                                    if (j != 0) {
                                        if (tolePosiciones != null && tolePosiciones.includes(j)) {
                                            for (let k = 0; k < 2; k++) {
                                                // Adjust visual strings for combined values
                                                let val = valoresTole[toleDataIndex];
                                                if (val !== "" && val !== null && val !== undefined) {
                                                    val = (k === 0 ? '+ ' : '- ') + val;
                                                } else {
                                                    val = (k === 0 ? '+ ' : '- ');
                                                }
                                                let inputMedio = this.crearInputs('input-medio', val);
                                                td.appendChild(inputMedio);
                                                toleDataIndex++;
                                            }
                                        } else {
                                            let val = valoresTole[toleDataIndex];
                                            let titleLower = titulos[j].toLowerCase();
                                            let isExcluded = ['error', 'observaciones', 'acet', 'guage', 'altura total',
                                                'gaugue_ceja', 'altura_total'
                                            ].some(kw => titleLower.includes(kw));
                                            if (isExcluded) {
                                                // Do not prepend ± to text fields
                                            } else {
                                                if (val !== "" && val !== null && val !== undefined) {
                                                    val = '± ' + val;
                                                } else {
                                                    val = '± ';
                                                }
                                            }
                                            td.appendChild(this.crearInputs('input', val)); // Crear inputs
                                            toleDataIndex++;
                                        }
                                    } else {
                                        td.innerHTML = 'Tolerancias';
                                        toleDataIndex++;
                                    }
                                    tr.appendChild(td);
                                }
                                table.appendChild(tr); //Agregar fila a la tabla.
                            }
                            break;
                        case 3: // Crear columnas de pieza
                            for (let j = 0; j < valoresPieza.length; j++) {
                                let tr = document.createElement('tr');
                                let piezaDataIndex = 0;
                                for (let p = 0; p < titulos.length; p++) {
                                    let error = false;
                                    const td = document.createElement('td');
                                    if (p != 0) {
                                        if (piezaPosiciones != null && piezaPosiciones.includes(p)) {
                                            for (let k = 0; k < 2; k++) {
                                                if (valoresCNomi != null && valoresTole != null) {
                                                    error = this.getError(valoresPieza[j][piezaDataIndex], p, k,
                                                        valoresCNomi, valoresTole, tolePosiciones,
                                                        cNomiPosiciones, piezaPosiciones);
                                                }
                                                let inputMedio = this.crearInputs('input-medio', valoresPieza[j][
                                                    piezaDataIndex
                                                ], error);
                                                td.appendChild(inputMedio);
                                                piezaDataIndex++;
                                            }
                                        } else {
                                            if (valoresCNomi != null && valoresTole != null) {
                                                error = this.getError(valoresPieza[j][piezaDataIndex], p, null,
                                                    valoresCNomi, valoresTole, tolePosiciones,
                                                    cNomiPosiciones, piezaPosiciones);
                                            }
                                            td.appendChild(this.crearInputs('input', valoresPieza[j][
                                                piezaDataIndex
                                            ], error));
                                            piezaDataIndex++;
                                        }
                                    } else {
                                        td.appendChild(this.crearInputs('input', valoresPieza[j][
                                            piezaDataIndex
                                        ]));
                                        piezaDataIndex++;
                                    }
                                    tr.appendChild(td);
                                }
                                table.appendChild(tr);
                            }
                            break;
                    }
                }
                return table; // Retornar tabla.
            }
            crearInputs(className, valor, error) { // Crear inputs
                let input = document.createElement('input'); // Crear input
                input.className = className; // Agregar clase al input
                input.type = 'text'; // Agregar tipo al input
                input.step = 'any'; // Agregar step al input
                input.inputMode = "decimal"; // Agregar inputMode al input
                input.value = (valor === undefined || valor === null) ? "" : valor; // Agregar valor al input
                input.disabled = 'true';
                if (error != undefined) {
                    if (error === true) {
                        input.style = 'border: 3px solid red;';
                    }
                }
                return input; // Retornar input
            }
            getError(valorPieza, posicion, k, valoresCnomi, valoresTole, tolePosiciones, cNomiPosiciones, piezaPosiciones) {
                if (valorPieza === undefined || valorPieza === null || valorPieza === "") {
                    return false;
                }
                if (posicion === 0) return false;

                let nomiIdx = 1;
                let toleIdx = 1;

                for (let i = 1; i < posicion; i++) {
                    if (cNomiPosiciones != null && cNomiPosiciones.includes(i)) nomiIdx += 2;
                    else nomiIdx++;
                    if (tolePosiciones != null && tolePosiciones.includes(i)) toleIdx += 2;
                    else toleIdx++;
                }

                let cNomiValStr = undefined;
                if (cNomiPosiciones != null && cNomiPosiciones.includes(posicion)) {
                    cNomiValStr = valoresCnomi[nomiIdx + (k === 1 ? 1 : 0)];
                } else {
                    cNomiValStr = valoresCnomi[nomiIdx];
                }

                // Si la cota nominal no existe o está en blanco, no puede haber error
                if (cNomiValStr === undefined || cNomiValStr === null || cNomiValStr === "") return false;

                // Verificar si es un string (Acetato, Bien, Mal, Si, No, etc.)
                if (isNaN(parseFloat(valorPieza)) || isNaN(parseFloat(cNomiValStr))) {
                    let textPieza = String(valorPieza).trim().toLowerCase();
                    let textCnomi = String(cNomiValStr).trim().toLowerCase();

                    if (textPieza !== textCnomi && textPieza !== "----") {
                        return true;
                    }
                    return false;
                }

                let cNomiVal = parseFloat(cNomiValStr);
                let error = false;
                let limiteInferior, limiteSuperior;

                if (tolePosiciones != null && tolePosiciones.includes(posicion)) {
                    if (piezaPosiciones != null && piezaPosiciones.includes(posicion)) {
                        let toleVal = parseFloat(valoresTole[toleIdx + (k === 1 ? 1 : 0)]);
                        limiteInferior = cNomiVal - toleVal;
                        limiteSuperior = cNomiVal + toleVal;
                    } else {
                        let tole0 = parseFloat(valoresTole[toleIdx]);
                        let tole1 = parseFloat(valoresTole[toleIdx + 1]);
                        limiteInferior = cNomiVal - tole1;
                        limiteSuperior = cNomiVal + tole0;
                    }
                } else {
                    let tole0 = parseFloat(valoresTole[toleIdx]);
                    limiteInferior = cNomiVal - tole0;
                    limiteSuperior = cNomiVal + tole0;
                }

                if (parseFloat(valorPieza) < parseFloat(limiteInferior).toFixed(3) || parseFloat(valorPieza) >
                    parseFloat(limiteSuperior).toFixed(3)) {
                    error = true;
                }
                return error;
            }

            convertirObjectToArray(obj) {
                let array = [];
                for (let i = 0; i < obj.length; i++) {
                    array.push(Object.values(obj[i]));
                }
                return array;
            }
            crearTablaOperadores(operadores) {
                const table = document.createElement('table');
                table.className = "tablaOperadores";
                table.style.borderCollapse = 'collapse'; // Colapsar los bordes de las celdas
                table.style.border = '1px solid black';


                const tbody = document.createElement('tbody');
                for (let i = 0; i < (operadores.length + 1); i++) {
                    const tr = document.createElement('tr');
                    switch (i) {
                        case 0:
                            const th1 = document.createElement('th');
                            th1.textContent = "No. Pieza";
                            const th2 = document.createElement('th');
                            th2.textContent = "Operador";
                            tr.appendChild(th1);
                            tr.appendChild(th2);
                            th1.style.border = '1px solid black';
                            th2.style.border = '1px solid black';

                            break;

                        default:
                            for (let j = 0; j < operadores[i - 1].length; j++) {
                                const td = document.createElement('td');
                                td.textContent = operadores[i - 1][j];
                                tr.appendChild(td);
                            }
                            tr.querySelectorAll('td').forEach(td => {
                                td.style.border = '1px solid black';

                                tr.appendChild(td);
                            });
                            break;
                    }
                    tr.style.border = '1px solid black';
                    tbody.appendChild(tr);
                }
                table.appendChild(tbody);
                return table;
            }
        }
    </script>
    @if ($process == 'Soldadura' || $process == 'Soldadura PTA' || $process == 'Rectificado' || $process == 'Palomas')
        <style>
            .tabla3 {
                width: 130%;
            }
        </style>
    @endif
    @if ($process == 'Copiado')
        <style>
            .scrollabe-table {
                height: 500px;
            }
        </style>
    @endif

    <body background="{{ asset('images/fondoLogin.jpg') }}">

        <div class="container" id="container">

            <a href="javascript:history.back()"" class=" btn-regresar">Regresar</a>

            <script>
                let process = new Proceso(@json($process), @json($cNominal), @json($tolerance),
                    @json($piecesInfo)); // Crear el proceso
                document.getElementById('container').appendChild(process.crearTablaOperadores(
                    @json($operadores))); // Crear tabla de operadores
            </script>
            @csrf
            <div class="titles">
                <label class="title">{{ $ot }}</label>
                <label class="title">{{ $clase }} - {{ $process }}</label>
            </div>
            <div class="scrollabe-table" id="scrollabe-table">
                @if ($process == 'Asentado')
                    <table border="1" class="tabla3 cp-width-100pct">
                        <tr>
                            <th class="t-title cp-width-150px">#PZ</th>
                            <th class="t-title">Sin juego</th>
                            <th class="t-title">Sin luz</th>
                            <th class="t-title">Error</th>
                            <th class="t-title cp-width-700px">Observaciones</th>
                        </tr>
                        @foreach ($piecesInfo as $piece)
                            <tr>
                                <td><input type="text" class="input" value="{{ $piece->n_juego ?? ($piece['n_juego'] ?? '') }}"
                                        disabled></td>
                                <td><input type="text" class="input" value="{{ $piece->sin_juego ?? ($piece['sin_juego'] ?? '') }}"
                                        disabled></td>
                                <td><input type="text" class="input" value="{{ $piece->sin_luz ?? ($piece['sin_luz'] ?? '') }}"
                                        disabled></td>
                                <td><input type="text" class="input" value="{{ $piece->error ?? ($piece['error'] ?? '') }}"
                                        disabled /></td>
                                <td><input type="text" class="input"
                                        value="{{ $piece->observaciones ?? ($piece['observaciones'] ?? '') }}" disabled />
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @elseif ($process == 'Cavidades')
                    <table class="tabla3">
                        <tr>
                            <th class="t-title cp-width-150px">#PZ</th>
                            <th class="t-title" colspan="2">
                                Altura 1
                                @if (isset($cNominal->altura1) && !isset($cNominal->profundidad1))
                                    <div class="cp-margin-top-6px">
                                        <span class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                            <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                            {{ $cNominal->altura1 }} mm
                                        </span>
                                    </div>
                                @elseif (isset($cNominal->profundidad1))
                                    <div class="cp-margin-top-6px">
                                        <span class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                            <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                            {{ $cNominal->profundidad1 }} mm
                                        </span>
                                    </div>
                                @endif
                            </th>
                            <th class="t-title" colspan="2">
                                Altura 2
                                @if (isset($cNominal->altura2) && !isset($cNominal->profundidad1))
                                    <div class="cp-margin-top-6px">
                                        <span class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                            <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                            {{ $cNominal->altura2 }} mm
                                        </span>
                                    </div>
                                @elseif (isset($cNominal->profundidad2))
                                    <div class="cp-margin-top-6px">
                                        <span class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                            <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                            {{ $cNominal->profundidad2 }} mm
                                        </span>
                                    </div>
                                @endif
                            </th>
                            <th class="t-title" colspan="2">
                                Altura 3
                                @if (isset($cNominal->altura3) && !isset($cNominal->profundidad1))
                                    <div class="cp-margin-top-6px">
                                        <span class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                            <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                            {{ $cNominal->altura3 }} mm
                                        </span>
                                    </div>
                                @elseif (isset($cNominal->profundidad3))
                                    <div class="cp-margin-top-6px">
                                        <span class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                            <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                            {{ $cNominal->profundidad3 }} mm
                                        </span>
                                    </div>
                                @endif
                            </th>
                            <th class="t-title"></th>
                            <th class="t-title" colspan="2"></th>
                        </tr>
                        <tr>
                            <th class="t-title"></th>
                            @if (isset($cNominal->altura1) && !isset($cNominal->profundidad1))
                                <th colspan="2">Altura</th>
                                <th colspan="2">Altura</th>
                                <th colspan="2">Altura</th>
                            @else
                                <th>Profundidad</th>
                                <th>Diametro</th>
                                <th>Profundidad</th>
                                <th>Diametro</th>
                                <th>Profundidad</th>
                                <th>Diametro</th>
                            @endif
                            <th>Acetato B/M</th>
                            <th>Error</th>
                            <th class="cp-width-1000px">Observaciones</th>
                        </tr>
                        <tr>
                            <td>C.Nominal</td>
                            @if (isset($cNominal->altura1) && !isset($cNominal->profundidad1))
                                <td colspan="2"><input type="number" value="{{ $cNominal->altura1 }}" class="input" step="any"
                                        inputmode="decimal" disabled></td>
                                <td colspan="2"><input type="number" value="{{ $cNominal->altura2 }}" class="input" step="any"
                                        inputmode="decimal" disabled></td>
                                <td colspan="2"><input type="number" value="{{ $cNominal->altura3 }}" class="input" step="any"
                                        inputmode="decimal" disabled></td>
                            @else
                                <td><input type="number" value="{{ $cNominal->profundidad1 }}" class="input" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" value="{{ $cNominal->diametro1 }}" class="input" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" value="{{ $cNominal->profundidad2 }}" class="input" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" value="{{ $cNominal->diametro2 }}" class="input" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" value="{{ $cNominal->profundidad3 }}" class="input" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" value="{{ $cNominal->diametro3 }}" class="input" step="any"
                                        inputmode="decimal" disabled></td>
                            @endif
                            <td><input type="number" class="input" disabled></td>
                            <td><input type="number" class="input" disabled></td>
                            <td><input type="number" class="input" disabled></td>
                        </tr>
                        <tr>
                            <td> Tolerancias </td>
                            @if (isset($cNominal->altura1) && !isset($cNominal->profundidad1))
                                <td colspan="2"><input type="text"
                                        value="{{ (isset($tolerance->altura1) && $tolerance->altura1 !== '') ? '± ' . $tolerance->altura1 : '± ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled></td>
                                <td colspan="2"><input type="text"
                                        value="{{ (isset($tolerance->altura2) && $tolerance->altura2 !== '') ? '± ' . $tolerance->altura2 : '± ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled></td>
                                <td colspan="2"><input type="text"
                                        value="{{ (isset($tolerance->altura3) && $tolerance->altura3 !== '') ? '± ' . $tolerance->altura3 : '± ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled></td>
                            @else
                                <td><input type="text"
                                        value="{{ (isset($tolerance->profundidad1_1) && $tolerance->profundidad1_1 !== '') ? '+ ' . $tolerance->profundidad1_1 : '+ ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled><input type="text"
                                        value="{{ (isset($tolerance->profundidad2_1) && $tolerance->profundidad2_1 !== '') ? '- ' . $tolerance->profundidad2_1 : '- ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text"
                                        value="{{ (isset($tolerance->diametro1_1) && $tolerance->diametro1_1 !== '') ? '+ ' . $tolerance->diametro1_1 : '+ ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled><input type="text"
                                        value="{{ (isset($tolerance->diametro2_1) && $tolerance->diametro2_1 !== '') ? '- ' . $tolerance->diametro2_1 : '- ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text"
                                        value="{{ (isset($tolerance->profundidad1_2) && $tolerance->profundidad1_2 !== '') ? '+ ' . $tolerance->profundidad1_2 : '+ ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled><input type="text"
                                        value="{{ (isset($tolerance->profundidad2_2) && $tolerance->profundidad2_2 !== '') ? '- ' . $tolerance->profundidad2_2 : '- ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text"
                                        value="{{ (isset($tolerance->diametro1_2) && $tolerance->diametro1_2 !== '') ? '+ ' . $tolerance->diametro1_2 : '+ ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled><input type="text"
                                        value="{{ (isset($tolerance->diametro2_2) && $tolerance->diametro2_2 !== '') ? '- ' . $tolerance->diametro2_2 : '- ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text"
                                        value="{{ (isset($tolerance->profundidad1_3) && $tolerance->profundidad1_3 !== '') ? '+ ' . $tolerance->profundidad1_3 : '+ ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled><input type="text"
                                        value="{{ (isset($tolerance->profundidad2_3) && $tolerance->profundidad2_3 !== '') ? '- ' . $tolerance->profundidad2_3 : '- ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text"
                                        value="{{ (isset($tolerance->diametro1_3) && $tolerance->diametro1_3 !== '') ? '+ ' . $tolerance->diametro1_3 : '+ ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled><input type="text"
                                        value="{{ (isset($tolerance->diametro2_3) && $tolerance->diametro2_3 !== '') ? '- ' . $tolerance->diametro2_3 : '- ' }}"
                                        class="input-medio" step="any" inputmode="decimal" disabled></td>
                            @endif
                            <td><input type="number" class="input" disabled></td>
                            <td><input type="number" class="input" disabled></td>
                            <td><input type="number" class="input" disabled></td>
                        </tr>
                        @foreach ($piecesInfo as $pieceInfo)
                            <tr>
                                <td><input type="text" class="input" value="{{ $pieceInfo['n_pieza'] }}" disabled>
                                </td>
                                @if (isset($cNominal->altura1) && !isset($cNominal->profundidad1))
                                    @php
                                        // Altura1 validation (symmetric tolerance)
                                        $altura1_error = false;
                                        if (isset($cNominal->altura1) && isset($tolerance->altura1)) {
                                            $upper = $cNominal->altura1 + $tolerance->altura1;
                                            $lower = $cNominal->altura1 - $tolerance->altura1;
                                            $altura1_error =
                                                ($pieceInfo['altura1'] ?? 0) < $lower ||
                                                ($pieceInfo['altura1'] ?? 0) > $upper;
                                        }

                                        // Altura2 validation (symmetric tolerance)
                                        $altura2_error = false;
                                        if (isset($cNominal->altura2) && isset($tolerance->altura2)) {
                                            $upper = $cNominal->altura2 + $tolerance->altura2;
                                            $lower = $cNominal->altura2 - $tolerance->altura2;
                                            $altura2_error =
                                                ($pieceInfo['altura2'] ?? 0) < $lower ||
                                                ($pieceInfo['altura2'] ?? 0) > $upper;
                                        }

                                        // Altura3 validation (symmetric tolerance)
                                        $altura3_error = false;
                                        if (isset($cNominal->altura3) && isset($tolerance->altura3)) {
                                            $upper = $cNominal->altura3 + $tolerance->altura3;
                                            $lower = $cNominal->altura3 - $tolerance->altura3;
                                            $altura3_error =
                                                ($pieceInfo['altura3'] ?? 0) < $lower ||
                                                ($pieceInfo['altura3'] ?? 0) > $upper;
                                        }
                                    @endphp
                                    <td colspan="2"><input type="number" class="input" value="{{ $pieceInfo['altura1'] ?? '' }}"
                                            step="any" inputmode="decimal" style="{{ $altura1_error ? 'border: 3px solid red;' : '' }}"
                                            disabled></td>
                                    <td colspan="2"><input type="number" class="input" value="{{ $pieceInfo['altura2'] ?? '' }}"
                                            step="any" inputmode="decimal" style="{{ $altura2_error ? 'border: 3px solid red;' : '' }}"
                                            disabled></td>
                                    <td colspan="2"><input type="number" class="input" value="{{ $pieceInfo['altura3'] ?? '' }}"
                                            step="any" inputmode="decimal" style="{{ $altura3_error ? 'border: 3px solid red;' : '' }}"
                                            disabled></td>
                                @else
                                    @php
                                        // Profundidad1 validation
                                        $prof1_error = false;
                                        if (
                                            isset($cNominal->profundidad1) &&
                                            isset($tolerance->profundidad1_1) &&
                                            isset($tolerance->profundidad2_1)
                                        ) {
                                            $upper = $cNominal->profundidad1 + $tolerance->profundidad1_1;
                                            $lower = $cNominal->profundidad1 - $tolerance->profundidad2_1;
                                            $prof1_error =
                                                $pieceInfo['profundidad1'] < $lower ||
                                                $pieceInfo['profundidad1'] > $upper;
                                        }

                                        // Diametro1 validation
                                        $diam1_error = false;
                                        if (
                                            isset($cNominal->diametro1) &&
                                            isset($tolerance->diametro1_1) &&
                                            isset($tolerance->diametro2_1)
                                        ) {
                                            $upper = $cNominal->diametro1 + $tolerance->diametro1_1;
                                            $lower = $cNominal->diametro1 - $tolerance->diametro2_1;
                                            $diam1_error =
                                                $pieceInfo['diametro1'] < $lower || $pieceInfo['diametro1'] > $upper;
                                        }

                                        // Profundidad2 validation
                                        $prof2_error = false;
                                        if (
                                            isset($cNominal->profundidad2) &&
                                            isset($tolerance->profundidad1_2) &&
                                            isset($tolerance->profundidad2_2)
                                        ) {
                                            $upper = $cNominal->profundidad2 + $tolerance->profundidad1_2;
                                            $lower = $cNominal->profundidad2 - $tolerance->profundidad2_2;
                                            $prof2_error =
                                                $pieceInfo['profundidad2'] < $lower ||
                                                $pieceInfo['profundidad2'] > $upper;
                                        }

                                        // Diametro2 validation
                                        $diam2_error = false;
                                        if (
                                            isset($cNominal->diametro2) &&
                                            isset($tolerance->diametro1_2) &&
                                            isset($tolerance->diametro2_2)
                                        ) {
                                            $upper = $cNominal->diametro2 + $tolerance->diametro1_2;
                                            $lower = $cNominal->diametro2 - $tolerance->diametro2_2;
                                            $diam2_error =
                                                $pieceInfo['diametro2'] < $lower || $pieceInfo['diametro2'] > $upper;
                                        }

                                        // Profundidad3 validation
                                        $prof3_error = false;
                                        if (
                                            isset($cNominal->profundidad3) &&
                                            isset($tolerance->profundidad1_3) &&
                                            isset($tolerance->profundidad2_3)
                                        ) {
                                            $upper = $cNominal->profundidad3 + $tolerance->profundidad1_3;
                                            $lower = $cNominal->profundidad3 - $tolerance->profundidad2_3;
                                            $prof3_error =
                                                $pieceInfo['profundidad3'] < $lower ||
                                                $pieceInfo['profundidad3'] > $upper;
                                        }

                                        // Diametro3 validation
                                        $diam3_error = false;
                                        if (
                                            isset($cNominal->diametro3) &&
                                            isset($tolerance->diametro1_3) &&
                                            isset($tolerance->diametro2_3)
                                        ) {
                                            $upper = $cNominal->diametro3 + $tolerance->diametro1_3;
                                            $lower = $cNominal->diametro3 - $tolerance->diametro2_3;
                                            $diam3_error =
                                                $pieceInfo['diametro3'] < $lower || $pieceInfo['diametro3'] > $upper;
                                        }
                                    @endphp
                                    <td><input type="number" class="input" style="{{ $prof1_error ? 'border: 3px solid red;' : '' }}"
                                            value="{{ $pieceInfo['profundidad1'] }}" step="any" inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input" style="{{ $diam1_error ? 'border: 3px solid red;' : '' }}"
                                            value="{{ $pieceInfo['diametro1'] }}" step="any" inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input" style="{{ $prof2_error ? 'border: 3px solid red;' : '' }}"
                                            value="{{ $pieceInfo['profundidad2'] }}" step="any" inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input" style="{{ $diam2_error ? 'border: 3px solid red;' : '' }}"
                                            value="{{ $pieceInfo['diametro2'] }}" step="any" inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input" style="{{ $prof3_error ? 'border: 3px solid red;' : '' }}"
                                            value="{{ $pieceInfo['profundidad3'] }}" step="any" inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input" style="{{ $diam3_error ? 'border: 3px solid red;' : '' }}"
                                            value="{{ $pieceInfo['diametro3'] }}" step="any" inputmode="decimal" disabled></td>
                                @endif
                                <td><input type="text" class="input" value="{{ $pieceInfo['acetatoBM'] }}" disabled>
                                </td>
                                <td><input type="text" class="input" value="{{ $pieceInfo['error'] }}" disabled>
                                </td>
                                <td><input type="text" class="input" value="{{ $pieceInfo['observaciones'] }}" disabled></td>
                            </tr>
                        @endforeach
                    </table>
                @elseif ($process == 'Copiado')
                    <table border="1" class="tabla3">
                        <label class="title-subproceso"> C I L I N D R A D O</label>
                        <tr>
                            <th class="t-title cp-width-150px">#PZ</th>
                            <th class="t-title">Diametro 1</th>
                            <th class="t-title">Profundidad 1</th>
                            <th class="t-title">Diametro 2</th>
                            <th class="t-title">Profundidad 2</th>
                            <th class="t-title">Diametro de sufridera</th>
                            <th class="t-title">Diametro de ranura</th>
                            <th class="t-title">Profundidad de ranura</th>
                            <th class="t-title">Profundidad de sufridera</th>
                            <th class="t-title">Altura total</th>
                            <th class="t-title cp-width-200px">Error</th><br>
                            <th class="t-title cp-width-700px">Observaciones</th>
                        </tr>
                        <tr>
                            <td>C.Nominal.</td>
                            <td><input type="number" class="input" value="{{ $cNominal->diametro1_cilindrado }}" disabled></td>
                            <td><input type="number" class="input" value="{{ $cNominal->profundidad1_cilindrado }}" disabled>
                            </td>
                            <td><input type="number" class="input" value="{{ $cNominal->diametro2_cilindrado }}" disabled></td>
                            <td><input type="number" class="input" value="{{ $cNominal->profundidad2_cilindrado }}" disabled>
                            </td>
                            <td><input type="number" class="input" value="{{ $cNominal->diametro_sufridera }}" disabled></td>
                            <td><input type="number" class="input" value="{{ $cNominal->diametro_ranura }}" disabled>
                            </td>
                            <td><input type="number" class="input" value="{{ $cNominal->profundidad_ranura }}" disabled></td>
                            <td><input type="number" class="input" value="{{ $cNominal->profundidad_sufridera }}" disabled></td>
                            <td><input type="number" class="input" value="{{ $cNominal->altura_total }}" disabled>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td> Tolerancias. </td>
                            <td><input type="text" class="input"
                                    value="{{ (isset($tolerance->diametro1_cilindrado) && $tolerance->diametro1_cilindrado !== '') ? '± ' . $tolerance->diametro1_cilindrado : '± ' }}"
                                    disabled></td>
                            <td><input type="text" class="input"
                                    value="{{ (isset($tolerance->profundidad1_cilindrado) && $tolerance->profundidad1_cilindrado !== '') ? '± ' . $tolerance->profundidad1_cilindrado : '± ' }}"
                                    disabled>
                            </td>
                            <td><input type="text" class="input"
                                    value="{{ (isset($tolerance->diametro2_cilindrado) && $tolerance->diametro2_cilindrado !== '') ? '± ' . $tolerance->diametro2_cilindrado : '± ' }}"
                                    disabled></td>
                            <td><input type="text" class="input"
                                    value="{{ (isset($tolerance->profundidad2_cilindrado) && $tolerance->profundidad2_cilindrado !== '') ? '± ' . $tolerance->profundidad2_cilindrado : '± ' }}"
                                    disabled>
                            </td>
                            <td><input type="text" class="input"
                                    value="{{ (isset($tolerance->diametro_sufridera) && $tolerance->diametro_sufridera !== '') ? '± ' . $tolerance->diametro_sufridera : '± ' }}"
                                    disabled></td>
                            <td><input type="text" class="input"
                                    value="{{ (isset($tolerance->diametro_ranura) && $tolerance->diametro_ranura !== '') ? '± ' . $tolerance->diametro_ranura : '± ' }}"
                                    disabled>
                            </td>
                            <td><input type="text" class="input"
                                    value="{{ (isset($tolerance->profundidad_ranura) && $tolerance->profundidad_ranura !== '') ? '± ' . $tolerance->profundidad_ranura : '± ' }}"
                                    disabled></td>
                            <td><input type="text" class="input"
                                    value="{{ (isset($tolerance->profundidad_sufridera) && $tolerance->profundidad_sufridera !== '') ? '± ' . $tolerance->profundidad_sufridera : '± ' }}"
                                    disabled>
                            </td>
                            <td><input type="text" class="input"
                                    value="{{ (isset($tolerance->altura_total) && $tolerance->altura_total !== '') ? '± ' . $tolerance->altura_total : '± ' }}"
                                    disabled>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        @foreach ($piecesInfo as $pieceInfo)
                            <tr>
                                <td><input type="text" class="input" value="{{ $pieceInfo['n_pieza'] }}" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" class="input" value="{{ $pieceInfo['diametro1_cilindrado'] }}" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" class="input" value="{{ $pieceInfo['profundidad1_cilindrado'] }}"
                                        step="any" inputmode="decimal" disabled></td>
                                <td><input type="number" class="input" value="{{ $pieceInfo['diametro2_cilindrado'] }}" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" class="input" value="{{ $pieceInfo['profundidad2_cilindrado'] }}"
                                        step="any" inputmode="decimal" disabled></td>
                                <td><input type="number" class="input" value="{{ $pieceInfo['diametro_sufridera'] }}" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" class="input" value="{{ $pieceInfo['diametro_ranura'] }}" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" class="input" value="{{ $pieceInfo['profundidad_ranura'] }}" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" class="input" value="{{ $pieceInfo['profundidad_sufridera'] }}" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" class="input" value="{{ $pieceInfo['altura_total'] }}" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="text" class="input" value="{{ $pieceInfo['error_cilindrado'] }}" disabled></td>
                                <td><input type="text" class="input" value="{{ $pieceInfo['observaciones_cilindrado'] }}" disabled>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @elseif($process == 'Off Set')
                    <table border="1" class="tabla3">
                        <>
                            <tr>
                                <th class="t-title cp-width-150px">#PZ</th>
                                <th class="t-title cp-width-200px cp-border-bottom-none">Ancho de
                                    altura</th>
                                <th class="t-title" colspan="2">Profundidad de tacon</th>
                                <th class="t-title" colspan="2">Simetría</th>
                                <th class="t-title cp-width-200px cp-border-bottom-none">Ancho del
                                    tacon</th>
                                <th class="t-title" colspan="2">Barreno Lateral</th>
                                <th class="t-title cp-width-200px cp-border-bottom-none">Altura tacon
                                    inicial</th>
                                <th class="t-title cp-width-200px cp-border-bottom-none">Altura tacon
                                    intermedia</th>
                                <th class="t-title cp-width-200px cp-border-bottom-none">Error</th>
                                <th class="t-title cp-width-700px cp-border-bottom-none">Observaciones</th>
                            </tr>
                            <tr>
                                <th class="t-title"></th>
                                <th class="cp-border-bottom-none cp-border-top-none"></th>
                                <th>Hembra</th>
                                <th>Macho</th>
                                <th>Hembra</th>
                                <th>Macho</th>
                                <th class="cp-border-bottom-none cp-border-top-none"></th>
                                <th>Hembra</th>
                                <th>Macho</th>
                                <th class="cp-border-bottom-none cp-border-top-none"></th>
                                <th class="cp-border-bottom-none cp-border-top-none"></th>
                                <th class="cp-border-bottom-none cp-border-top-none"></th>
                                <th class="cp-border-bottom-none cp-border-top-none"></th>
                            </tr>
                            <tr>
                                <td>C.Nominal</td>
                                <td><input type="number" name="cNomi_anchoRanura" value="{{ $cNominal->anchoRanura }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="number" name="cNomi_profuTaconHembra" value="{{ $cNominal->profuTaconHembra }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="number" name="cNomi_profuTaconMacho" value="{{ $cNominal->profuTaconMacho }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="number" name="cNomi_simetriaHembra" value="{{ $cNominal->simetriaHembra }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="number" name="cNomi_simetriaMacho" value="{{ $cNominal->simetriaMacho }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="number" name="cNomi_anchoTacon" value="{{ $cNominal->anchoTacon }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="number" name="cNomi_barrenoLateralHembra"
                                        value="{{ $cNominal->barrenoLateralHembra }}" class="input" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" name="cNomi_barrenoLateralMacho"
                                        value="{{ $cNominal->barrenoLateralMacho }}" class="input" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number" name="cNomi_alturaTaconInicial"
                                        value="{{ (isset($cNominal->alturaTaconInicial) && $cNominal->alturaTaconInicial !== '') ? number_format($cNominal->alturaTaconInicial, 3) : '' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="number" name="cNomi_alturaTaconIntermedia"
                                        value="{{ (isset($cNominal->alturaTaconIntermedia) && $cNominal->alturaTaconIntermedia !== '') ? number_format($cNominal->alturaTaconIntermedia, 3) : '' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="number" class="input" disabled></td>
                                <td><input type="number" class="input" disabled></td>
                            </tr>
                            <tr>
                                <td> Tolerancias </td>
                                <td><input type="text" name="tole_anchoRanura"
                                        value="{{ (isset($tolerance->anchoRanura) && $tolerance->anchoRanura !== '') ? '± ' . $tolerance->anchoRanura : '± ' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text" name="tole_profuTaconHembra"
                                        value="{{ (isset($tolerance->profuTaconHembra) && $tolerance->profuTaconHembra !== '') ? '± ' . $tolerance->profuTaconHembra : '± ' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text" name="tole_profuTaconMacho"
                                        value="{{ (isset($tolerance->profuTaconMacho) && $tolerance->profuTaconMacho !== '') ? '± ' . $tolerance->profuTaconMacho : '± ' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text" name="tole_simetriaHembra"
                                        value="{{ (isset($tolerance->simetriaHembra) && $tolerance->simetriaHembra !== '') ? '± ' . $tolerance->simetriaHembra : '± ' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text" name="tole_simetriaMacho"
                                        value="{{ (isset($tolerance->simetriaMacho) && $tolerance->simetriaMacho !== '') ? '± ' . $tolerance->simetriaMacho : '± ' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text" name="tole_anchoTacon"
                                        value="{{ (isset($tolerance->anchoTacon) && $tolerance->anchoTacon !== '') ? '± ' . $tolerance->anchoTacon : '± ' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text" name="tole_barrenoLateralHembra"
                                        value="{{ (isset($tolerance->barrenoLateralHembra) && $tolerance->barrenoLateralHembra !== '') ? '± ' . $tolerance->barrenoLateralHembra : '± ' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text" name="tole_barrenoLateralMacho"
                                        value="{{ (isset($tolerance->barrenoLateralMacho) && $tolerance->barrenoLateralMacho !== '') ? '± ' . $tolerance->barrenoLateralMacho : '± ' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text" name="tole_alturaTaconInicial"
                                        value="{{ (isset($tolerance->alturaTaconInicial) && $tolerance->alturaTaconInicial !== '') ? '± ' . number_format($tolerance->alturaTaconInicial, 3) : '± ' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text" name="tole_alturaTaconIntermedia"
                                        value="{{ (isset($tolerance->alturaTaconIntermedia) && $tolerance->alturaTaconIntermedia !== '') ? '± ' . number_format($tolerance->alturaTaconIntermedia, 3) : '± ' }}"
                                        class="input" step="any" inputmode="decimal" disabled></td>
                                <td><input type="text" class="input" disabled></td>
                                <td><input type="text" class="input" disabled></td>
                            </tr>
                            @foreach ($piecesInfo as $pieceInfo)
                                @php
                                    $fields = [
                                        'anchoRanura',
                                        'profuTaconHembra',
                                        'profuTaconMacho',
                                        'simetriaHembra',
                                        'simetriaMacho',
                                        'anchoTacon',
                                        'barrenoLateralHembra',
                                        'barrenoLateralMacho',
                                        'alturaTaconInicial',
                                        'alturaTaconIntermedia'
                                    ];
                                    $errorsInfo = [];
                                    foreach ($fields as $field) {
                                        $errorsInfo[$field] = false;
                                        if (isset($cNominal->$field) && isset($tolerance->$field) && is_numeric($cNominal->$field) && is_numeric($tolerance->$field) && isset($pieceInfo[$field]) && is_numeric($pieceInfo[$field])) {
                                            $upper = $cNominal->$field + $tolerance->$field;
                                            $lower = $cNominal->$field - $tolerance->$field;
                                            $errorsInfo[$field] = $pieceInfo[$field] < $lower || $pieceInfo[$field] > $upper;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td><input type="text" class="input" value="{{ $pieceInfo['n_pieza'] }}" disabled></td>
                                    <td><input type="number" class="input cp-border-3px-solid-red"' : '' !!} value="{{ $pieceInfo['anchoRanura'] }}" step="any"
                                            inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input cp-border-3px-solid-red"' : '' !!} value="{{ $pieceInfo['profuTaconHembra'] }}" step="any"
                                            inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input cp-border-3px-solid-red"' : '' !!} value="{{ $pieceInfo['profuTaconMacho'] }}" step="any"
                                            inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input cp-border-3px-solid-red"' : '' !!} value="{{ $pieceInfo['simetriaHembra'] }}" step="any"
                                            inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input cp-border-3px-solid-red"' : '' !!} value="{{ $pieceInfo['simetriaMacho'] }}" step="any"
                                            inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input cp-border-3px-solid-red"' : '' !!} value="{{ $pieceInfo['anchoTacon'] }}" step="any" inputmode="decimal"
                                            disabled></td>
                                    <td><input type="number" class="input cp-border-3px-solid-red"' : '' !!} value="{{ $pieceInfo['barrenoLateralHembra'] }}" step="any"
                                            inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input cp-border-3px-solid-red"' : '' !!}
                                            value="{{ $pieceInfo['barrenoLateralMacho'] }}" step="any" inputmode="decimal" disabled>
                                    </td>
                                    <td><input type="number" class="input cp-border-3px-solid-red"' : '' !!}
                                            value="{{ (isset($pieceInfo['alturaTaconInicial']) && $pieceInfo['alturaTaconInicial'] !== '') ? number_format($pieceInfo['alturaTaconInicial'], 3) : '' }}"
                                            step="any" inputmode="decimal" disabled></td>
                                    <td><input type="number" class="input cp-border-3px-solid-red"' : '' !!}
                                            value="{{ (isset($pieceInfo['alturaTaconIntermedia']) && $pieceInfo['alturaTaconIntermedia'] !== '') ? number_format($pieceInfo['alturaTaconIntermedia'], 3) : '' }}"
                                            step="any" inputmode="decimal" disabled></td>
                                    <td><input type="text" class="input" value="{{ $pieceInfo['error'] }}" disabled></td>
                                    <td><input type="text" class="input" value="{{ $pieceInfo['observaciones'] }}" disabled></td>
                                </tr>
                            @endforeach
                    </table>
                @elseif ($process == 'Soldadura PTA')
                    {{--
                    Tabla especial de Soldadura PTA con estructura de 3 sub-filas por pieza.
                    El partial recibe $piezasGroup ya agrupado por n_pieza desde el controlador.
                    Se usa en modo 'reporte' (sin inputs, solo lectura).
                    --}}
                    @include('processes_views.welding_pta_table_partial', [
                        'piezas' => $piecesInfo,
                        'piezasGroup' => $piezasGroup ?? collect(),
                        'modo' => 'reporte',
                    ])
                @else
                        <script>
                            document.getElementById('scrollabe-table').appendChild(process.crearProceso()); //Agregar tabla al div.
                        </script>
                    @endif
                            </div>
                        </div>
                    </body>
@endsection
