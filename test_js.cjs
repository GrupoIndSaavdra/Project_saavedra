const fs = require('fs');

let raw = fs.readFileSync('pieces_full.json', 'utf8');
let data = JSON.parse(raw);
let window = { pieces: data.pieces, infoPiezas: data.infoPiezas };

function asignColorTr(status, error, process) {
    switch (status) {
        case 1: return "#79BFED";
        case 2: return "#FF6B6B";
        case 3: return "#90EE90";
        case 4:
            if (process === "Soldadura PTA" && !error.toLowerCase().includes("fundicion") && !error.toLowerCase().includes("fundición")) {
                return "#90EE90";
            }
            return "#DDA0DD";
        case 5: return "#FFD700";
        case 0:
        default:
            if (error.includes("Incompleto")) {
                return "#FFD700";
            } else if (error == "Ninguno") {
                return "#90EE90";
            } else {
                if (process === "Soldadura PTA" && !error.toLowerCase().includes("fundicion") && !error.toLowerCase().includes("fundición")) {
                    return "#90EE90";
                }
                return "#DDA0DD";
            }
    }
}

function orderedArray(array) {
    return {
        class: array["className"],
        workOrder: array[0],
        noAssembly: array[1],
        operator: array[2],
        machine: array[3],
        process: array[4],
        errors: array[5],
        observations: array.observations ?? "",
        startTime: array.hora_inicio ?? "N/A",
        endTime: array.hora_termino ?? "N/A",
        totalTime: array.tiempo_total ?? "N/A",
        machinedDate: array[6],
        liberationDate: array[7],
        user_liberation: array[8],
        observacion_liberacion: array.observacion_liberacion ?? "",
        btn_seePiece: array[2],
        colorPiece: asignColorTr(array[9], array[5] ?? "", array[4] ?? ""),
    };
}

function sortPiezasDatabaseOrder(piezas, infoPiezas) {
    let mapped = piezas.map((p, i) => ({
        original: p,
        info: infoPiezas[i],
        ordered: orderedArray(p)
    }));

    const classOrder = ["Bombillo", "Molde", "Obturador", "Fondo", "Corona", "Plato", "Embudo", "Cabeza de Soplo", "Candado Obturador"];
    const processOrder = [
        "Cepillado", "Desbaste Exterior", "Revision Laterales", "Primera Operacion",
        "Barreno Maniobra", "Segunda Operacion", "Soldadura", "Soldadura PTA",
        "Rectificado", "Asentado", "Calificado", "Acabado Bombillo", "Acabado Molde",
        "Barreno Profundidad", "Cavidades", "Copiado", "Off Set", "Palomas",
        "Rebajes", "Operacion Equipo_1 operacion", "Operacion Equipo_2 operacion",
        "Candado Obturador_1 operacion", "Candado Obturador_2 operacion",
        "Embudo CM", "Primera Operacion Cabeza Soplo", "Segunda Operacion Cabeza Soplo"
    ];

    mapped.sort((a, b) => {
        let pA = a.ordered;
        let pB = b.ordered;
        let otA = parseInt(pA.workOrder) || 0;
        let otB = parseInt(pB.workOrder) || 0;
        if (otA !== otB) return otA - otB;
        let cIdxA = classOrder.indexOf(pA.class);
        let cIdxB = classOrder.indexOf(pB.class);
        if (cIdxA === -1) cIdxA = 999;
        if (cIdxB === -1) cIdxB = 999;
        if (cIdxA !== cIdxB) return cIdxA - cIdxB;
        let pIdxA = processOrder.indexOf(pA.process);
        let pIdxB = processOrder.indexOf(pB.process);
        if (pIdxA === -1) pIdxA = 999;
        if (pIdxB === -1) pIdxB = 999;
        if (pIdxA !== pIdxB) return pIdxA - pIdxB;
        let numA = parseInt(String(pA.noAssembly).replace(/[^0-9]/g, "")) || 0;
        let numB = parseInt(String(pB.noAssembly).replace(/[^0-9]/g, "")) || 0;
        if (numA !== numB) return numA - numB;
        return 0;
    });

    return {
        piezas: mapped.map(m => m.original),
        infoPiezas: mapped.map(m => m.info)
    };
}

try {
    let sortedData = sortPiezasDatabaseOrder(window.pieces, window.infoPiezas);
    console.log("Success! Sorted pieces length: " + sortedData.piezas.length);
} catch (e) {
    console.error("Error: ", e);
}
