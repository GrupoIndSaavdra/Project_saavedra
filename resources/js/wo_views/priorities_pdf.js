// ══════════════════════════════════════════════════════════════════
// priorities_pdf.js
//
// Módulo independiente para Generación de Exportación a Excel (.xls)
// de la vista de Prioridades GIS.
// ══════════════════════════════════════════════════════════════════

import './priorities_documents.js';

/**
 * Exporta la tabla de prioridades a archivo Excel (.xls) con diseño corporativo GIS impecable.
 */
function exportTableToExcel(tableID, filename = '') {
    var tableSelect = document.getElementById(tableID);
    if (!tableSelect) {
        console.error('No se encontró la tabla con ID:', tableID);
        return;
    }

    // 1. Extraer textos de encabezados principales
    var titleText = document.querySelector('.header-title h1')?.innerText.trim() || 'PRIORIDADES';
    var weekHeader = document.querySelector('.header-week h2')?.innerText.trim() || 'SEMANA';
    var weekSub = document.querySelector('.header-week p')?.innerText.trim() || 'REPORTE GENERAL';
    var dateText = document.querySelector('.header-date h3')?.innerText.trim() || '';

    // 2. Clonar thead y tbody
    var originalThead = tableSelect.querySelector('thead');
    var originalTbody = tableSelect.querySelector('tbody');
    if (!originalThead || !originalTbody) return;

    var thead = originalThead.cloneNode(true);
    var tbody = originalTbody.cloneNode(true);

    // 3. Eliminar elementos interactivos y columnas no imprimibles
    thead.querySelectorAll('.no-print, .col-drag, .col-select').forEach(function(el) {
        el.remove();
    });
    tbody.querySelectorAll('.no-print, .cell-drag, .col-select').forEach(function(el) {
        el.remove();
    });

    // 4. Limpiar cualquier atributo o estilo de height y rowspan que deforme o colapse filas en Excel
    thead.querySelectorAll('th, td, tr').forEach(function(el) {
        el.removeAttribute('rowspan');
        el.removeAttribute('height');
        el.style.height = '';
    });

    tbody.querySelectorAll('th, td, tr').forEach(function(el) {
        el.removeAttribute('rowspan');
        el.removeAttribute('height');
        el.style.height = '';
    });

    // 5. Crear el banner institucional como primer tr dentro del thead (Exactamente 16 columnas)
    var bannerTr = document.createElement('tr');
    bannerTr.innerHTML = `
        <th colspan="3" style="background-color:#ffffff; color:#033966; border:2px solid #033966; padding:10px; text-align:center; vertical-align:middle; font-size:12pt; font-weight:bold; font-family:Arial, sans-serif;">
            GRUPO INDUSTRIAL SAAVEDRA
        </th>
        <th colspan="7" style="background-color:#033966; color:#ffffff; border:2px solid #033966; font-size:17pt; font-weight:bold; letter-spacing:4px; text-align:center; vertical-align:middle; padding:10px; font-family:Arial, sans-serif;">
            ${titleText}
        </th>
        <th colspan="3" style="background-color:#0A8504; color:#ffffff; border:2px solid #0A8504; padding:8px; text-align:center; vertical-align:middle; font-family:Arial, sans-serif;">
            <span style="font-size:13pt; font-weight:bold;">${weekHeader}</span><br>
            <span style="font-size:9pt; font-weight:normal;">${weekSub}</span>
        </th>
        <th colspan="3" style="background-color:#404040; color:#ffffff; border:2px solid #404040; padding:8px; font-size:10pt; text-align:center; vertical-align:middle; font-weight:bold; font-family:Arial, sans-serif;">
            ${dateText}
        </th>
    `;
    thead.insertBefore(bannerTr, thead.firstChild);

    // 6. Estilizar encabezados de columnas (Fila 2 de thead)
    var colHeaders = thead.querySelectorAll('tr:nth-child(2) th');
    colHeaders.forEach(function(th) {
        var isFechaPrometida = th.classList.contains('col-fecha-prom') || th.innerText.includes('PROMETIDA');
        if (isFechaPrometida) {
            th.style.backgroundColor = '#facc15';
            th.style.color = '#000000';
        } else {
            th.style.backgroundColor = '#033966';
            th.style.color = '#ffffff';
        }
        th.style.fontWeight = 'bold';
        th.style.fontSize = '9pt';
        th.style.fontFamily = 'Arial, sans-serif';
        th.style.border = '1px solid #000000';
        th.style.padding = '8px 5px';
        th.style.verticalAlign = 'middle';
        th.style.textAlign = 'center';
    });

    // 7. Sanitizar y aplanar cada celda en tbody para garantizar altura natural sin colapso
    tbody.querySelectorAll('tr').forEach(function(tr) {
        var rowBg = tr.style.backgroundColor || '#ffffff';
        tr.style.height = '';
        tr.removeAttribute('height');

        var cells = tr.querySelectorAll('td');
        cells.forEach(function(td, idx) {
            // Eliminar cualquier height residual del hack CSS
            td.style.height = '';
            td.removeAttribute('height');

            // Formato tipográfico y bordes limpios
            td.style.fontSize = '9pt';
            td.style.fontFamily = 'Arial, sans-serif';
            td.style.border = '1px solid #b0b0b0';
            td.style.padding = '6px 8px';
            td.style.verticalAlign = 'middle';
            td.style.backgroundColor = rowBg;
            td.style.msoNumberFormat = '\\@'; // No truncar ceros a la izquierda

            // Procesar contenido:
            // Caso A: Celdas con sub-renglones (Cantidades, Piezas, Material, Fechas de fundición)
            var subcells = td.querySelectorAll('.subcell-row');
            if (subcells.length > 0) {
                var lines = [];
                subcells.forEach(function(sc) {
                    var t = sc.innerText.trim();
                    if (t && t !== '') lines.push(t);
                });
                td.innerHTML = lines.length > 0 ? lines.join('<br style="mso-data-placement:same-cell;"/>') : '-';
            } else {
                // Caso B: Celdas con fecha individual
                var dateDisp = td.querySelector('.date-display');
                if (dateDisp) {
                    var dTxt = dateDisp.innerText.trim();
                    td.innerHTML = dTxt || '-';
                } else {
                    // Caso C: Celdas de texto general, eliminar SVGs, inputs y botones
                    td.querySelectorAll('svg, input, button').forEach(function(el) {
                        el.remove();
                    });
                    var txt = td.innerText.trim();
                    if (!txt || txt === '' || txt === '-') {
                        td.innerHTML = '-';
                    } else {
                        // Respetar saltos de línea con el estándar de Excel
                        td.innerHTML = txt.replace(/\r?\n/g, '<br style="mso-data-placement:same-cell;"/>');
                    }
                }
            }

            // Alineación según tipo de columna (Índice 4 = Nombre del Producto a la izquierda)
            if (idx === 4) {
                td.style.textAlign = 'left';
            } else {
                td.style.textAlign = 'center';
            }

            // Columna 14 = Fecha Prometida Entrega (amarillo distintivo)
            if (idx === 14) {
                td.style.backgroundColor = '#fef08a';
                td.style.fontWeight = 'bold';
            }
        });
    });

    // 8. Construir tabla final con colgroup de 16 columnas proporcionales
    var finalTable = document.createElement('table');
    finalTable.className = 'excel-table';

    var colgroup = document.createElement('colgroup');
    colgroup.innerHTML = `
        <col style="width: 45px;">   <!-- 1. Nº -->
        <col style="width: 85px;">   <!-- 2. ORDEN DE TRABAJO -->
        <col style="width: 125px;">  <!-- 3. FECHA Y ORDEN DE COMPRA CLIENTE -->
        <col style="width: 140px;">  <!-- 4. CLIENTE -->
        <col style="width: 280px;">  <!-- 5. NOMBRE DEL PRODUCTO -->
        <col style="width: 75px;">   <!-- 6. CANTIDADES -->
        <col style="width: 130px;">  <!-- 7. PIEZAS -->
        <col style="width: 95px;">   <!-- 8. FORMA GRABADOS 1,2,3,4 -->
        <col style="width: 180px;">  <!-- 9. PROVEEDOR DE MATERIAL -->
        <col style="width: 120px;">  <!-- 10. MATERIAL -->
        <col style="width: 120px;">  <!-- 11. FECHA ENTREGA PROV FUNDICION -->
        <col style="width: 110px;">  <!-- 12. FECHA ENTREGA TECAMAC -->
        <col style="width: 75px;">   <!-- 13. N° DE SEMANA -->
        <col style="width: 110px;">  <!-- 14. FECHA ENTREGA MEXICO -->
        <col style="width: 115px;">  <!-- 15. FECHA PROMETIDA ENTREGA -->
        <col style="width: 180px;">  <!-- 16. OBSERVACIONES -->
    `;
    finalTable.appendChild(colgroup);
    finalTable.appendChild(thead);
    finalTable.appendChild(tbody);

    // 9. Ensamblar documento HTML para Excel
    var xTag = 'x:';
    var excelHTML = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            <!--[if gte mso 9]>
            <xml>
                <` + xTag + `ExcelWorkbook>
                    <` + xTag + `ExcelWorksheets>
                        <` + xTag + `ExcelWorksheet>
                            <` + xTag + `Name>Prioridades GIS</` + xTag + `Name>
                            <` + xTag + `WorksheetOptions>
                                <` + xTag + `DisplayGridlines/>
                                <` + xTag + `Print>
                                    <` + xTag + `ValidPrinterInfo/>
                                    <` + xTag + `PaperSizeIndex>1</` + xTag + `PaperSizeIndex>
                                    <` + xTag + `Orientation>Landscape</` + xTag + `Orientation>
                                    <` + xTag + `FitToPage/>
                                    <` + xTag + `FitWidth>1</` + xTag + `FitWidth>
                                    <` + xTag + `FitHeight>99</` + xTag + `FitHeight>
                                </` + xTag + `Print>
                            </` + xTag + `WorksheetOptions>
                        </` + xTag + `ExcelWorksheet>
                    </` + xTag + `ExcelWorksheets>
                </` + xTag + `ExcelWorkbook>
            </xml>
            <![endif]-->
            <style>
                table { border-collapse: collapse; font-family: 'Calibri', Arial, sans-serif; font-size: 9.5pt; width: 100%; }
                th, td { border: 1px solid #b0b0b0; vertical-align: middle; padding: 5px 6px; }
            </style>
        </head>
        <body>
            ${finalTable.outerHTML}
        </body>
        </html>
    `;

    filename = filename ? filename + '.xls' : 'Prioridades_GIS.xls';
    var blob = new Blob(['\ufeff', excelHTML], {
        type: 'application/vnd.ms-excel;charset=utf-8'
    });

    if (navigator.msSaveOrOpenBlob) {
        navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        var downloadLink = document.createElement("a");
        downloadLink.href = URL.createObjectURL(blob);
        downloadLink.download = filename;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
}

// Exponer función globalmente
window.exportTableToExcel = exportTableToExcel;
