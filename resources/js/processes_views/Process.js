export class Process {
    constructor(nameProcess, subprocess, cNomiData, toleData) {
        this.nameProcess = nameProcess;
        this.subprocess = subprocess;
        this.cNomiData = cNomiData;
        this.toleData = toleData;
        this.tableTitles = [];
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

                                        keyNames[i].push(
                                            `${rowName}_${field}1_${lastChar}`
                                        );
                                        keyNames[i].push(
                                            `${rowName}_${field}2_${lastChar}`
                                        );

                                        if (this.cNomiData && this.toleData) {
                                            values[i].push(
                                                arrayValues[
                                                    `${field}1_${lastChar}`
                                                ]
                                            );
                                            values[i].push(
                                                arrayValues[
                                                    `${field}2_${lastChar}`
                                                ]
                                            );
                                        }
                                        break;
                                    case "OffSet":
                                        break;
                                    default:
                                        keyNames[i].push(
                                            `${rowName}_${field}1`
                                        );
                                        keyNames[i].push(
                                            `${rowName}_${field}2`
                                        );

                                        if (this.cNomiData && this.toleData) {
                                            values[i].push(
                                                arrayValues[field + "1"]
                                            );
                                            values[i].push(
                                                arrayValues[field + "2"]
                                            );
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
        let fields = [];
        let values = [];
        console.log(this.nameProcess);
        switch (this.nameProcess) {
            case "Cepillado":
                this.tableTitles = ["", "Radio final de mordaza", "Radio final mayor", "Radio final de sufridera", "Profundidad final conexión Fondo/Corona", "Profundidad final mitad de Molde/Bombillo", "Profundidad final Pico/Conexión de obturador", "Ensamble", "Distancia de barreno de alineación", "Profundidad de barreno de alineación Hembra", "Profundidad de barreno de alineación Macho", "Altura de vena Hembra", "Altura de vena Macho", "Ancho de vena", "Laterales", "PIN"];

                divisionsCNomi = [15];
                divisionsTole = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29];

                fields = ["id", "radiof_mordaza", "radiof_mayor", "radiof_sufridera", "profuFinal_CFC", "profuFinal_mitadMB", "profuFinal_PCO", "ensamble", "distancia_barrenoAli", "profu_barrenoAliHembra", "profu_barrenoAliMacho", "altura_venaHembra", "altura_venaMacho", "ancho_vena", "laterales", "pin"];
                break;

            case "Desbaste Exterior":
                this.tableTitles = [ "", "Diametro de mordaza", "Diametro de ceja", "Diametro de sufridera/Extra", "Simetría ceja", "Simetría Mordaza", "Altura de ceja", "Altura sufridera"];

                divisionsCNomi = [null];
                divisionsTole = [1, 3, 5, 7, 9, 11, 13];

                fields = ["id", "diametro_mordaza", "diametro_ceja", "diametro_sufrideraExtra", "simetria_ceja", "simetria_mordaza", "altura_ceja", "altura_sufridera"];
                break;

            case "Revision Laterales":
                this.tableTitles = ["","Desfasamiento Entrada","Desfasamiento Salida","Ancho de simetria Entrada","Ancho de simetria Salida","Angulo de corte"];
                
                divisionsCNomi = [null];
                divisionsTole = [1, 3, 5, 7, 9];

                fields = [ "id", "desfasamiento_entrada", "desfasamiento_salida", "ancho_simetriaEntrada", "ancho_simetriaSalida", "angulo_corte"];
                break;

            case "Primera Operacion": //Proceso de primera operacion
                this.tableTitles = [ "", "Diametro 1", "Profundidad 1 ", "Diametro 2", "Profundidad 2", "Diametro 3", "Profunfidad 3", "Diametro de soldadura", "Profundidad de soldadura", "Diametro de barreno", "Simetria línea de partida", "Perno de alineación", "Simetría a 90°"];

                divisionsCNomi = [null];
                divisionsTole = [9, 11];

                fields = ["id", "diametro1", "profundidad1", "diametro2", "profundidad2", "diametro3", "profundidad3", "diametroSoldadura", "profundidadSoldadura", "diametroBarreno", "simetriaLinea_partida", "pernoAlineacion", "Simetria90G"];
                break;

            case "Barreno Maniobra": //Proceso de barreno maniobra
                this.tableTitles = [ "", "Profundidad de Barreno", "Diametro de machuelo"];

                divisionsCNomi = [null];
                divisionsTole = [1, 3];

                fields = ["id", "profundidad_barreno", "diametro_machuelo"];
                break;

            case "Segunda Operacion": //Proceso de segunda operacion
                this.tableTitles = [ "", "Diametro 1", "Profundidad 1 ", "Diametro 2", "Profundidad 2", "Diametro 3", "Profunfidad 3", "Diametro de soldadura", "Profundidad de soldadura", "Altura total", "Simetría a 90°", "Simetria línea de partida"];

                divisionsCNomi = [null];
                divisionsTole = [9, 11];

                fields = ["id", "diametro1", "profundidad1", "diametro2", "profundidad2", "diametro3", "profundidad3", "diametroSoldadura", "profundidadSoldadura", "alturaTotal", "simetria90G", "simetriaLinea_Partida"];
                break;

            case "Operacion Equipo":
                this.tableTitles = [ "", "Altura", "ø Altura de candado", "Altura asiento obturador", "ø Profundidad de soldadura", "ø de PushUp"];

                divisionsCNomi = [2, 4, 6];
                divisionsTole = [2, 4, 6];

                fields = ["id", "altura", "alturaCandado", "alturaAsientoObturador", "profundidadSoldadura", "pushUp"];
                break;

            case "Calificado":
                this.tableTitles = [ "", "Diametro de ceja", "Diametro de sufridera", "Altura de sufridera", "Diametro de conexion", "Altura de conexion", "Diametro de caja", "Altura de caja", "Altura total", "Simetria"];

                divisionsCNomi = [null];
                divisionsTole = [1, 3, 5, 7, 9, 11, 13, 15, 17];

                fields = ["id", "diametro_ceja", "diametro_sufridera", "altura_sufridera", "diametro_conexion", "altura_conexion", "diametro_caja", "altura_caja", "altura_total", "simetria"];
                break;

            case "Acabado Bombillo":
                this.tableTitles = [ "", "Diametro de mordaza", "Diametro de ceja", "Diametro de sufridera", "Altura de mordaza", "Altura de ceja", "Altura de sufridera", "Diametro Boca", "Diametro Asiento Corona", "Diametro llanta", "Diametro caja corona", "Profundidad corona", "Angulo de 30", "Profundidad caja corona", "Simetria" ];

                divisionsCNomi = [null];
                divisionsTole = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27];

                fields = ["id", "diametro_mordaza", "diametro_ceja", "diametro_sufridera", "altura_mordaza", "altura_ceja", "altura_sufridera", "diametro_boca", "diametro_asiento_corona", "diametro_llanta", "diametro_caja_corona", "profundidad_corona", "angulo_30", "profundidad_caja_corona", "simetria"];
                break;

            case "Acabado Molde":
                this.tableTitles = [ "", "Diametro de mordaza", "Diametro de ceja", "Diametro de sufridera", "Altura de mordaza", "Altura de ceja", "Altura de sufridera", "Diametro Conexion Fondo", "Diametro llanta", "Diametro Caja Fondo", "Altura Conexion Fondo", "Profundidad Llanta", "Profundidad Caja Fondo", "Simetria"];

                divisionsCNomi = [null];
                divisionsTole = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25];

                fields = ["id", "diametro_mordaza", "diametro_ceja", "diametro_sufridera", "altura_mordaza", "altura_ceja", "altura_sufridera", "diametro_conexion_fondo", "diametro_llanta", "diametro_caja_fondo", "altura_conexion_fondo", "profundidad_llanta", "profundidad_caja_fondo", "simetria"];
                break;

            case "Barreno Profundidad":
                this.tableTitles = [ "", "Broca 1", "Tiempo 1", "Broca 2", "Tiempo2", "Broca3", "Tiempo3", "Entrada / Salida", "Diametro de arrastre 1", "Diametro de arrastre 2", "Diametro de arrastre 3" ];

                divisionsCNomi = [null];
                divisionsTole = [7];

                fields = ["id", "broca1", "tiempo1", "broca2", "tiempo2", "broca3", "tiempo3", "entradaSalida", "diametro_arrastre1", "diametro_arrastre2", "diametro_arrastre3"];
                break;

            case "Copiado":
                if (this.subprocess == "Cilindrado") {
                    this.tableTitles = ["", "Diametro 1", "Profundidad 1", "Diametro 2", "Profundidad 2", "Diametro de sufridera", "Diametro Ranura", "Profundidad Ranura", "Profundidad de sufridera", "ALTURA TOTAL"];

                    divisionsCNomi = [null];
                    divisionsTole = [null];

                    fields = ["id", "diametro1_cilindrado", "profundidad1_cilindrado", "diametro2_cilindrado", "profundidad2_cilindrado", "diametro_sufridera", "diametro_ranura", "profundidad_ranura", "profundidad_sufridera", "altura_total"];
                } else {
                    this.tableTitles = [ "", "Diametro 1", "Profundidad 1", "Diametro 2", "Profundidad 2", "Diametro 3", "Profundidad 3", "Diametro 4", "Profundidad 4", " VOLUMEN " ];

                    divisionsCNomi = [null];
                    divisionsTole = [null];
                    fields = ["id", "diametro1_cavidades", "profundidad1_cavidades", "diametro2_cavidades", "profundidad2_cavidades", "diametro3", "profundidad3", "diametro4", "profundidad4", "volumen"];
                }
                break;

            case "Palomas": //Proceso de palomas
                this.tableTitles = [ "", "Ancho de Paloma", "Grueso de Paloma", "Profundidad de Paloma", "Rebaje de llanta" ];

                divisionsCNomi = [null];
                divisionsTole = [null];

                fields = ["id", "anchoPaloma", "gruesoPaloma", "profundidadPaloma", "rebajeLlanta"];
                break;
    
            case "Rebajes": //Proceso de Rebajes
                this.tableTitles = [ "", "Rebaje 1", "Rebaje 2", "Rebaje 3", "Profundidad de Bordonio", "Vena 1", "Vena 2", "Simetria" ];

                divisionsCNomi = [null];
                divisionsTole = [null];

                fields = ["id", "rebaje1", "rebaje2", "rebaje3", "profundidad_bordonio", "vena1", "vena2", "simetria"];
                break;
                
            case "Embudo CM": //Proceso de Rebajes
                this.tableTitles = ["", "Conexion de linea de partida", "Conexión 90G", "Altura de conexión", "Diametro de embudo"];

                divisionsCNomi = [null];
                divisionsTole = [null];

                fields = ["id", "conexion_lineaPartida", "conexion_90G", "altura_conexion", "diametro_embudo"];
                break;
            case "Cavidades":
                this.tableTitles = [["#PZ", "Altura 1", "Altura 2", "Altura 3"], ["", "Profundidad", "Diametro", "Profundidad", "Diametro", "Profundidad", "Diametro"]];

                divisionsTitles = [1, 2, 3];
                divisionsCNomi = [null];
                divisionsTole = [1, 3, 5, 7, 9, 11];

                fields = ["id", "profundidad1", "diametro1", "profundidad2", "diametro2", "profundidad3", "diametro3"];
                break;
            case "Off Set":
                this.tableTitles = [["#PZ", "Ancho de altura", "Profundidad de tacon", "Simetria", "Ancho del tacon", "Barreno lateral", "Altura tacon inicial", "Altura tacon intermedia"], ["", "", "Hembra", "Macho", "Hembra", "Macho", "", "Hembra", "Macho", "", ""]];

                divisionsTitles = [2, 3, 5];
                divisionsCNomi = [null];
                divisionsTole = [null];

                fields = ["id", "anchoRanura", "profuTaconHembra", "profuTaconMacho", "simetriaHembra", "simetriaMacho", "anchoTacon", "barrenoLateralHembra", "barrenoLateralMacho", "alturaTaconInicial", "alturaTaconIntermedia"];
                break;

            // Procesos que no necesitan Cotas nominales ni tolerancias
            case "Soldadura":
                this.tableTitles = ["#PZ", "Peso por pieza", "Temperatura de precalentado", "Tiempo de aplicación", "Tipo de soldadura", "Lote", "Error", "Observaciones"];
                divisionsCNomi = [null];
                divisionsTole = [null];
                fields = null;
                break;
            case "Soldadura PTA":
                this.tableTitles = ["#PZ", "Temperatura de calentado", "Temperatura en dispositivo", "Limpieza", "Error", "Observaciones"];
                divisionsCNomi = [null];
                divisionsTole = [null];
                fields = null;
                break;
            case "Asentado":
                this.tableTitles = ["#PZ", "Sin juego", "Sin luz", "Error", "Observaciones"];
                divisionsCNomi = [null];
                divisionsTole = [null];
                fields = null;
                break;
            case "Rectificado":
                this.tableTitles = ["#PZ", "Cumple", "Error", "Observaciones"];
                divisionsCNomi = [null];
                divisionsTole = [null];
                fields = null;
                break;
        }
        
        values = fields !== null ? this.getValues(fields, divisionsCNomi, divisionsTole) : null;
        if(values != null){
            return this.crearTabla(values[0], divisionsCNomi, divisionsTole, values[1], divisionsTitles);
        }
        return this.crearTabla(null, divisionsCNomi, divisionsTole, null, divisionsTitles);
    }
    //prettier-ignore
    crearTabla(names, divisionsCNomi, divisionsTole, values, divisionsTitles = []) {
        // Crear tabla
        const table = document.createElement("table"); // Crear tabla
        table.className = "table"; // Agregar clase a la tabla

        for (let i = 0; i < 3; i++) {
            if(names == null && i > 0){
                return table;
            }
            let tr;
            switch (i) {
                case 0: // Crear columnas de titulos
                let titles = this.tableTitles.length > 2 ? [this.tableTitles] : this.tableTitles;
                titles.forEach((array, indexArray) => {
                    tr = document.createElement("tr");
                    array.forEach((title, index) => {
                        let th = document.createElement("th");
                        th.className = "table-title";
                        th.innerHTML = title;
                        if (index == 0) {
                            th.style = "width:150px;";
                        }else if (indexArray == 0 && titles.length > 1) {
                            if (divisionsTitles.includes(index)) {
                                th.colSpan = 2;
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
                    tr = document.createElement("tr");
                    let divisions = i == 1 ? divisionsCNomi : divisionsTole;

                    for(let x=0; x < names[i - 1].length; x++) {
                        const td = document.createElement("td");
                        if (x != 0){
                            if(divisions.includes(x)){
                                for (let j = 0; j < 2; j++) {
                                    if (values) {
                                        td.appendChild(this.crearInputs("input-medio", names[i - 1][x], values[i - 1][x]));
                                    }else {
                                        td.appendChild(this.crearInputs("input-medio", names[i - 1][x]));
                                    }
                                    if (j != 1) {
                                        x++;
                                    }
                                }
                            }else {
                                if(values) {
                                    td.appendChild(this.crearInputs("input", names[i - 1][x], values[i - 1][x]));
                                }else {
                                    td.appendChild(this.crearInputs("input", names[i - 1][x]));
                                }
                            }
                        }else {
                            td.innerHTML = names[i - 1][x];
                        }
                        tr.appendChild(td);
                    }
                    table.appendChild(tr); //Agregar fila a la tabla.
                    break;
            }
        }
        return table;
    }
    crearInputs(className, name, valueInput = null) {
        let input = document.createElement("input");
        input.className = className;
        input.type = "text";
        input.name = name;
        input.step = "any";
        input.inputMode = "decimal";
        input.required = "true";
        input.value = valueInput || "";
        return input;
    }
}
