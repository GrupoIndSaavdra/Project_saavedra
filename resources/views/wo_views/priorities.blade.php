@extends('layouts.appMenu')

@section('head')
<title>Dashboard de Prioridades</title>
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@vite(['resources/css/wo_views/priorities.css'])
@endsection

@php
function safeDateParse($value) {
    if (empty($value)) return '';
    try {
        // Handle common formats before parsing
        $clean = str_replace('/', '-', $value);
        return \Carbon\Carbon::parse($clean)->format('Y-m-d');
    } catch (\Exception $e) {
        return ''; // If it's text like "FGF", return empty for the datepicker
    }
}

function safeDateParseDisplay($value) {
    if (empty($value)) return '';
    try {
        $clean = str_replace('/', '-', $value);
        return \Carbon\Carbon::parse($clean)->format('d/m/Y');
    } catch (\Exception $e) {
        return $value; // If it's text, just display the text
    }
}

function getPastelColorForWeek($weekString) {
    // Una paleta curada de 20 colores pasteles profesionales y armoniosos
    $premiumPastels = [
        '#d4f0f0', // Cyan claro
        '#ffdfd3', // Peach
        '#e2f0cb', // Menta suave
        '#f3e5f5', // Lila
        '#ffebd2', // Naranja suave
        '#d6e4ff', // Azul bebe
        '#ffe5e5', // Rosa claro
        '#e8f4d9', // Verde te
        '#f0e6ff', // Lavanda
        '#fff2cc', // Amarillo pastel
        '#d9f0f9', // Celeste
        '#fbe4e4', // Rosa palido
        '#e0f7fa', // Cyan
        '#f5f5dc', // Beige
        '#e6e6fa', // Lavanda gris
        '#ffefd5', // Papaya
        '#e0e8f5', // Azul lavanda
        '#f0fff0', // Honeydew
        '#fff0f5', // Lavender blush
        '#fdf5e6'  // Old lace
    ];

    $weekNum = (int) preg_replace('/[^0-9]/', '', $weekString);
    if ($weekNum === 0) {
        $weekNum = crc32($weekString) % count($premiumPastels);
    }
    
    // Usar el número de semana como índice (haciendo un wrap-around si es mayor a 20)
    $index = $weekNum % count($premiumPastels);
    
    return $premiumPastels[$index];
}
@endphp

@section('content')
@section('background-body', 'background-color: #f4f4f4;')

<div class="priorities-wrapper">
    <!-- Barra de Filtros y Herramientas (Oculta al imprimir) -->
    <div class="toolbar no-print">
        <form method="GET" action="{{ route('master.priorities') }}" class="filters-form">
            <div class="filter-group">
                <label for="start_week">De Semana:</label>
                <select id="start_week" name="start_week" onchange="this.form.submit()">
                    <option value="">Cualquiera</option>
                    @for($i = 1; $i <= 52; $i++)
                        <option value="{{ $i }}" {{ ($startWeek == $i) ? 'selected' : '' }}>Semana {{ $i }}</option>
                    @endfor
                </select>
            </div>
            
            <div class="filter-group">
                <label for="end_week">A Semana:</label>
                <select id="end_week" name="end_week" onchange="this.form.submit()">
                    <option value="">Cualquiera</option>
                    @for($i = 1; $i <= 52; $i++)
                        <option value="{{ $i }}" {{ ($endWeek == $i) ? 'selected' : '' }}>Semana {{ $i }}</option>
                    @endfor
                </select>
            </div>
            
            <div class="filter-group">
                <label for="ot_id">Por OT:</label>
                <select id="ot_id" name="ot_id" onchange="this.form.submit()">
                    <option value="">Todas las OTs</option>
                    @foreach($allOts as $ot)
                        <option value="{{ $ot->id }}" {{ ($otId == $ot->id) ? 'selected' : '' }}>
                            OT {{ $ot->id }} - {{ $ot->cliente ?? 'Sin Cliente' }} ({{ $ot->moldura->nombre ?? 'Sin Moldura' }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="filter-actions">
                <a href="{{ route('master.priorities') }}" class="btn-clear">General / Limpiar</a>
            </div>
        </form>
        
        <div class="export-actions">
            <div id="pm-status-badge" class="pm-table-status-badge no-print" title="Estado de sincronización">
                <span class="pm-table-spinner" hidden></span>
                <span class="pm-table-status-text">Autoguardado Activo</span>
            </div>
            <button type="button" class="btn-export-excel" onclick="exportTableToExcel('prioritiesTable', 'Prioridades_GIS')">Descargar Excel</button>
            <button type="button" class="btn-export-pdf" onclick="generatePDF()">Descargar PDF</button>
            <button type="button" class="btn-clear" onclick="window.print()">Imprimir</button>
        </div>
    </div>

    <!-- Encabezado Estilo Excel -->
    <div class="excel-header">
        <div class="header-logo">
            <img src="{{ asset('images/lg_saavedra.png') }}" alt="Grupo Industrial Saavedra" />
        </div>
        <div class="header-title">
            <h1>PRIORIDADES</h1>
        </div>
        <div class="header-week">
            @if(!empty($startWeek) && !empty($endWeek) && $startWeek == $endWeek)
                <h2>SEMANA {{ $startWeek }}</h2>
            @elseif(!empty($startWeek) || !empty($endWeek))
                <h2>SEMANAS {{ $startWeek ?? 1 }}-{{ $endWeek ?? 52 }}</h2>
            @else
                <h2>SEMANA {{ \Carbon\Carbon::now()->isoWeek() }}</h2>
            @endif
            <p>REPORTE GENERAL</p>
        </div>
        <div class="header-date">
            <h3>{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</h3>
        </div>
    </div>

    <!-- Tabla -->
    <div class="excel-table-container">
        <table class="excel-table" id="prioritiesTable">
            <thead>
                <tr>
                    <th rowspan="2" class="col-drag no-print">MOVER</th>
                    <th rowspan="2" class="col-num">Nº</th>
                    <th rowspan="2" class="col-ot">ORDEN DE TRABAJO</th>
                    <th rowspan="2" class="col-compra">FECHA Y ORDEN DE COMPRA CLIENTE</th>
                    <th rowspan="2" class="col-cliente">CLIENTE</th>
                    <th rowspan="2" class="col-producto">NOMBRE DEL PRODUCTO</th>
                    <th rowspan="2" class="col-cantidades">CANTIDADES</th>
                    <th rowspan="2" class="col-piezas">PIEZAS</th>
                    <th rowspan="2" class="col-forma">FORMA<br>GRABADOS<br>1,2,3,4</th>
                    <th rowspan="2" class="col-prov">PROVEEDOR DE MATERIAL</th>
                    <th rowspan="2" class="col-material">MATERIAL</th>
                    <th rowspan="2" class="col-fecha-fund">FECHA DE ENTREGA PROVEEDOR FUNDICION</th>
                    <th rowspan="2" class="col-fecha-tec">FECHA ENTREGA TECAMAC</th>
                    <th rowspan="2" class="col-semana">Nº DE SEMANA</th>
                    <th rowspan="2" class="col-fecha-mex">FECHA ENTREGA MEXICO</th>
                    <th rowspan="2" class="col-fecha-prom" style="background-color: yellow;">FECHA PROMETIDA ENTREGA</th>
                    <th colspan="2" class="col-obs text-center">OBSERVACIONES</th>
                </tr>
                <tr>
                    <th class="col-pzas">PZAS</th>
                    <th class="col-cav">CAV</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rowCount = 1;
                @endphp
                @foreach($groupedWOs as $semana => $wos)
                    @php
                        $rowColor = getPastelColorForWeek($semana);
                    @endphp
                    @foreach($wos as $wo)
                        <tr class="pm-table-row" data-ot-id="{{ $wo->id }}" style="background-color: {{ $rowColor }};">
                            <td class="text-center no-print cell-drag">
                                <div class="table-drag-handle" title="Sujeta y arrastra para mover de posición la OT">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="9" cy="5" r="1.5" fill="currentColor"/>
                                        <circle cx="9" cy="12" r="1.5" fill="currentColor"/>
                                        <circle cx="9" cy="19" r="1.5" fill="currentColor"/>
                                        <circle cx="15" cy="5" r="1.5" fill="currentColor"/>
                                        <circle cx="15" cy="12" r="1.5" fill="currentColor"/>
                                        <circle cx="15" cy="19" r="1.5" fill="currentColor"/>
                                    </svg>
                                </div>
                            </td>
                            <td class="text-center font-bold font-large cell-row-num">{{ $rowCount++ }}</td>
                            <td class="text-center font-bold cell-ot-val">{{ $wo->id }}</td>
                            <td class="text-center">
                                @if($wo->fecha_compra)
                                    {{ \Carbon\Carbon::parse($wo->fecha_compra)->format('d/m/Y') }}<br>
                                @endif
                                {{ $wo->orden_compra }}
                            </td>
                            <td class="text-center cell-cliente-val">{{ $wo->cliente }}</td>
                            <td class="text-center font-bold cell-producto-val">
                                {{ $wo->moldura ? $wo->moldura->nombre : $wo->nombre_producto }}
                            </td>
                            <td class="text-center p-0">
                                @if($wo->clases->count() > 0)
                                    @foreach($wo->clases as $cl)
                                        <div class="subcell-row">{{ $cl->pedido }}</div>
                                    @endforeach
                                @else
                                    <div class="subcell-row">{{ $wo->cantidad }}</div>
                                @endif
                            </td>
                            <td class="text-center p-0 text-uppercase">
                                @if($wo->clases->count() > 0)
                                    @foreach($wo->clases as $cl)
                                        <div class="subcell-row">{{ $cl->nombre }}</div>
                                    @endforeach
                                @else
                                    <div class="subcell-row">-</div>
                                @endif
                            </td>
                            <td class="text-center" contenteditable="true" onblur="autosaveField({{ $wo->id }}, 'forma_grabados', this)">{{ $wo->forma_grabados }}</td>
                            <td class="text-center">{{ $wo->proveedor_material }}</td>
                            <td class="text-center p-0 cell-materials">
                                @if($wo->clases->count() > 0)
                                    @foreach($wo->clases as $cl)
                                        <div class="subcell-row">{{ $cl->material ?? '-' }}</div>
                                    @endforeach
                                @else
                                    <div class="subcell-row">{{ $wo->material }}</div>
                                @endif
                            </td>
                            <td class="text-center p-0 date-cell" style="cursor: pointer;" onclick="openDatePicker(this)">
                                <span class="date-display">{{ safeDateParseDisplay($wo->fecha_entrega_fundicion) }}</span>
                                <input type="date" style="opacity:0; position:absolute; z-index:-1; width:1px; height:1px;" value="{{ safeDateParse($wo->fecha_entrega_fundicion) }}" onchange="handleDateChange(this, {{ $wo->id }}, 'fecha_entrega_fundicion')">
                            </td>
                            <td class="text-center p-0 date-cell" style="cursor: pointer;" onclick="openDatePicker(this)">
                                <span class="date-display">{{ safeDateParseDisplay($wo->entrega_tecamac) }}</span>
                                <input type="date" style="opacity:0; position:absolute; z-index:-1; width:1px; height:1px;" value="{{ safeDateParse($wo->entrega_tecamac) }}" onchange="handleDateChange(this, {{ $wo->id }}, 'entrega_tecamac')">
                            </td>
                            <td class="text-center font-large font-bold cell-semana">{{ $wo->semana_entrega_cliente }}</td>
                            <td class="text-center p-0 date-cell" style="cursor: pointer;" onclick="openDatePicker(this)">
                                <span class="date-display">{{ safeDateParseDisplay($wo->fecha_real) }}</span>
                                <input type="date" style="opacity:0; position:absolute; z-index:-1; width:1px; height:1px;" value="{{ safeDateParse($wo->fecha_real) }}" onchange="handleDateChange(this, {{ $wo->id }}, 'fecha_real')">
                            </td>
                            <td class="text-center font-bold">
                                {{ safeDateParseDisplay($wo->fecha_entrega_cliente) }}
                            </td>
                            <td class="text-center" contenteditable="true" onblur="autosaveField({{ $wo->id }}, 'observaciones_prioridad', this)" colspan="2">{{ $wo->observaciones_prioridad }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    function exportTableToExcel(tableID, filename = ''){
        var tableSelect = document.getElementById(tableID);
        var cloneTable = tableSelect.cloneNode(true);
        
        // Remove hidden date inputs and drag handles before exporting
        var hiddenInputs = cloneTable.querySelectorAll('input[type="date"], .no-print, .col-drag, .cell-drag');
        hiddenInputs.forEach(function(input) {
            input.remove();
        });

        // Get header texts
        var titleText = document.querySelector('.header-title h1')?.innerText || 'PRIORIDADES';
        var weekHeader = document.querySelector('.header-week h2')?.innerText || 'SEMANA';
        var weekSub = document.querySelector('.header-week p')?.innerText || 'REPORTE GENERAL';
        var dateText = document.querySelector('.header-date h3')?.innerText || '';

        var excelHeaderTable = `
            <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
                <tr>
                    <th colspan="3" style="background-color:#ffffff; border:2px solid #000000; padding:10px; text-align:center; vertical-align:middle;">
                        <span style="font-size:16px; font-weight:bold; color:#000000;">GRUPO INDUSTRIAL SAAVEDRA</span>
                    </th>
                    <th colspan="7" style="background-color:#033966; color:#ffffff; border:2px solid #000000; font-size:22px; font-weight:bold; letter-spacing:4px; text-align:center; vertical-align:middle;">
                        ${titleText}
                    </th>
                    <th colspan="4" style="background-color:#0A8504; color:#ffffff; border:2px solid #000000; padding:5px; text-align:center; vertical-align:middle;">
                        <span style="font-size:16px; font-weight:bold;">${weekHeader}</span><br>
                        <span style="font-size:11px;">${weekSub}</span>
                    </th>
                    <th colspan="4" style="background-color:#404040; color:#ffffff; border:2px solid #000000; padding:5px; font-size:13px; text-align:center; vertical-align:middle;">
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
                                </` + xTag + `WorksheetOptions>
                            </` + xTag + `ExcelWorksheet>
                        </` + xTag + `ExcelWorksheets>
                    </` + xTag + `ExcelWorkbook>
                </xml>
                <![endif]-->
                <style>
                    table { border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; }
                    th, td { border: 1px solid #000000; text-align: center; vertical-align: middle; padding: 4px; }
                    th { background-color: #e0e0e0; font-weight: bold; }
                    .cell-ot-val { font-size: 13px; font-weight: bold; }
                    .cell-producto-val { font-size: 12px; font-weight: bold; }
                    .subcell-row { padding: 4px; border-bottom: 1px solid #000000; }
                    .subcell-row:last-child { border-bottom: none; }
                </style>
            </head>
            <body>
                ${excelHeaderTable}
                <br>
                ${cloneTable.outerHTML}
            </body>
            </html>
        `;

        filename = filename ? filename + '.xls' : 'excel_data.xls';
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

    function generatePDF() {
        var element = document.querySelector('.priorities-wrapper');
        var clone = element.cloneNode(true);
        
        // Remove toolbar from PDF
        var toolbar = clone.querySelector('.toolbar');
        if(toolbar) toolbar.remove();
        
        var hiddenInputs = clone.querySelectorAll('input[type="date"]');
        hiddenInputs.forEach(function(input) {
            input.remove();
        });

        var opt = {
            margin:       [4, 4, 4, 4],
            filename:     'Prioridades_GIS.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, logging: false },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(clone).save();
    }

    function autosaveField(otId, fieldName, element) {
        var value = element.innerText.trim();
        _doAutosave(otId, fieldName, value, element);
    }

    function openDatePicker(td) {
        var input = td.querySelector('input[type="date"]');
        if (input) {
            try {
                input.showPicker();
            } catch (e) {
                input.focus();
            }
        }
    }

    function handleDateChange(input, otId, fieldName) {
        var value = input.value;
        var td = input.parentElement;
        var span = td.querySelector('.date-display');
        
        // Update display text immediately
        if(value) {
            var parts = value.split('-');
            span.innerText = parts[2] + '/' + parts[1] + '/' + parts[0];
        } else {
            span.innerText = '';
        }
        
        _doAutosave(otId, fieldName, value, td);
    }

    function _doAutosave(otId, fieldName, value, feedbackElement) {
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            console.error("CSRF token not found.");
            return;
        }
        
        // Add a visual indicator of saving (border outline)
        feedbackElement.style.outline = "2px solid #0d6efd";
        feedbackElement.style.outlineOffset = "-2px";

        fetch("{{ route('master.priorities.autosave') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                ot_id: otId,
                field: fieldName,
                value: value
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Success indicator (green)
                feedbackElement.style.outline = "2px solid #198754";
                setTimeout(() => {
                    feedbackElement.style.outline = "none";
                }, 1000);
            } else {
                // Error indicator (red)
                feedbackElement.style.outline = "2px solid #dc3545";
                console.error(data.message);
            }
        })
        .catch(error => {
            feedbackElement.style.outline = "2px solid #dc3545";
            console.error("Error al guardar:", error);
        });
    }

    // ══════════════════════════════════════════════════════════════
    // DRAG AND DROP EN LA TABLA DE PRIORIDADES CON AUTOGUARDADO
    // ══════════════════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function () {
        var tableBody = document.querySelector('#prioritiesTable tbody');
        if (!tableBody) return;

        var draggedRow = null;
        var isSavingPriorities = false;
        var savePrioritiesPending = false;

        function initRowDrag(row) {
            var handle = row.querySelector('.table-drag-handle');
            if (!handle) return;

            handle.addEventListener('mousedown', function () {
                row.draggable = true;
            });
            handle.addEventListener('touchstart', function () {
                row.draggable = true;
            }, { passive: true });

            row.addEventListener('dragstart', function (e) {
                draggedRow = row;
                row.classList.add('is-row-dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', row.dataset.otId || '');
            });

            row.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                if (!draggedRow || draggedRow === row) return;

                var rect = row.getBoundingClientRect();
                var isBottom = e.clientY > (rect.top + rect.height / 2);

                if (isBottom) {
                    row.classList.remove('drag-over-top');
                    row.classList.add('drag-over-bottom');
                } else {
                    row.classList.remove('drag-over-bottom');
                    row.classList.add('drag-over-top');
                }
            });

            row.addEventListener('dragleave', function (e) {
                var rect = row.getBoundingClientRect();
                if (
                    e.clientX < rect.left ||
                    e.clientX >= rect.right ||
                    e.clientY < rect.top ||
                    e.clientY >= rect.bottom
                ) {
                    row.classList.remove('drag-over-top', 'drag-over-bottom');
                }
            });

            row.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();

                row.classList.remove('drag-over-top', 'drag-over-bottom');
                if (!draggedRow || draggedRow === row) return;

                var rect = row.getBoundingClientRect();
                var isBottom = e.clientY > (rect.top + rect.height / 2);

                if (isBottom) {
                    row.parentNode.insertBefore(draggedRow, row.nextSibling);
                } else {
                    row.parentNode.insertBefore(draggedRow, row);
                }

                renumberRows();
                autoSaveTablePriorities();
            });

            row.addEventListener('dragend', function () {
                row.draggable = false;
                row.classList.remove('is-row-dragging');
                document.querySelectorAll('#prioritiesTable tbody tr').forEach(function (r) {
                    r.classList.remove('drag-over-top', 'drag-over-bottom', 'is-row-dragging');
                });
                draggedRow = null;
            });
        }

        document.querySelectorAll('#prioritiesTable tbody tr').forEach(initRowDrag);

        function renumberRows() {
            var rows = document.querySelectorAll('#prioritiesTable tbody tr');
            rows.forEach(function (r, index) {
                var numCell = r.querySelector('.cell-row-num');
                if (numCell) {
                    numCell.textContent = index + 1;
                }
            });
        }

        function autoSaveTablePriorities() {
            if (isSavingPriorities) {
                savePrioritiesPending = true;
                return;
            }
            isSavingPriorities = true;
            savePrioritiesPending = false;

            updateTableStatus('saving');

            var rows = document.querySelectorAll('#prioritiesTable tbody tr');
            var priorities = [];
            rows.forEach(function (r, index) {
                var otId = r.dataset.otId;
                if (otId) {
                    priorities.push({
                        ot_id: otId,
                        prioridad: index + 1
                    });
                }
            });

            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch("{{ route('savePriorities') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ priorities: priorities })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data && data.success) {
                    updateTableStatus('saved');
                } else {
                    updateTableStatus('error');
                }
            })
            .catch(function (err) {
                console.error("Error al autoguardar prioridades:", err);
                updateTableStatus('error');
            })
            .finally(function () {
                isSavingPriorities = false;
                if (savePrioritiesPending) {
                    autoSaveTablePriorities();
                }
            });
        }

        function updateTableStatus(state) {
            var badge = document.getElementById('pm-status-badge');
            if (!badge) return;
            var spinner = badge.querySelector('.pm-table-spinner');
            var text = badge.querySelector('.pm-table-status-text');

            if (state === 'saving') {
                badge.className = 'pm-table-status-badge pm-status-saving no-print';
                if (spinner) spinner.hidden = false;
                if (text) text.textContent = 'Guardando orden...';
            } else if (state === 'saved') {
                badge.className = 'pm-table-status-badge pm-status-saved no-print';
                if (spinner) spinner.hidden = true;
                if (text) text.textContent = '✓ Orden Guardado';
            } else if (state === 'error') {
                badge.className = 'pm-table-status-badge pm-status-error no-print';
                if (spinner) spinner.hidden = true;
                if (text) text.textContent = '⚠ Error al guardar';
            } else {
                badge.className = 'pm-table-status-badge no-print';
                if (spinner) spinner.hidden = true;
                if (text) text.textContent = 'Autoguardado Activo';
            }
        }
    });
</script>
@endsection
