export class Process {
    constructor(
        nameProcess,
        subprocess,
        cNomiData,
        toleData,
        piecesData = [],
        pieceToBeUsed = null,
        tablePieces = false,
        edit = false
    ) {
        this.nameProcess = nameProcess;
        this.subprocess = subprocess;
        this.cNomiData = cNomiData;
        this.toleData = toleData;
        this.tableTitles = [];
        this.piecesData = piecesData;
        this.pieceToBeUsed = pieceToBeUsed != "NoPreviousPieces" ? pieceToBeUsed : null;
        this.tablePieces = tablePieces;
        this.edit = edit;
    }

    getValues(fields, divisionCNomi, divisionsTole) {
        let keyNames = [];
        let values = this.cNomiData ? [] : null;

        for (let i = 0; i < 2; i++) {
            let rowName = i === 0 ? "cNomi" : "tole";
            let division = i === 0 ? divisionCNomi : divisionsTole;
            let firstField = i === 0 ? "C.nominal" : "Tolerancias";
            let arrayValues = i === 0 ? this.cNomiData : this.toleData;

            let counter = 0;
            fields.forEach((field) => {
                keyNames[i] = keyNames[i] || [];
                if (values) {
                    values[i] = values[i] || [];
                }
                if (field !== "id") {
                    if (division.includes(counter)) {
                        switch (field) {
                            case "entradaSalida":
                                keyNames[i].push(`${rowName}_entrada`);
                                keyNames[i].push(`${rowName}_salida`);

                                if (this.cNomiData && this.toleData) {
                                    values[i].push(arrayValues["entrada"]);
                                    values[i].push(arrayValues["salida"]);
                                }
                                break;
                            default:
                                switch (this.nameProcess) {
                                    case "Cavidades":
                                        let lastChar = field.slice(-1);
                                        field = field.slice(0, -1);

                                        keyNames[i].push(`${rowName}_${field}1_${lastChar}`);
                                        keyNames[i].push(`${rowName}_${field}2_${lastChar}`);

                                        if (this.cNomiData && this.toleData) {
                                            values[i].push(arrayValues[`${field}1_${lastChar}`]);
                                            values[i].push(arrayValues[`${field}2_${lastChar}`]);
                                        }
                                        break;
                                    case "OffSet":
                                        break;
                                    default:
                                        keyNames[i].push(`${rowName}_${field}1`);
                                        keyNames[i].push(`${rowName}_${field}2`);

                                        if (this.cNomiData && this.toleData) {
                                            values[i].push(arrayValues[field + "1"]);
                                            values[i].push(arrayValues[field + "2"]);
                                        }
                                        break;
                                }
                        }
                        counter++;
                    } else {
                        if (this.cNomiData && this.toleData) {
                            values[i].push(arrayValues[field]);
                        }
                        keyNames[i].push(`${rowName}_${field}`);
                    }
                } else {
                    keyNames[i].push(firstField);
                    if (this.cNomiData && this.toleData) {
                        values[i].push(arrayValues[field]);
                    }
                }
                counter++;
            });
        }
        return [keyNames, values];
    }
    //prettier-ignore
    createProcess() {
        let divisionsTitles = [];
        let divisionsCNomi = [];
        let divisionsTole = [];
        let positionSelects = [];
        let fields = [];
        let values = [];
        // console.log(this.nameProcess);
        switch (this.nameProcess) {
            case "Cepillado":
                this.tableTitles = !this.tablePieces ?
                    ["", "Radio final de mordaza", "Radio final mayor", "Radio final de sufridera", "Profundidad final conexión Fondo/Corona", "Profundidad final mitad de Molde/Bombillo", "Profundidad final Pico/Conexión de obturador", "Ensamble", "Distancia de barreno de alineación", "Profundidad de barreno de alineación Hembra", "Profundidad de barreno de alineación Macho", "Altura de vena Hembra", "Altura de vena Macho", "Ancho de vena", "Laterales", "PIN"]
                    : ["", "Radio final de mordaza", "Radio final mayor", "Radio final de sufridera", "Profundidad final conexión Fondo/Corona", "Profundidad final mitad de Molde/Bombillo", "Profundidad final Pico/Conexión de obturador", "Acetato B/M", "Ensamble", "Distancia de barreno de alineación", "Profundidad de barreno de alineación Hembra", "Profundidad de barreno de alineación Macho", "Altura de vena Hembra", "Altura de vena Macho", "Ancho de vena", "Laterales", "PIN"];

                // Divisiones de la tabla (Comienza a contar desde 0)
                divisionsCNomi = !this.tablePieces ? [15] : [16];
                divisionsTole = !this.tablePieces ? [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29]
                    : [1, 3, 5, 7, 9, 11, 14, 16, 18, 20, 22, 24, 26, 28, 30];

                positionSelects = [
                    [7, 17],
                    [["Bien", "Mal"], ["Ninguno", "Fundicion"]]
                ];

                fields = !this.tablePieces ? ["id", "radiof_mordaza", "radiof_mayor", "radiof_sufridera", "profuFinal_CFC", "profuFinal_mitadMB", "profuFinal_PCO", "ensamble", "distancia_barrenoAli", "profu_barrenoAliHembra", "profu_barrenoAliMacho", "altura_venaHembra", "altura_venaMacho", "ancho_vena", "laterales", "pin"]
                    : ["id", "radiof_mordaza", "radiof_mayor", "radiof_sufridera", "profuFinal_CFC", "profuFinal_mitadMB", "profuFinal_PCO", "acetato_MB", "ensamble", "distancia_barrenoAli", "profu_barrenoAliHembra", "profu_barrenoAliMacho", "altura_venaHembra", "altura_venaMacho", "ancho_vena", "laterales", "pin"];
                break;

            case "Desbaste Exterior":
                this.tableTitles = ["", "Diametro de mordaza", "Diametro de ceja", "Diametro de sufridera/Extra", "Simetría ceja", "Simetría Mordaza", "Altura de ceja", "Altura sufridera"];

                divisionsCNomi = [null];
                divisionsTole = [1, 3, 5, 7, 9, 11, 13];

                positionSelects = [
                    [8],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "diametro_mordaza", "diametro_ceja", "diametro_sufrideraExtra", "simetria_ceja", "simetria_mordaza", "altura_ceja", "altura_sufridera"];
                break;

            case "Revision Laterales":
                this.tableTitles = ["", "Desfasamiento Entrada", "Desfasamiento Salida", "Ancho de simetria Entrada", "Ancho de simetria Salida", "Angulo de corte"];

                divisionsCNomi = [null];
                divisionsTole = [1, 3, 5, 7, 9];

                positionSelects = [
                    [6],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "desfasamiento_entrada", "desfasamiento_salida", "ancho_simetriaEntrada", "ancho_simetriaSalida", "angulo_corte"];
                break;

            case "Primera Operacion": //Proceso de primera operacion
                this.tableTitles = ["", "Diametro 1", "Profundidad 1 ", "Diametro 2", "Profundidad 2", "Diametro 3", "Profunfidad 3", "Diametro de soldadura", "Profundidad de soldadura", "Diametro de barreno", "Simetria línea de partida", "Perno de alineación", "Simetría a 90°"];

                divisionsCNomi = [null];
                divisionsTole = [9, 11];

                positionSelects = [
                    [13],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "diametro1", "profundidad1", "diametro2", "profundidad2", "diametro3", "profundidad3", "diametroSoldadura", "profundidadSoldadura", "diametroBarreno", "simetriaLinea_partida", "pernoAlineacion", "Simetria90G"];
                break;

            case "Barreno Maniobra": //Proceso de barreno maniobra
                this.tableTitles = !this.tablePieces ? ["", "Profundidad de Barreno", "Diametro de machuelo"] : ["", "Profundidad de Barreno", "Diametro de machuelo", "Acetato B/M"];

                divisionsCNomi = [null];
                divisionsTole = [1, 3];

                positionSelects = [
                    [3, 4],
                    [["Bien", "Mal"], ["Ninguno", "Fundicion"]]
                ];

                fields = !this.tablePieces ? ["id", "profundidad_barreno", "diametro_machuelo"] : ["id", "profundidad_barreno", "diametro_machuelo", "acetatoBM"];
                break;

            case "Segunda Operacion": //Proceso de segunda operacion
                this.tableTitles = ["", "Diametro 1", "Profundidad 1 ", "Diametro 2", "Profundidad 2", "Diametro 3", "Profunfidad 3", "Diametro de soldadura", "Profundidad de soldadura", "Altura total", "Simetría a 90°", "Simetria línea de partida"];

                divisionsCNomi = [null];
                divisionsTole = [9, 11];

                positionSelects = [
                    [12],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "diametro1", "profundidad1", "diametro2", "profundidad2", "diametro3", "profundidad3", "diametroSoldadura", "profundidadSoldadura", "alturaTotal", "simetria90G", "simetriaLinea_Partida"];
                break;

            case "Soldadura PTA": //Proceso de soldadura PTA
                this.tableTitles = ["Pieza", "Temperatura de precalentado", "Temperatura en dispositivo", "Limpieza"];

                divisionsCNomi = [null];
                divisionsTole = [null];

                positionSelects = [
                    [3, 4],
                    [["Si", "No"], ["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "temp_calentado", "temp_dispositivo", "limpieza"];
                break;
            case "Soldadura": //Proceso de soldadura
                this.tableTitles = ["Pieza", "Peso por pieza", "Temperatura de precalentado °", "Tiempo de aplicacion", "Tipo de soldadura", "Lote"];

                divisionsCNomi = [null];
                divisionsTole = [null];

                positionSelects = [
                    [6],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "pesoxpieza", "temperatura_precalentado", "tiempo_aplicacion", "tipo_soldadura", "lote"];
                break;
            case "Rectificado": //Proceso de rectificado
                this.tableTitles = ["Pieza", "Cumple"];

                divisionsCNomi = [null];
                divisionsTole = [null];

                positionSelects = [
                    [1, 2],
                    [["Si", "No"], ["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "cumple"];
                break;
            case "Asentado": //Proceso de asentado
                this.tableTitles = ["Pieza", "Sin juego", "Sin luz"];

                divisionsCNomi = [null];
                divisionsTole = [null];

                positionSelects = [
                    [1, 2, 3],
                    [["✔", "X"], ["✔", "X"], ["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "sin_juego", "sin_luz"];
                break;
            case "Calificado":
                this.tableTitles = ["", "Diametro de ceja", "Diametro de sufridera", "Altura de sufridera", "Diametro de conexion", "Altura de conexion", "Diametro de caja", "Altura de caja", "Altura total", "Simetria"];

                divisionsCNomi = [null];
                divisionsTole = [1, 3, 5, 7, 9, 11, 13, 15, 17];

                positionSelects = [
                    [10],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "diametro_ceja", "diametro_sufridera", "altura_sufridera", "diametro_conexion", "altura_conexion", "diametro_caja", "altura_caja", "altura_total", "simetria"];
                break;
            case "Acabado Bombillo":
                this.tableTitles = !this.tablePieces ? ["", "Diametro de mordaza", "Diametro de ceja", "Diametro de sufridera", "Altura de mordaza", "Altura de ceja", "Altura de sufridera", "Diametro Boca", "Diametro Asiento Corona", "Diametro llanta", "Diametro caja corona", "Profundidad corona", "Angulo de 30", "Profundidad caja corona", "Simetria"] : ["", "Diametro de mordaza", "Diametro de ceja", "Diametro de sufridera", "Altura de mordaza", "Altura de ceja", "Altura de sufridera", "Gauge ceja", "Gauge corona", "Gauge llanta", "Altura total", "Diametro Boca", "Diametro Asiento Corona", "Diametro llanta", "Diametro caja corona", "Profundidad corona", "Angulo de 30", "Profundidad caja corona", "Simetria"];

                divisionsCNomi = [null];
                divisionsTole = !this.tablePieces ? [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27] : [1, 3, 5, 7, 9, 11, 17, 19, 21, 23, 25, 27, 29, 31];

                positionSelects = [
                    [7, 9, 19],
                    [["Si", "No"], ["Si", "No"], ["Ninguno", "Fundicion"]]
                ];

                fields = !this.tablePieces ? ["id", "diametro_mordaza", "diametro_ceja", "diametro_sufridera", "altura_mordaza", "altura_ceja", "altura_sufridera", "diametro_boca", "diametro_asiento_corona", "diametro_llanta", "diametro_caja_corona", "profundidad_corona", "angulo_30", "profundidad_caja_corona", "simetria"] : ["id", "diametro_mordaza", "diametro_ceja", "diametro_sufridera", "altura_mordaza", "altura_ceja", "altura_sufridera", "gauge_ceja", "gauge_corona", "gauge_llanta", "altura_total", "diametro_boca", "diametro_asiento_corona", "diametro_llanta", "diametro_caja_corona", "profundidad_corona", "angulo_30", "profundidad_caja_corona", "simetria"];
                break;

            case "Acabado Molde":
                this.tableTitles = !this.tablePieces ? ["", "Diametro de mordaza", "Diametro de ceja", "Diametro de sufridera", "Altura de mordaza", "Altura de ceja", "Altura de sufridera", "Diametro Conexion Fondo", "Diametro llanta", "Diametro Caja Fondo", "Altura Conexion Fondo", "Profundidad Llanta", "Profundidad Caja Fondo", "Simetria"] : ["", "Diametro de mordaza", "Diametro de ceja", "Diametro de sufridera", "Altura de mordaza", "Altura de ceja", "Altura de sufridera", "Gauje ceja", "Altura total", "Diametro Conexion Fondo", "Diametro llanta", "Diametro Caja Fondo", "Altura Conexion Fondo", "Profundidad Llanta", "Profundidad Caja Fondo", "Simetria"];

                divisionsCNomi = [null];
                divisionsTole = !this.tablePieces ? [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25] : [1, 3, 5, 7, 9, 11, 15, 17, 19, 21, 23, 25, 27];
                positionSelects = [
                    [7, 16],
                    [["Si", "No"], ["Ninguno", "Fundicion"]]
                ];

                fields = !this.tablePieces ? ["id", "diametro_mordaza", "diametro_ceja", "diametro_sufridera", "altura_mordaza", "altura_ceja", "altura_sufridera", "diametro_conexion_fondo", "diametro_llanta", "diametro_caja_fondo", "altura_conexion_fondo", "profundidad_llanta", "profundidad_caja_fondo", "simetria"] : ["id", "diametro_mordaza", "diametro_ceja", "diametro_sufridera", "altura_mordaza", "altura_ceja", "altura_sufridera", "gauge_ceja", "altura_total", "diametro_conexion_fondo", "diametro_llanta", "diametro_caja_fondo", "altura_conexion_fondo", "profundidad_llanta", "profundidad_caja_fondo", "simetria"];
                break;
            case "Barreno Profundidad":
                this.tableTitles = ["", "Broca 1", "Tiempo 1", "Broca 2", "Tiempo2", "Broca3", "Tiempo3", "Entrada / Salida", "Diametro de arrastre 1", "Diametro de arrastre 2", "Diametro de arrastre 3"];

                divisionsCNomi = [null];
                divisionsTole = [7];

                positionSelects = [
                    [11],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "broca1", "tiempo1", "broca2", "tiempo2", "broca3", "tiempo3", "entradaSalida", "diametro_arrastre1", "diametro_arrastre2", "diametro_arrastre3"];
                break;
            case "Cavidades":
                this.tableTitles = !this.tablePieces ? [["#PZ", "Altura 1", "Altura 2", "Altura 3"], ["", "Profundidad", "Diametro", "Profundidad", "Diametro", "Profundidad", "Diametro"]] : [["#PZ", "Altura 1", "Altura 2", "Altura 3", ""], ["", "Profundidad", "Diametro", "Profundidad", "Diametro", "Profundidad", "Diametro", "Acetato B/M"]];

                divisionsTitles = [1, 2, 3];
                divisionsCNomi = [null];
                divisionsTole = [1, 3, 5, 7, 9, 11];

                positionSelects = [
                    [7, 8],
                    [["Bien", "Mal"], ["Ninguno", "Fundicion"]]
                ];

                fields = !this.tablePieces ? ["id", "profundidad1", "diametro1", "profundidad2", "diametro2", "profundidad3", "diametro3"] : ["id", "profundidad1", "diametro1", "profundidad2", "diametro2", "profundidad3", "diametro3", "acetatoBM"];
                break;

            case "Copiado":
                if (this.subprocess == "Cilindrado") {
                    this.tableTitles = ["", "Diametro 1", "Profundidad 1", "Diametro 2", "Profundidad 2", "Diametro de sufridera", "Diametro Ranura", "Profundidad Ranura", "Profundidad de sufridera", "ALTURA TOTAL"];

                    divisionsCNomi = [null];
                    divisionsTole = [null];

                    positionSelects = [
                        [10],
                        [["Ninguno", "Fundicion"]]
                    ];

                    fields = ["id", "diametro1_cilindrado", "profundidad1_cilindrado", "diametro2_cilindrado", "profundidad2_cilindrado", "diametro_sufridera", "diametro_ranura", "profundidad_ranura", "profundidad_sufridera", "altura_total"];
                } else {
                    this.tableTitles = ["", "Diametro 1", "Profundidad 1", "Diametro 2", "Profundidad 2", "Diametro 3", "Profundidad 3", "Diametro 4", "Profundidad 4", " VOLUMEN"];

                    divisionsCNomi = [null];
                    divisionsTole = [null];

                    positionSelects = [
                        [10],
                        [["Ninguno", "Fundicion"]]
                    ];

                    fields = ["id", "diametro1_cavidades", "profundidad1_cavidades", "diametro2_cavidades", "profundidad2_cavidades", "diametro3", "profundidad3", "diametro4", "profundidad4", "volumen"];
                }
                break;
            case "Off Set":
                this.tableTitles = [["#PZ", "Ancho de altura", "Profundidad de tacon", "Simetria", "Ancho del tacon", "Barreno lateral", "Altura tacon inicial", "Altura tacon intermedia"], ["", "", "Hembra", "Macho", "Hembra", "Macho", "", "Hembra", "Macho", "", ""]];

                divisionsTitles = [2, 3, 5];
                divisionsCNomi = [null];
                divisionsTole = [null];

                positionSelects = [
                    [11],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "anchoRanura", "profuTaconHembra", "profuTaconMacho", "simetriaHembra", "simetriaMacho", "anchoTacon", "barrenoLateralHembra", "barrenoLateralMacho", "alturaTaconInicial", "alturaTaconIntermedia"];
                break;
            case "Palomas": //Proceso de palomas
                this.tableTitles = ["", "Ancho de Paloma", "Grueso de Paloma", "Profundidad de Paloma", "Rebaje de llanta"];

                divisionsCNomi = [null];
                divisionsTole = [null];

                positionSelects = [
                    [5],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "anchoPaloma", "gruesoPaloma", "profundidadPaloma", "rebajeLlanta"];
                break;

            case "Rebajes": //Proceso de Rebajes
                this.tableTitles = ["", "Rebaje 1", "Rebaje 2", "Rebaje 3", "Profundidad de Bordonio", "Vena 1", "Vena 2", "Simetria"];

                divisionsCNomi = [null];
                divisionsTole = [null];

                positionSelects = [
                    [8],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "rebaje1", "rebaje2", "rebaje3", "profundidad_bordonio", "vena1", "vena2", "simetria"];
                break;

            // Procesos no comunes
            case "Embudo CM":
                this.tableTitles = ["", "Conexion de linea de partida", "Conexión 90G", "Altura de conexión", "Diametro de embudo"];

                divisionsCNomi = [null];
                divisionsTole = [null];

                positionSelects = [
                    [5],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "conexion_lineaPartida", "conexion_90G", "altura_conexion", "diametro_embudo"];
                break;
            case "Operacion Equipo":
                this.tableTitles = ["", "Altura", "ø Altura de candado", "Altura asiento obturador", "ø Profundidad de soldadura", "ø de PushUp"];

                divisionsCNomi = [2, 4, 6];
                divisionsTole = [2, 4, 6];

                positionSelects = [
                    [6],
                    [["Ninguno", "Fundicion"]]
                ];

                fields = ["id", "altura", "alturaCandado", "alturaAsientoObturador", "profundidadSoldadura", "pushUp"];
                break;
        }

        if (this.tablePieces) { // Agregar campos de error y observaciones si la tabla es la de piezas
            if (this.nameProcess == "Cavidades" || this.nameProcess == "Off Set") {
                this.tableTitles[0].push("", "");
                this.tableTitles[1].push("Error", "Observaciones");
            } else {
                this.tableTitles.push("Error", "Observaciones");
            }

            if (this.nameProcess == "Copiado") {
                if (this.subprocess == "Cilindrado") {
                    fields = fields !== null ? [...fields, "error_cilindrado", "observaciones_cilindrado"] : null;
                } else {
                    fields = fields !== null ? [...fields, "error_cavidades", "observaciones_cavidades"] : null;
                }
            } else {
                fields = fields !== null ? [...fields, "error", "observaciones"] : null;
            }
        }

        values = fields !== null ? this.getValues(fields, divisionsCNomi, divisionsTole) : null;
        if (values != null) {
            return this.crearTabla(values[0], divisionsCNomi, divisionsTole, values[1], divisionsTitles, fields, positionSelects);
        }
        return this.crearTabla(null, divisionsCNomi, divisionsTole, null, divisionsTitles);
    }
    // prettier-ignore
    crearTabla(names, divisionsCNomi, divisionsTole, values, divisionsTitles = [], fields = [], positionSelects = []) {
        // Crear tabla
        const table = document.createElement("table"); // Crear tabla
        table.className = this.nameProcess != "Copiado" ? "table" : `table ${this.subprocess}`; // Agregar clase a la tabla


        for (let i = 0; i < 5; i++) {
            if (names == null && i > 0) {
                return table;
            }
            let tr;
            switch (i) {
                case 0: // Crear columnas de titulos
                    let titles = this.tableTitles.length > 2 ? [this.tableTitles] : this.tableTitles;
                    titles.forEach((array, indexArray) => {
                        tr = document.createElement("tr");
                        tr.className = "table-row-title";
                        array.forEach((title, index) => {
                            let th = document.createElement("th");
                            th.className = "table-title";
                            th.innerHTML = title;
                            if (index == 0) {
                                th.style = "width:300px;";
                            } else if (title == "Observaciones") {
                                th.style = "width:500px;";
                            }

                            if (this.nameProcess == "Cavidades" && title.includes("Altura")) {
                                const indexAltura = title.split(" ")[1];

                                if (!this.tablePieces) {
                                    let input = document.createElement("input");
                                    input.type = "number";
                                    input.step = "any";
                                    input.name = `cNomi_altura${indexAltura}`;
                                    input.placeholder = "Ref";

                                    // Estilos Modernos
                                    Object.assign(input.style, {
                                        width: "85px",
                                        marginLeft: "10px",
                                        textAlign: "center",
                                        border: "1px solid #dee2e6",
                                        borderRadius: "6px",
                                        padding: "4px 8px",
                                        fontSize: "0.85rem",
                                        color: "#495057",
                                        backgroundColor: "#f8f9fa",
                                        transition: "all 0.2s ease-in-out",
                                        outline: "none",
                                        boxShadow: "inset 0 1px 2px rgba(0,0,0,0.075)"
                                    });

                                    // Eventos de Interacción
                                    input.onfocus = () => {
                                        input.style.borderColor = "#80bdff";
                                        input.style.backgroundColor = "#fff";
                                        input.style.boxShadow = "0 0 0 0.2rem rgba(0,123,255,.25)";
                                    };
                                    input.onblur = () => {
                                        input.style.borderColor = "#dee2e6";
                                        input.style.backgroundColor = "#f8f9fa";
                                        input.style.boxShadow = "inset 0 1px 2px rgba(0,0,0,0.075)";
                                    };

                                    // Persistencia de datos
                                    input.addEventListener('input', (e) => {
                                        if (!this.cNomiData) this.cNomiData = {};
                                        this.cNomiData[`altura${indexAltura}`] = e.target.value;
                                    });

                                    if (this.cNomiData && this.cNomiData[`altura${indexAltura}`]) {
                                        input.value = this.cNomiData[`altura${indexAltura}`];
                                    }
                                    th.appendChild(input);

                                } else {
                                    // Modo lectura: Badge estilizado
                                    if (this.cNomiData && this.cNomiData[`altura${indexAltura}`]) {
                                        const val = this.cNomiData[`altura${indexAltura}`];
                                        th.innerHTML += `
                                        <div style="margin-top: 6px;">
                                            <span style="
                                                display: inline-flex;
                                                align-items: center;
                                                padding: 2px 12px;
                                                background: #f1f3f5;
                                                border-radius: 50px;
                                                font-size: 11px;
                                                color: #495057;
                                                border: 1px solid #dee2e6;
                                                font-weight: 500;
                                            ">
                                                <strong style="color: #007bff; margin-right: 5px;">REF</strong> ${val} mm
                                            </span>
                                        </div>`;
                                    }
                                }
                            }

                            if (indexArray == 0 && divisionsTitles.length > 0) {
                                if (titles.length > 1) {
                                    if (divisionsTitles.includes(index)) {
                                        th.colSpan = 2;
                                    }
                                }
                            }
                            tr.appendChild(th);
                        });
                        table.appendChild(tr); //Agregar fila a la tabla.
                    });
                    break;

                // Crear columnas de cNominal y tolerancias
                case 1:
                case 2:
                    if (this.nameProcess != "Soldadura" && this.nameProcess != "Asentado" && this.nameProcess != "Rectificado" && this.nameProcess != "Soldadura PTA") {
                        tr = document.createElement("tr");
                        tr.className = "table-row-cNominals";
                        let divisions = i == 1 ? divisionsCNomi : divisionsTole;

                        for (let x = 0; x < names[i - 1].length; x++) {
                            const td = document.createElement("td");
                            if (x != 0) {
                                if (divisions.includes(x)) {
                                    for (let j = 0; j < 2; j++) {
                                        let sign = j == 0 ? "+" : "-";
                                        if (values) {
                                            if (i == 2) {
                                                if (names[i - 1][x].includes("pin")) {
                                                    if (!this.tablePieces) {
                                                        td.appendChild(this.crearInputs("input-medio", names[i - 1][x], `${values[i - 1][x]}`));
                                                    } else {
                                                        td.appendChild(this.crearInputs("input-medio", names[i - 1][x], `+-${values[i - 1][x]}`));
                                                    }
                                                } else {
                                                    if (!this.tablePieces) {
                                                        td.appendChild(this.crearInputs("input-medio", names[i - 1][x], `${values[i - 1][x]}`));
                                                    } else {
                                                        td.appendChild(this.crearInputs("input-medio", names[i - 1][x], `${sign}${values[i - 1][x]}`));
                                                    }
                                                }
                                            } else {
                                                td.appendChild(this.crearInputs("input-medio", names[i - 1][x], values[i - 1][x]));
                                            }
                                        } else {
                                            td.appendChild(this.crearInputs("input-medio", names[i - 1][x], null));
                                        }
                                        if (j != 1) {
                                            x++;
                                        }
                                    }
                                } else {
                                    if (values) {
                                        if (i == 2 && values[i - 1][x] != null) {
                                            if (!this.tablePieces) {
                                                td.appendChild(this.crearInputs("input", names[i - 1][x], `${values[i - 1][x]}`));
                                            } else {
                                                td.appendChild(this.crearInputs("input", names[i - 1][x], `+-${values[i - 1][x]}`));
                                            }
                                        } else {
                                            td.appendChild(this.crearInputs("input", names[i - 1][x], values[i - 1][x]));
                                        }
                                    } else {
                                        td.appendChild(this.crearInputs("input", names[i - 1][x], null));
                                    }
                                }
                            } else {
                                td.innerHTML = names[i - 1][x];
                            }
                            tr.appendChild(td);
                        }
                        table.appendChild(tr); //Agregar fila a la tabla.
                    }
                    break;
                case 3://Crear inputs de las piezas e input de la pieza a utilizar si es que existe
                    if (this.piecesData.length > 0) { // Crear inputs inhabilitados de las piezas maquinadas en la meta
                        let divisions = this.nameProcess == "Operacion Equipo" ? [2, 3, 4] : divisionsCNomi;
                        this.piecesData.forEach((piece, index) => { // Recorrer cada una de las piezas
                            let tr = document.createElement("tr");
                            for (let i = 0; i < fields.length; i++) { // Recorrer las medidas de la pieza
                                const td = document.createElement("td");
                                if (fields[i] != "id") {
                                    if (divisions.includes(i)) {
                                        for (let j = 0; j < 2; j++) {
                                            td.appendChild(this.crearInputs("input-medio", fields[i] + (j + 1), piece.piece[fields[i] + (j + 1)], this.dataType(piece.piece[fields[i] + (j + 1)])));
                                            if (!this.edit) {
                                                td.style.backgroundColor = piece.color;
                                            }
                                        }
                                    } else {
                                        if (this.edit) {
                                            if (positionSelects[0].includes(i)) {
                                                td.appendChild(
                                                    this.createSelects(
                                                        "select input-pieceUsed",
                                                        fields[i],
                                                        positionSelects[1][positionSelects[0].indexOf(i)],
                                                        piece.piece[fields[i]]
                                                    )
                                                );
                                            } else if (fields[i].includes("observaciones")) {
                                                let textarea = document.createElement("textarea");
                                                textarea.className = "textarea input-pieceUsed";
                                                textarea.name = `${fields[i]}[]`;
                                                textarea.value = piece.piece[fields[i]];
                                                td.appendChild(textarea);
                                            } else {
                                                if (fields[i] == "entradaSalida" && this.nameProcess == "Barreno Profundidad") {
                                                    td.appendChild(this.crearInputs("input-medio", "entrada", piece.piece["entrada"], piece.piece["entrada"]));
                                                    td.appendChild(this.crearInputs("input-medio", "salida", piece.piece["salida"], piece.piece["salida"]));
                                                } else {
                                                    if (fields[i] == "tipo_soldadura" || fields[i] == "lote") {
                                                        td.appendChild(this.crearInputs("input", fields[i], piece.piece[fields[i]], this.dataType(piece.piece[fields[i]])));
                                                    } else {
                                                        td.appendChild(this.crearInputs("input", fields[i], piece.piece[fields[i]], this.dataType(piece.piece[fields[i]])));
                                                    }
                                                }
                                            }
                                        } else {
                                            if (fields[i] == "entradaSalida" && this.nameProcess == "Barreno Profundidad") {
                                                td.appendChild(this.crearInputs("input-medio", "entrada", piece.piece["entrada"], this.dataType(piece.piece["entrada"])));
                                                td.appendChild(this.crearInputs("input-medio", "salida", piece.piece["salida"], this.dataType(piece.piece["salida"])));
                                            } else {
                                                td.appendChild(this.crearInputs("input", fields[i], piece.piece[fields[i]], this.dataType(piece.piece[fields[i]])));
                                            }
                                            td.style.backgroundColor = piece.color;
                                        }
                                    }
                                    tr.appendChild(td);
                                } else {
                                    let noPiece = piece.piece.n_pieza ? piece.piece.n_pieza.slice(0, - 1) : piece.piece.n_juego.slice(0, -1);
                                    let letterPiece = piece.piece.n_pieza ? piece.piece.n_pieza[piece.piece.n_pieza.length - 1] : piece.piece.n_juego[piece.piece.n_juego.length - 1];
                                    td.innerHTML = {
                                        "H": noPiece + " HEMBRA",
                                        "M": noPiece + " MACHO",
                                    }[letterPiece] || noPiece + " JUEGO";
                                    if (!this.edit) {
                                        td.style.backgroundColor = piece.color;
                                    } else {
                                        let inptHiddn_id = document.createElement("input");
                                        inptHiddn_id.type = "hidden";
                                        inptHiddn_id.value = piece.piece["id"];
                                        table.appendChild(inptHiddn_id);
                                        inptHiddn_id.name = "piece[]";
                                    }
                                }
                                tr.appendChild(td);
                            }
                            table.appendChild(tr);
                        });
                    }
                    break;
                case 4:
                    if (this.pieceToBeUsed) { // Crear input de la pieza a utilizar
                        // console.log("Crear input de la pieza a utilizar: " + this.pieceToBeUsed.n_pieza);
                        // console.log("Crear input del juego a utilizar: " + this.pieceToBeUsed.n_juego);
                        if (!this.edit) {
                            this.createPieceToBeUsed(tr, fields, divisionsCNomi, positionSelects, table);
                        }
                    }
                    break;
            }
        }
        return table;
    }
    createPieceToBeUsed(tr, fields, divisionsCNomi, positionSelects, table) {
        let divisions = this.nameProcess == "Operacion Equipo" ? [2, 3, 4] : divisionsCNomi;
        tr = document.createElement("tr");
        for (let x = 0; x < fields.length; x++) {
            const td = document.createElement("td");
            if (x != 0) {
                if (divisions.includes(x)) {

                    //Crear los dos inputs e insertarlos en el mismo td
                    for (let j = 0; j < 2; j++) {
                        td.appendChild(
                            this.crearInputs("input-medio input-pieceUsed", fields[x] + (j + 1), null, "number")
                        );
                    }
                } else {
                    if (positionSelects[0].includes(x)) {
                        td.appendChild(
                            this.createSelects(
                                "select input-pieceUsed",
                                fields[x],
                                positionSelects[1][positionSelects[0].indexOf(x)]
                            )
                        );
                    } else if (fields[x].includes("observaciones")) {
                        let textarea = document.createElement("textarea");
                        textarea.className = "textarea input-pieceUsed";
                        textarea.name = fields[x];
                        td.appendChild(textarea);
                    } else {
                        if (fields[x] == "entradaSalida" && this.nameProcess == "Barreno Profundidad") {
                            td.appendChild(this.crearInputs("input-medio input-pieceUsed", "entrada", null, "number"));
                            td.appendChild(this.crearInputs("input-medio input-pieceUsed", "salida", null, "number"));
                        } else {
                            if (fields[x] == "tipo_soldadura" || fields[x] == "lote") {
                                td.appendChild(this.crearInputs("input input-pieceUsed", fields[x], null));
                            } else {
                                td.appendChild(this.crearInputs("input input-pieceUsed", fields[x], null, "number"));
                            }
                        }
                    }
                }
            } else {
                if (this.pieceToBeUsed.n_pieza) {
                    let noPiece = this.pieceToBeUsed.n_pieza.slice(0, -1);
                    let letterPiece = this.pieceToBeUsed.n_pieza[this.pieceToBeUsed.n_pieza.length - 1];
                    if (letterPiece == "H") {
                        td.innerHTML = noPiece + " HEMBRA";
                    } else if (letterPiece == "M") {
                        td.innerHTML = noPiece + " MACHO";
                    } else {
                        td.innerHTML = noPiece + " JUEGO";
                    }
                } else {
                    let noPiece = this.pieceToBeUsed.n_juego.slice(0, -1);
                    td.innerHTML = noPiece + " JUEGO";
                }
            }
            tr.appendChild(td);
        }
        table.appendChild(tr);
    }
    crearInputs(className, name, valueInput, type = "text") {
        let input = document.createElement("input");
        input.className = className;
        input.type = type;
        if (type == "text") {
            input.maxLength = 25;
        }
        input.name = this.edit ? `${name}[]` : name;
        input.step = "any";
        input.inputMode = "decimal";
        input.required = "true";
        input.value = valueInput && valueInput != "null" ? valueInput : "";
        return input;
    }
    createSelects(className, name, options, value = null) {
        let select = document.createElement("select");
        select.className = className;
        select.name = this.edit ? `${name}[]` : name;
        options.forEach((option) => {
            let opt = document.createElement("option");
            opt.value = option;
            opt.text = option;
            select.appendChild(opt);
        });
        if (value) {
            let key = options.indexOf(value);
            if (key > -1) {
                select.selectedIndex = key;
            }
        }
        return select;
    }
    dataType(value) {
        return value != null && !isNaN(value) && String(value).trim() !== "" ? "number" : "text";
    }
}
