// ══════════════════════════════════════════════════════════════════
// priorities_pdf.js
//
// Módulo independiente para Generación de Exportación a Excel
// de la vista de Prioridades GIS.
// ══════════════════════════════════════════════════════════════════

/**
 * Exporta la tabla de prioridades a archivo Excel (.xls)
 */
function exportTableToExcel(tableID, filename = '') {
    var downloadLink;
    var dataType = 'application/vnd.ms-excel;charset=UTF-8';

    // Configuración especial para evitar truncar ceros en Excel
    var xTag = 'x:';

    var weekElement = document.querySelector('.header-week h2');
    var weekText = weekElement ? weekElement.innerText : 'SEMANA';
    var date = new Date();
    var month = date.toLocaleString('es-MX', { month: 'long' }).toUpperCase();
    var year = date.getFullYear();
    var monthYearText = month + ' DE ' + year;

    var excelHeaderTable = `
    <table style="border: none; width: 100%;">
        <tr>
            <td colspan="3" style="text-align: left; vertical-align: middle;">
                <img src="${window.location.origin}/images/lg_saavedra.png" width="110" height="auto">
            </td>
            <td colspan="7" style="font-family: 'Algerian', serif; font-size: 32px; letter-spacing: 8px; text-align: center; vertical-align: middle;">
                P R I O R I D A D E S
            </td>
            <td colspan="7" style="font-family: 'Calibri', sans-serif; font-size: 16px; font-weight: bold; text-align: right; vertical-align: middle;">
                ${weekText} <span style="font-size: 20px; font-weight: normal; margin-left: 15px;">${monthYearText}</span>
            </td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="7" style="font-family: 'Calibri', sans-serif; font-size: 16px; text-align: center; padding-top: 15px;">
                EN PRODUCCION EN TALLER TECAMAC
            </td>
            <td colspan="7" style="font-family: 'Calibri', sans-serif; font-size: 16px; text-align: center; padding-top: 15px;">
                EN PRODUCCION EN TALLER CDMX
            </td>
        </tr>
    </table>
    `;

    // Clonamos la tabla completa (que ahora incluye thead y tbody juntos)
    var tableSelect = document.getElementById(tableID);

    var cloneTable = document.createElement('table');
    cloneTable.className = 'excel-table';
    var colgroup = tableSelect ? tableSelect.querySelector('colgroup') : null;
    if (colgroup) cloneTable.appendChild(colgroup.cloneNode(true));
    if (tableSelect) cloneTable.appendChild(tableSelect.querySelector('thead').cloneNode(true));
    if (tableSelect) cloneTable.appendChild(tableSelect.querySelector('tbody').cloneNode(true));

    // Remover inputs de fecha ocultos y mangos de arrastre
    var hiddenInputs = cloneTable.querySelectorAll('input[type="date"], .no-print, .col-drag, .cell-drag');
    hiddenInputs.forEach(function (input) {
        input.remove();
    });

    // Aplicar estilos corporativos limpios a los encabezados y celdas
    cloneTable.querySelectorAll('th').forEach(function (th) {
        th.style.backgroundColor = '#033966';
        th.style.color = '#ffffff';
        th.style.fontWeight = 'bold';
        th.style.fontSize = '9pt';
        th.style.fontFamily = 'Arial, sans-serif';
        th.style.border = '1px solid #000000';
        th.style.padding = '6px 8px';
        th.style.verticalAlign = 'middle';
        th.style.wordWrap = 'normal';
        th.style.wordBreak = 'normal';
    });

    cloneTable.querySelectorAll('tbody tr').forEach(function (tr) {
        var rowBg = tr.style.backgroundColor;
        tr.querySelectorAll('td').forEach(function (td) {
            td.style.fontSize = '9.5pt';
            td.style.fontFamily = 'Arial, sans-serif';
            td.style.border = '1px solid #b0b0b0';
            td.style.padding = '4px 6px';
            td.style.verticalAlign = 'middle';
            if (rowBg) {
                td.style.backgroundColor = rowBg;
            }
        });
    });

    // Obtener textos de encabezados
    var titleText = document.querySelector('.header-title h1')?.innerText || 'PRIORIDADES';
    var weekHeader = document.querySelector('.header-week h2')?.innerText || 'SEMANA';
    var weekSub = document.querySelector('.header-week p')?.innerText || 'REPORTE GENERAL';
    var dateText = document.querySelector('.header-date h3')?.innerText || '';

    // Encabezado exacto de 17 columnas
    var excelHeaderTable = `
        <table style="width:100%; border-collapse:collapse; margin-bottom:12px; font-family:Arial, sans-serif;">
            <tr>
                <th colspan="3" style="background-color:#ffffff; color:#033966; border:2px solid #033966; padding:10px; text-align:center; vertical-align:middle; font-size:13pt; font-weight:bold;">
                    GRUPO INDUSTRIAL SAAVEDRA
                </th>
                <th colspan="7" style="background-color:#033966; color:#ffffff; border:2px solid #033966; font-size:18pt; font-weight:bold; letter-spacing:4px; text-align:center; vertical-align:middle; padding:10px;">
                    ${titleText}
                </th>
                <th colspan="4" style="background-color:#0A8504; color:#ffffff; border:2px solid #0A8504; padding:8px; text-align:center; vertical-align:middle;">
                    <span style="font-size:14pt; font-weight:bold;">${weekHeader}</span><br>
                    <span style="font-size:9pt; font-weight:normal;">${weekSub}</span>
                </th>
                <th colspan="3" style="background-color:#404040; color:#ffffff; border:2px solid #404040; padding:8px; font-size:11pt; text-align:center; vertical-align:middle; font-weight:bold;">
                    ${dateText}
                </th>
            </tr>
        </table>
    `;

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
                table { border-collapse: collapse; font-family: 'Calibri', Arial, sans-serif; font-size: 10pt; width: 100%; }
                th, td { border: 1px solid #95a5a6; text-align: center; vertical-align: middle; padding: 5px 6px; }
                th { background-color: #033966; color: #ffffff; font-weight: bold; font-size: 10pt; font-family: '3ds', Arial, sans-serif; }
                .header-title h1 { font-family: 'Algerian', serif !important; font-size: 32px !important; font-weight: normal !important; letter-spacing: 2px !important; }
                .cell-ot-val { font-size: 11pt; font-weight: bold; }
                .cell-producto-val { font-size: 10pt; font-weight: bold; }
                .subcell-row { padding: 3px; border-bottom: 1px solid #d1d5db; }
                .subcell-row:last-child { border-bottom: none; }
            </style>
        </head>
        <body>
            ${excelHeaderTable}
            ${cloneTable.outerHTML}
        </body>
        </html>
    `;

    filename = filename ? filename + '.xls' : 'Prioridades_GIS.xls';
    var blob = new Blob(['\ufeff', excelHTML], {
        type: 'application/vnd.ms-excel'
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

// Exponer funciones globales
window.exportTableToExcel = exportTableToExcel;
