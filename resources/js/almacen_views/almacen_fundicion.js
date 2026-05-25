/**
 * almacen_fundicion.js
  * Lógica de la vista de Almacén/Calidad para Dibujos de Fundición.
  */
console.log('ALMACEN_FUNDICION_JS_V2_LOADED');

// Helper para notificaciones usando el sistema del layout
function almacenToast(message, type = 'success') {
    if (typeof window.mostrarNotificacion === 'function') {
        window.mostrarNotificacion(message, type);
    } else if (typeof window.toastpremium === 'function') {
        window.toastpremium(message, type);
    } else if (typeof window.showToastAlert === 'function') {
        window.showToastAlert(message, type);
    } else {
        alert(message);
    }
}


// Helper para truncar a 3 decimales sin redondear
function truncateToThreeDecimalsJS(val) {
    if (val === null || val === undefined || val === '') return '';
    let valStr = String(val);
    if (valStr.includes('.')) {
        let parts = valStr.split('.');
        let integerPart = parts[0];
        let decimalPart = parts[1].substring(0, 3);
        return integerPart + '.' + decimalPart;
    }
    return valStr;
}

function formatInputTruncated(input) {
    let val = input.value;
    if (!val) return;
    let truncated = truncateToThreeDecimalsJS(val);
    if (truncated !== '') {
        let parts = truncated.split('.');
        let integerPart = parts[0];
        let decimalPart = parts[1] || '';
        while (decimalPart.length < 3) {
            decimalPart += '0';
        }
        input.value = integerPart + '.' + decimalPart;
    }
}

function initTruncateInputs() {
    document.addEventListener('blur', (e) => {
        if (e.target.matches('.lib-num-input, .lib-num-input-sm')) {
            formatInputTruncated(e.target);
        }
    }, true);
}

document.addEventListener('DOMContentLoaded', () => {
    initToggleFiles();
    initCustomFileInputs();
    initTruncateInputs();
});

// ── TOGGLE FILAS DE ARCHIVOS ──────────────────────────────────────────────────

function initToggleFiles() {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-toggle-files');
        if (!btn) return;

        const targetId = btn.dataset.target;
        const filesRow = document.getElementById(targetId);

        if (!filesRow) return;

        const isOpen = filesRow.classList.contains('open');

        // Cerrar todos los demás antes de abrir el nuevo (Comportamiento de Acordeón)
        if (!isOpen) {
            document.querySelectorAll('.alm-files-row.open').forEach(row => {
                row.classList.remove('open');
            });
            document.querySelectorAll('.btn-toggle-files.active').forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-expanded', 'false');
                b.innerHTML = 'Ver PDFs';
            });
        }

        if (isOpen) {
            filesRow.classList.remove('open');
            btn.classList.remove('active');
            btn.setAttribute('aria-expanded', 'false');
            btn.innerHTML = 'Ver PDFs';
        } else {
            filesRow.classList.add('open');
            btn.classList.add('active');
            btn.setAttribute('aria-expanded', 'true');
            btn.innerHTML = 'Ocultar';
        }
    });
}

// ── VER PDF ───────────────────────────────────────────────────────────────────

/**
 * Abre el PDF desde el directorio aislado FUNDICION_ALMACEN en una nueva pestaña.
 *
 * @param {string} ot      - Nombre de la carpeta OT
 * @param {string} archivo - Nombre del archivo PDF
 */
window.almacenVerPdf = function (ot, archivo, tipo = 'dibujo') {
    const url = window.almacenRoutes.serve
        + '?ot=' + encodeURIComponent(ot)
        + '&archivo=' + encodeURIComponent(archivo)
        + '&tipo=' + encodeURIComponent(tipo);

    window.open(url, '_blank', 'noopener,noreferrer');
};

// ── TOAST NOTIFICACIONES ──────────────────────────────────────────────────────

function mostrarToast(mensaje, esError = false) {
    const prev = document.querySelector('.alm-toast');
    if (prev) prev.remove();

    const toast = document.createElement('div');
    toast.className = 'alm-toast ' + (esError ? 'error' : 'success');

    let baseUrl = window.baseUrl || (window.location.origin + '/');
    if (!baseUrl.endsWith('/')) baseUrl += '/';

    const iconPath = esError ? baseUrl + 'images/delete.png' : baseUrl + 'images/ready.png';
    const iconAlt = esError ? 'error' : 'success';

    toast.innerHTML = `
        <img src="${iconPath}" class="alert-icon-small" alt="${iconAlt}">
        <div class="alert-content">
            ${mensaje}
        </div>
        <button class="close-alert-x" onclick="this.parentElement.remove()">×</button>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.add('fade-out');
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 450);
        }
    }, 4000);
}

// ── CONTROL DE MODELOS ────────────────────────────────────────────────────────

/**
 * Marca una OT como que ya tiene el modelo físico.
 * @param {string} ot
 */
window.confirmarModelo = function (ot) {
    if (!confirm(`¿Confirmas que actualmente cuentas con el modelo físico para la OT ${ot}?`)) return;

    fetch(window.almacenRoutes.confirmarModelo, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ ot })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarToast(data.message);
                const container = document.getElementById(`status-modelo-${ot}`);
                const baseUrl = window.baseUrl || (window.location.origin + '/');
                if (container) {
                    container.innerHTML = `
                    <span class="badge-modelo-ok" title="Modelo disponible">
                        <img src="${baseUrl}${baseUrl.endsWith('/') ? '' : '/'}images/aprobado.png" alt="OK" style="width: 35px; height: 35px;">
                    </span>
                `;
                }
            } else {
                mostrarToast(data.message || 'Error al actualizar estado del modelo', true);
            }
        })
        .catch(err => {
            console.error(err);
            mostrarToast('Error de conexión', true);
        });
};

// ── MODAL PRE-ORDEN ───────────────────────────────────────────────────────────

let availableClasses = [];   // Caché de clases para las filas nuevas
let optionsHtmlCache = '';   // Caché del HTML de las opciones para evitar reconstruir en cada fila

const normalizeStr = (str) => {
    if (!str) return '';
    return str.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
};

// ── Apertura / Cierre del modal ──

window.abrirModalPreOrden = function (ot) {
    const modal = document.getElementById('modalPreOrden');
    const inputOt = document.getElementById('po-ot');
    const inputMoldura = document.getElementById('po-moldura');
    const tbody = document.getElementById('alm-tbody-preorden');

    window.currentFechaEntrega = '';

    // Resetear estado multi-orden (botón añadir siempre visible)
    resetMultiOrderState();

    // Separar OT y Moldura si vienen juntas (ej. "6473 - VINERA...")
    let otNum = ot;
    let molduraName = '';
    if (ot.includes(' - ')) {
        const parts = ot.split(' - ');
        otNum = parts[0].trim();
        molduraName = parts.slice(1).join(' - ').trim();
    }
    // Dejar solo los números de la OT (por ejemplo, "OT 6748" pasa a "6748")
    otNum = otNum.replace(/[^0-9]/g, '');

    inputOt.value = otNum;
    document.getElementById('po-ot-raw').value = ot;
    if (molduraName) inputMoldura.value = molduraName;
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px;"><div class="alm-spinner"></div> Cargando datos de la OT...</td></tr>';

    modal.classList.add('open');
    document.body.classList.add('modal-open');

    // Cargar datos de la OT (Moldura y Clases activas)
    fetch(`${window.almacenRoutes.getOtData}?ot=${ot}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                inputMoldura.value = data.moldura || 'N/A';
                availableClasses = data.clases || [];

                availableClasses.forEach(c => {
                    c._norm = normalizeStr(c.nombre);
                });

                if (data.clases_vinculadas) {
                    data.clases_vinculadas.forEach(cv => {
                        const cvNorm = normalizeStr(cv);
                        const found = availableClasses.find(ac => ac._norm === cvNorm || ac._norm.includes(cvNorm) || cvNorm.includes(ac._norm));
                        if (!found) {
                            availableClasses.push({
                                id: `manual_${cv}`,
                                nombre: cv,
                                _norm: cvNorm
                            });
                        }
                    });
                }

                optionsHtmlCache = '<option value="">Selecciona clase</option>' +
                    availableClasses.map(c => `<option value="${c.id}" data-nombre="${c.nombre}">${c.nombre}</option>`).join('');

                if (data.folio) document.getElementById('po-folio').value = data.folio;

                tbody.innerHTML = '';

                if (data.pre_orden_data) {
                    const pod = data.pre_orden_data;
                    window.currentFechaEntrega = pod.fecha_entrega ? pod.fecha_entrega.split(' ')[0] : '';
                    if (pod.fecha_creacion) {
                        const dateOnly = pod.fecha_creacion.split(' ')[0];
                        document.getElementById('po-fecha').value = dateOnly;
                    }
                    if (pod.observaciones) document.getElementById('po-observaciones').value = pod.observaciones;
                    if (pod.proveedor) document.getElementById('po-proveedor').value = pod.proveedor;

                    if (pod.filas && pod.filas.length > 0) {
                        const fragment = document.createDocumentFragment();
                        pod.filas.forEach(rowObj => {
                            const claseId = rowObj.id_clase || rowObj.clase_id;
                            fragment.appendChild(createRowElement('', false, {
                                tipo_modelo: rowObj.tipo_modelo,
                                impresiones: rowObj.impresiones,
                                cantidad: rowObj.cantidad,
                                clase_id: claseId,
                                codigo_modelo: rowObj.codigo_modelo
                            }));
                        });
                        tbody.appendChild(fragment);
                    } else {
                        tbody.appendChild(createRowElement());
                    }
                } else {
                    if (data.clases_vinculadas && data.clases_vinculadas.length > 0) {
                        const fragment = document.createDocumentFragment();
                        data.clases_vinculadas.forEach(claseNombre => {
                            fragment.appendChild(createRowElement(claseNombre));
                        });
                        tbody.appendChild(fragment);
                        syncClassOptions('alm-tbody-preorden');
                    } else {
                        tbody.appendChild(createRowElement());
                    }
                }

                // Datos cargados con éxito
            } else {
                mostrarToast(data.message || 'Error al cargar datos de la OT', true);
                cerrarModalPreOrden();
            }
        })
        .catch(err => {
            console.error(err);
            mostrarToast('Error al obtener datos', true);
        });
};

window.cerrarModalPreOrden = function () {
    const modal = document.getElementById('modalPreOrden');
    modal.classList.remove('open');
    document.body.classList.remove('modal-open');
    document.getElementById('formPreOrden').reset();
    document.getElementById('alm-tbody-preorden').innerHTML = '';
    optionsHtmlCache = '';
    window.currentFechaEntrega = '';
    resetMultiOrderState();
};

function resetMultiOrderState() {
    // No-op para mantener compatibilidad
}

// ── Creación de filas de la tabla ──

/**
 * Crea un elemento TR para la tabla de pre-orden.
 * @param {string} claseNombrePredefinida - Nombre de clase a preseleccionar.
 * @param {boolean} isSecondOrder - Si es para la tabla 2, usa el tbody2.
 */
function createRowElement(claseNombrePredefinida = '', isSecondOrder = false, rowObj = null) {
    const tr = document.createElement('tr');

    const fechaEntregaVal = (rowObj && rowObj.fecha_entrega) ? rowObj.fecha_entrega : (window.currentFechaEntrega || '');

    let selectedId = '';
    if (rowObj && rowObj.clase_id) {
        selectedId = rowObj.clase_id;
    } else if (claseNombrePredefinida) {
        const search = normalizeStr(claseNombrePredefinida);
        const found = availableClasses.find(c => c._norm === search || c._norm.includes(search) || search.includes(c._norm));
        if (found) selectedId = found.id;
    }

    let options = optionsHtmlCache;
    if (selectedId) {
        options = '<option value="">Selecciona clase</option>' +
            availableClasses.map(c => {
                const selectedAttr = (selectedId == c.id) ? 'selected' : '';
                return `<option value="${c.id}" data-nombre="${c.nombre}" ${selectedAttr}>${c.nombre}</option>`;
            }).join('');
    }

    const deleteHandler = isSecondOrder ? 'eliminarFilaPreOrden2(this)' : 'eliminarFilaPreOrden(this)';

    const tipoVal = rowObj && rowObj.tipo_modelo ? rowObj.tipo_modelo : '';
    const impresionesVal = rowObj && rowObj.impresiones ? rowObj.impresiones : '';
    const cantidadVal = rowObj && rowObj.cantidad ? rowObj.cantidad : '';
    const codigoVal = rowObj && rowObj.codigo_modelo ? rowObj.codigo_modelo : '';

    tr.innerHTML = `
        <td>
            <select name="tipo_modelo[]" class="form-control po-tipo-select" required onchange="generarCodigoFila(this.closest('tr').querySelector('.po-clase-select'))">
                <option value="" disabled ${!tipoVal ? 'selected' : ''}>Selecciona uno</option>
                <option value="Suelto" ${tipoVal === 'Suelto' ? 'selected' : ''}>Suelto</option>
                <option value="Placa" ${tipoVal === 'Placa' ? 'selected' : ''}>Placa</option>
                <option value="Templadera" ${tipoVal === 'Templadera' ? 'selected' : ''}>Templadera</option>
            </select>
        </td>
        <td>
            <input type="text" name="impresiones[]" class="form-control" style="text-align:center;" placeholder="Ej. 1" required value="${impresionesVal}">
        </td>
        <td>
            <input type="number" name="cantidad[]" class="form-control" style="text-align:center;" min="1" placeholder="0" required value="${cantidadVal}">
        </td>
        <td>
            <select name="id_clase[]" class="form-control po-clase-select" required onchange="generarCodigoFila(this)">
                ${options}
            </select>
        </td>
        <td>
            <input type="text" name="codigo_modelo[]" class="form-control po-codigo-input" value="${codigoVal}">
        </td>
        <td>
            <input type="date" name="fecha_entrega_rows[]" class="form-control po-fecha-entrega-row" readonly value="${fechaEntregaVal}" style="text-align:center; background-color: #f1f5f9; color: #64748b;">
        </td>
        <td style="text-align:center;">
            <button type="button" class="btn-img-action" onclick="${deleteHandler}" title="Quitar esta clase de la lista">
                <img src="${window.baseUrl || ''}${(window.baseUrl || '').endsWith('/') ? '' : '/'}images/quitar.png" alt="Quitar" style="width: 30px;">
            </button>
        </td>
    `;

    const select = tr.querySelector('.po-clase-select');
    if (!rowObj && select.value) {
        const ot = document.getElementById('po-ot').value;
        const nombreClase = select.options[select.selectedIndex].dataset.nombre || select.options[select.selectedIndex].text;
        tr.querySelector('.po-codigo-input').value = calculateModelCode(ot, nombreClase);
    }

    return tr;
}

window.agregarFilaPreOrden = function () {
    document.getElementById('alm-tbody-preorden').appendChild(createRowElement());
    syncClassOptions('alm-tbody-preorden');
};

window.eliminarFilaPreOrden = function (btn) {
    const row = btn.closest('tr');
    const tbody = document.getElementById('alm-tbody-preorden');
    if (tbody.rows.length > 1) {
        row.remove();
        // No llamamos a syncClassOptions: las opciones ya no se filtran
    } else {
        mostrarToast('Debe haber al menos una clase en la pre-orden', true);
    }
};

// ── Cálculo de códigos ──

/**
 * Función centralizada para calcular el código de modelo
 */
function calculateModelCode(ot, nombreClase, tipoModelo = '') {
    const siglas = {
        'Corona': 'C',
        'Cabeza de Soplo': 'CS',
        'Candado Obturador': 'CO',
        'Obturador': 'O',
        'Bombillo': 'B',
        'Molde': 'M',
        'Fondo': 'F',
        'Guía': 'G',
        'Guias': 'G',
        'Guías': 'G',
        'Pistón': 'P',
        'Pistones': 'P',
        'Plato': 'PL'
    };

    const matches = ot.match(/\d+/);
    const otNum = matches ? matches[0] : ot;
    const sigla = siglas[nombreClase] || 'X';

    // Regla especial: Templadera → prefijo T en cualquier clase
    if (tipoModelo === 'Templadera') {
        return `T${sigla}${otNum}`;
    }

    return `${sigla}${otNum}`;
}

window.generarCodigoFila = function (select) {
    const row = select.closest('tr');
    const inputCodigo = row.querySelector('.po-codigo-input');
    const tipoSelect = row.querySelector('.po-tipo-select');
    const ot = document.getElementById('po-ot').value;
    const tipoModelo = tipoSelect ? tipoSelect.value : '';

    if (!select.value) {
        inputCodigo.value = '';
    } else {
        const nombreClase = select.options[select.selectedIndex].dataset.nombre || select.options[select.selectedIndex].text;
        inputCodigo.value = calculateModelCode(ot, nombreClase, tipoModelo);
    }
    // No se llama a syncClassOptions: las opciones no se filtran
};

/**
 * Sincroniza las opciones de todos los selects de un tbody.
 * En esta versión NO se filtran/ocultan clases: todas las opciones
 * permanecen disponibles para que el usuario pueda elegirlas libremente.
 * @param {string} tbodyId - ID del tbody (mantenido para compatibilidad)
 */
function syncClassOptions(tbodyId) {
    // Lógica de exclusión desactivada por requerimiento.
    // Todas las opciones quedan visibles en cada select.
}

// ── Modales de confirmación (ELIMINADOS) ──

/**
 * Construye el payload de una forma a partir del tbody y los campos del form.
 */
function buildPayload(tbodyId, formIds) {
    const rows = [];
    const tbody = document.getElementById(tbodyId);

    for (let i = 0; i < tbody.rows.length; i++) {
        const row = tbody.rows[i];
        const classSelect = row.querySelector('[name="id_clase[]"]');
        const tipoSelect = row.querySelector('[name="tipo_modelo[]"]');
        const rawClassName = classSelect.options[classSelect.selectedIndex].text;
        const tipoModelo = tipoSelect ? tipoSelect.value : '';

        // Si el tipo es Templadera, usar ese prefijo en la descripción; si no, usar "Modelo"
        const prefijo = tipoModelo === 'Templadera' ? 'Templadera' : 'Modelo';
        const claseNombreFinal = rawClassName.startsWith(prefijo + ' ')
            ? rawClassName
            : `${prefijo} ${rawClassName}`;

        rows.push({
            tipo_modelo: tipoModelo,
            impresiones: row.querySelector('[name="impresiones[]"]').value,
            cantidad: row.querySelector('[name="cantidad[]"]').value,
            id_clase: classSelect.value,
            clase_nombre: claseNombreFinal,
            codigo_modelo: row.querySelector('[name="codigo_modelo[]"]').value,
        });
    }

    // Limpiar OT: solo el número (quitar prefijos "OT ", espacios, etc.)
    const otRaw = document.getElementById(formIds.ot).value;
    const otClean = otRaw.replace(/[^0-9]/g, '') || otRaw;

    return {
        proveedor: document.getElementById(formIds.proveedor).value,
        fecha_creacion: document.getElementById(formIds.fecha_creacion).value,
        folio: document.getElementById(formIds.folio).value,
        ot: otClean,
        ot_raw: document.getElementById('po-ot-raw').value,
        moldura: document.getElementById(formIds.moldura).value,
        fecha_entrega: '', // Se deja vacío para llenado manual del proveedor
        observaciones: document.getElementById(formIds.observaciones).value,
        filas: rows
    };
}

/**
 * Dispara el fetch de guardado de una pre-orden y maneja la respuesta.
 */
function submitPreOrden(payload, btn, originalText, onSuccess) {
    fetch(window.almacenRoutes.storePreOrden, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
    })
        .then(async res => {
            const contentType = res.headers.get('content-type');
            if (contentType && contentType.includes('application/pdf')) {
                const blob = await res.blob();
                // Intentar extraer el nombre del header Content-Disposition
                const disposition = res.headers.get('Content-Disposition');
                let filename = '';
                if (disposition && disposition.indexOf('attachment') !== -1) {
                    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, '');
                    }
                }
                return { blob, filename };
            }
            return res.json();
        })
        .then(data => {
            if (data.blob) {
                const url = window.URL.createObjectURL(data.blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = data.filename || `PreOrden_${payload.ot.replace(/[^a-z0-9]/gi, '_')}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                mostrarToast('Pre-orden generada y descargada correctamente.');
                cerrarModalPreOrden();
                
                // Recargar para actualizar los estados y la lista de archivos
                setTimeout(() => { window.location.reload(); }, 1500);
                if (onSuccess) onSuccess(data);
            } else if (data.success) {
                mostrarToast(data.message + '. Actualizando...');
                setTimeout(() => { window.location.reload(); }, 1500);
                if (onSuccess) onSuccess();
            } else {
                mostrarToast(data.message || 'Error al procesar Pre-Orden', true);
            }
        })
        .catch(err => {
            console.error(err);
            mostrarToast('Error al procesar la solicitud', true);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
}

// ── Envío Pre-Orden 1 ──

document.getElementById('formPreOrden').addEventListener('submit', function (e) {
    e.preventDefault();

    const btn = document.getElementById('btn-submit-preorden');
    const originalText = btn.innerText;

    btn.disabled = true;
    btn.innerText = 'Procesando...';

    const payload = buildPayload('alm-tbody-preorden', {
        proveedor: 'po-proveedor',
        fecha_creacion: 'po-fecha',
        folio: 'po-folio',
        ot: 'po-ot',
        moldura: 'po-moldura',
        fecha_entrega: 'po-fecha-entrega',
        observaciones: 'po-observaciones'
    });

    submitPreOrden(payload, btn, originalText, () => {
        cerrarModalPreOrden();
    });
});

// ── Envío Pre-Orden 2 (ELIMINADO) ──

/**
 * Actualiza el icono de estado de modelo en la tabla principal (DOM)
 */
function updateModelStatusUI(ot, status) {
    const container = document.getElementById(`status-modelo-${ot}`);
    if (!container) return;

    if (status === 'pendiente') {
        container.innerHTML = `
            <span class="badge-modelo-pending" title="Pre-orden enviada (Pendiente)">
                <img src="/images/caducado.png" alt="Pendiente" style="width: 35px; height: 35px;">
            </span>
        `;
    } else if (status === 'ok') {
        container.innerHTML = `
            <span class="badge-modelo-ok" title="Modelo disponible">
                <img src="/images/aprobado.png" alt="OK" style="width: 35px; height: 35px;">
            </span>
        `;
    }
}

// ── FASE 2: ENVÍO DE CORREO ──

let adicionalesSelectedFiles = [];

window.abrirModalEnviarPreOrden = function (ot) {
    const modal = document.getElementById('modalEnviarPreOrden');
    const inputOt = document.getElementById('env-ot');
    const filesContainer = document.getElementById('env-server-files-container');

    inputOt.value = ot;

    // Reset file inputs and badges
    adicionalesSelectedFiles = [];
    renderSelectedFilesBadges();

    // Limpiar contenedor de archivos y mostrar cargando
    filesContainer.innerHTML = `
        <div style="text-align: center; padding: 10px;">
            <div class="alm-spinner" style="border-top-color: #033966; display: inline-block;"></div>
            <span style="color: #64748b; margin-left: 10px;">Obteniendo archivos del servidor...</span>
        </div>
    `;

    modal.classList.add('open');
    document.body.classList.add('modal-open');

    // Obtener los archivos de la OT desde el backend (pre-órdenes, dibujos y ayudas visuales)
    fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
        .then(res => res.json())
        .then(data => {
            // Prellenar la fecha de entrega si ya existe en la pre-orden
            const inputFecha = document.getElementById('env-fecha-entrega');
            if (inputFecha) {
                inputFecha.value = data.fecha_entrega || '';
            }

            if (data.existe && data.archivos && data.archivos.length > 0) {
                let baseUrl = window.baseUrl || (window.location.origin + '/');
                if (!baseUrl.endsWith('/')) baseUrl += '/';

                filesContainer.innerHTML = data.archivos.map((file, index) => {
                    const checkedAttr = 'checked';
                    const dispName = file.nombre.split('/').pop();
                    return `
                        <div class="dibujos-file-card card-ayuda select-file-card checked-card" style="position: relative; animation-delay: ${index * 0.05}s; width: 100%; max-width: 220px; display: inline-flex; flex-direction: column; align-items: center; text-align: center; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); box-sizing: border-box;">
                            <!-- Checkbox overlay -->
                            <div style="position: absolute; top: 10px; left: 10px; z-index: 10;">
                                <input type="checkbox" name="archivos_seleccionados[]" value="${file.nombre}" ${checkedAttr} style="width: 20px; height: 20px; cursor: pointer;" onchange="this.closest('.select-file-card').classList.toggle('checked-card', this.checked);">
                            </div>

                            <div class="file-icon-wrapper" onclick="almacenVerPdf('${ot}', '${file.nombre}', '${file.tipo}')" style="cursor: pointer; margin-top: 10px;" title="Abrir PDF">
                                <img src="${baseUrl}images/pdf-view-shadow.png" class="file-icon icon-default">
                                <img src="${baseUrl}images/pdf-view.png" class="file-icon icon-hover">
                            </div>
                            <div class="file-name" style="cursor: pointer; font-size: 0.85em; margin: 8px 0; max-height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; font-weight: 600; color: #334155; line-height: 1.3;" title="Abrir PDF" onclick="almacenVerPdf('${ot}', '${file.nombre}', '${file.tipo}')">
                                ${dispName}
                            </div>
                            <div class="file-actions" style="width: 100%; margin-top: auto;">
                                <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color" onclick="almacenVerPdf('${ot}', '${file.nombre}', '${file.tipo}')">Ver</button>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                filesContainer.innerHTML = `
                    <div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">
                        No se encontraron archivos en el servidor para esta OT.
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error(err);
            filesContainer.innerHTML = `
                <div style="text-align: center; color: #ef4444; padding: 15px; font-weight: 600;">
                    Error al cargar la lista de archivos.
                </div>
            `;
        });
};

window.cerrarModalEnviarPreOrden = function () {
    const modal = document.getElementById('modalEnviarPreOrden');
    modal.classList.remove('open');
    document.body.classList.remove('modal-open');
    document.getElementById('formEnviarPreOrden').reset();
    adicionalesSelectedFiles = [];
    renderSelectedFilesBadges();
};

document.getElementById('formEnviarPreOrden').addEventListener('submit', function (e) {
    e.preventDefault();

    const fecha = document.getElementById('env-fecha-entrega').value;
    if (!fecha) {
        mostrarToast('Por favor, indica la fecha de entrega acordada.', true);
        return;
    }

    if (adicionalesSelectedFiles.length === 0) {
        mostrarToast('Por favor, adjunta al menos un archivo escaneado desde tu equipo.', true);
        return;
    }

    const btn = document.getElementById('btn-submit-envio');
    const originalText = btn.innerText;

    btn.disabled = true;
    btn.innerText = 'Enviando correo...';

    const formData = new FormData(this);

    // Replace native files with custom selection array
    formData.delete('archivos_adicionales[]');
    adicionalesSelectedFiles.forEach(file => {
        formData.append('archivos_adicionales[]', file);
    });

    fetch(window.almacenRoutes.sendEmailPreOrden, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarToast(data.message);
                cerrarModalEnviarPreOrden();
                const ot = document.getElementById('env-ot').value;
                if (window.ModeloStateMachine) {
                    window.ModeloStateMachine.onCorreoEnviado(ot);
                }
            } else {
                mostrarToast(data.message || 'Error al enviar el correo.', true);
            }
        })
        .catch(err => {
            console.error(err);
            mostrarToast('Error de conexión al enviar el correo.', true);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
});

function initCustomFileInputs() {
    const input = document.getElementById('env-archivos-adicionales');
    if (!input) return;

    input.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            Array.from(this.files).forEach(file => {
                const alreadyExists = adicionalesSelectedFiles.some(f => f.name === file.name && f.size === file.size);
                if (!alreadyExists) {
                    adicionalesSelectedFiles.push(file);
                }
            });
        }
        renderSelectedFilesBadges();
        this.value = ''; // Reset input to allow re-selection
    });
}

function renderSelectedFilesBadges() {
    const listContainer = document.getElementById('env-archivos-adicionales-list');
    if (!listContainer) return;

    listContainer.innerHTML = '';
    adicionalesSelectedFiles.forEach((file, index) => {
        const badge = document.createElement('span');
        badge.className = 'file-badge';
        badge.style.display = 'inline-flex';
        badge.style.alignItems = 'center';
        badge.style.gap = '6px';
        badge.innerHTML = `
            📄 ${file.name} (${(file.size / 1024).toFixed(1)} KB)
            <button type="button" class="remove-file-badge-btn" style="background: none; border: none; color: #9c0300; font-weight: bold; cursor: pointer; padding: 0 4px; font-size: 1.2em; line-height: 1; display: flex; align-items: center;" onclick="removeSelectedAttachment(${index})">&times;</button>
        `;
        listContainer.appendChild(badge);
    });
}

window.removeSelectedAttachment = function (index) {
    adicionalesSelectedFiles.splice(index, 1);
    renderSelectedFilesBadges();
};

// ── MODAL LIBERACION DE MODELOS (Calidad) — F-CCL-LDM ────────────────────────

/**
 * Mapa de visibilidad de tablas por tipo de modelo.
 * lib-tabla-1    => Macho/Hembra A-G2 (Molde, Bombillo)
 * lib-tabla-2    => Matriz V,W,X,Y,Z  (Molde, Bombillo, Obturador)
 * lib-tabla-fondo=> Fondo
 */
const LIB_TABLA_MAP = {
    'Fondo':     ['lib-tabla-fondo'],
    'Obturador': ['lib-tabla-obturador'],
    'Molde':     ['lib-tabla-1', 'lib-tabla-2'],
    'Bombillo':  ['lib-tabla-1', 'lib-tabla-2'],
};

const LIB_TODAS_TABLAS = ['lib-tabla-1', 'lib-tabla-2', 'lib-tabla-fondo', 'lib-tabla-obturador'];

let _libTipo = 'aprobar';
let _libOt   = '';

// ── Apertura del modal ────────────────────────────────────────────────────────

/**
 * Abre el modal de Liberacion de Modelos configurado para aprobar o rechazar.
 *
 * @param {string} ot    - Nombre completo de la OT
 * @param {string} tipo  - 'aprobar' | 'rechazar'
 */
window.abrirModalLiberacion = function (ot, tipo) {
    _libTipo = tipo;
    _libOt   = ot;

    const modal        = document.getElementById('modalLiberacionModelo');
    const header       = document.getElementById('lib-modal-header');
    const title        = document.getElementById('lib-modal-title-text') || document.getElementById('lib-modal-title');
    const subtitle     = document.getElementById('lib-modal-subtitle');
    const rechazoBlock = document.getElementById('lib-rechazo-block');
    const actionsEl    = document.getElementById('lib-actions');
    const hiddenOt     = document.getElementById('lib-ot');
    const hiddenAccion = document.getElementById('lib-accion');
    const otDisplay    = document.getElementById('lib-ot-display');

    if (!modal) return;

    // Resetear formulario para no conservar datos previos
    const formEl = document.getElementById('formLiberacion');
    if (formEl) formEl.reset();

    // Mostrar OT en la cabecera del formato
    if (otDisplay) otDisplay.textContent = ot;

    // Configurar apariencia segun tipo de accion
    const esRechazo = tipo === 'rechazar';

    if (esRechazo) {
        header.classList.add('lib-modal-header-rechazo');
        if (title)    title.textContent    = 'Formato de Rechazo de Modelo — F-CCL-LDM';
        if (subtitle) subtitle.textContent = `OT: ${ot}  |  Modo: Rechazo`;
        if (rechazoBlock) rechazoBlock.style.display = '';
    } else {
        header.classList.remove('lib-modal-header-rechazo');
        if (title)    title.textContent    = 'Formato de Liberacion de Modelos — F-CCL-LDM';
        if (subtitle) subtitle.textContent = `OT: ${ot}  |  Modo: Aprobacion`;
        if (rechazoBlock) rechazoBlock.style.display = 'none';
    }

    // Renderizar botones de accion con imagen Liberar.png
    const imgBase = window.almacenAppAssets?.liberar ?? '/images/Liberar.png';
    if (actionsEl) {
        if (esRechazo) {
            actionsEl.innerHTML = `
                <button type="button" class="btn-lib-save" id="lib-btn-guardar">
                    <img src="${window.almacenAppAssets?.descarga ?? '/images/Descarga.png'}" alt="">
                    Guardar y Descargar PDF
                </button>
                <button type="button" class="btn-lib-rechazar-send" id="lib-btn-accion">
                    <img src="${imgBase}" alt="">
                    Rechazar y Enviar Alerta
                </button>
            `;
        } else {
            actionsEl.innerHTML = `
                <button type="button" class="btn-lib-save" id="lib-btn-guardar">
                    <img src="${window.almacenAppAssets?.descarga ?? '/images/Descarga.png'}" alt="">
                    Guardar y Descargar PDF
                </button>
                <button type="button" class="btn-lib-aprobar-send" id="lib-btn-accion">
                    <img src="${imgBase}" alt="">
                    Aprobar y Notificar
                </button>
            `;
        }

        // Asignar eventos a los botones recien creados
        document.getElementById('lib-btn-guardar')
            ?.addEventListener('click', () => _libSubmit('guardar'));
        document.getElementById('lib-btn-accion')
            ?.addEventListener('click', () => _libSubmit(esRechazo ? 'rechazar' : 'aprobar'));
    }

    if (hiddenOt)     hiddenOt.value     = ot;
    if (hiddenAccion) hiddenAccion.value = esRechazo ? 'rechazar' : 'aprobar';

    // Abrir modal
    modal.classList.add('open');
    document.body.classList.add('modal-open');

    // Inicializar la lupa para las imagenes
    _libInicializarZoom();

    // Pre-cargar datos existentes del backend
    _libCargarDatos(ot);
};

// ── Cierre del modal ──────────────────────────────────────────────────────────

window.cerrarModalLiberacion = function () {
    const modal = document.getElementById('modalLiberacionModelo');
    if (modal) modal.classList.remove('open');
    document.body.classList.remove('modal-open');
    // Ocultar zoom si quedara visible
    const zoomEl = document.getElementById('lib-zoom-result');
    if (zoomEl) zoomEl.style.display = 'none';
};

/**
 * Actualiza dinamicamente el badge de estado en la columna "Modelo" de la tabla
 * sin necesidad de recargar la pagina. Se ejecuta tras un guardado parcial (borrador).
 *
 * @param {string} ot          - Identificador exacto de la OT
 * @param {string} nuevoEstado - 'pendiente' | 'aprobado' | 'rechazado'
 */
function _libActualizarBadgeEstado(ot, nuevoEstado) {
    const container = document.getElementById(`status-modelo-${ot}`);
    if (!container) return;

    const assets = window.almacenAppAssets ?? {};
    const imgMap = {
        pendiente : { src: assets.guardado  ?? '/images/Guardado.png',  alt: 'Guardado (Borrador)', cls: 'badge-modelo-guardado',  title: 'Datos capturados por Calidad (borrador)' },
        aprobado  : { src: assets.aprobado  ?? '/images/aprobado.png',  alt: 'Aprobado',            cls: 'badge-modelo-ok',       title: 'Modelo liberado y aprobado por Calidad' },
        rechazado : { src: assets.rechazado ?? '/images/Rechazado.png', alt: 'Rechazado',           cls: 'badge-modelo-rechazado', title: 'Modelo rechazado por Calidad' },
    };

    const cfg = imgMap[nuevoEstado];
    if (!cfg) return;

    container.innerHTML = `
        <span class="${cfg.cls}" title="${cfg.title}">
            <img src="${cfg.src}" alt="${cfg.alt}" style="width:38px;height:38px;">
        </span>
    `;
}

// Cerrar al hacer clic en el backdrop
document.addEventListener('click', (e) => {
    if (e.target.id === 'modalLiberacionModelo') cerrarModalLiberacion();
});

// Cerrar lightbox con Escape, cerrar modal con Escape si lightbox cerrado
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    const lb = document.getElementById('lib-lightbox');
    if (lb && lb.classList.contains('open')) {
        libCerrarLightbox();
    } else {
        cerrarModalLiberacion();
    }
});

// ── Cambio dinamico de tabla segun tipo seleccionado ─────────────────────────

/**
 * Muestra u oculta las tablas segun el tipo de modelo elegido en el select.
 * Los campos de las tablas ocultas se marcan con data-lib-hidden="1" para
 * ser llenados con 0.000 antes del submit.
 *
 * @param {string} tipo - Valor seleccionado en #lib-tipo
 */
window.libCambiarTipo = function (tipo) {
    const aviso    = document.getElementById('lib-tabla-aviso');
    const visibles = LIB_TABLA_MAP[tipo] ?? [];

    // Resetear formulario para evitar cruce de datos entre "Molde" y "Bombillo"
    const form = document.getElementById('formLiberacion');
    const currOt = document.getElementById('lib-ot')?.value;
    const currAcc = document.getElementById('lib-accion')?.value;
    if (form) form.reset();
    
    // Restaurar meta datos despues de limpiar
    if (document.getElementById('lib-ot')) document.getElementById('lib-ot').value = currOt;
    if (document.getElementById('lib-accion')) document.getElementById('lib-accion').value = currAcc;
    if (document.getElementById('lib-tipo')) document.getElementById('lib-tipo').value = tipo;

    LIB_TODAS_TABLAS.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        const activo = visibles.includes(id);
        el.style.display = activo ? '' : 'none';
        // Marcar inputs ocultos para el zero-fill en submit
        el.querySelectorAll('input[type="number"]').forEach(inp => {
            inp.dataset.libHidden = activo ? '0' : '1';
        });
    });

    if (aviso) aviso.style.display = visibles.length > 0 ? 'none' : '';

    // Si tenemos registros cacheados especificos para este tipo, poblamos la UI
    if (tipo && window.cacheLiberacionGlobal && window.cacheLiberacionGlobal[tipo]) {
        _libRellenarInputs(window.cacheLiberacionGlobal[tipo]);
    }
};

// ── Lightbox de imagenes ──────────────────────────────────────────────────────

/**
 * Abre el lightbox con la imagen del wrapper clicado.
 * @param {HTMLElement} wrapper - div.lib-img-zoom-wrapper
 */
window.libAbrirLightbox = function (wrapper) {
    const lb      = document.getElementById('lib-lightbox');
    const lbImg   = document.getElementById('lib-lightbox-img');
    const lbCap   = document.getElementById('lib-lightbox-caption');
    const img     = wrapper.querySelector('.lib-ref-img');

    if (!lb || !lbImg || !img) return;

    lbImg.src = wrapper.dataset.src || img.src;
    lbImg.alt = wrapper.dataset.label || img.alt;
    if (lbCap) lbCap.textContent = wrapper.dataset.label || '';

    lb.classList.add('open');
    document.body.classList.add('modal-open');
};

/**
 * Cierra el lightbox de imagen ampliada.
 */
window.libCerrarLightbox = function () {
    const lb = document.getElementById('lib-lightbox');
    if (lb) lb.classList.remove('open');
};

// ── Zoom tipo lupa (magnifying glass) ────────────────────────────────────────

/**
 * Inicializa el efecto de lupa para todas las imagenes de referencia del modal.
 * Se llama una sola vez al abrir el modal; usa delegacion para evitar
 * registros duplicados si el modal se abre varias veces.
 */
let _libZoomInit = false;

function _libInicializarZoom() {
    if (_libZoomInit) return;
    _libZoomInit = true;

    const zoomResult = document.getElementById('lib-zoom-result');
    if (!zoomResult) return;

    // Tamano del recuadro de zoom y factor de ampliacion
    const ZOOM_SIZE  = 450;
    const ZOOM_RATIO = 3.2;

    document.addEventListener('mousemove', (e) => {
        const wrapper = e.target.closest('.lib-img-zoom-wrapper');
        if (!wrapper) {
            zoomResult.style.display = 'none';
            return;
        }

        // Solo activar si el modal de liberacion esta abierto
        const modal = document.getElementById('modalLiberacionModelo');
        if (!modal || !modal.classList.contains('open')) {
            zoomResult.style.display = 'none';
            return;
        }

        const img  = wrapper.querySelector('.lib-ref-img');
        if (!img || !img.complete) return;

        const rect = img.getBoundingClientRect();
        const x    = e.clientX - rect.left;
        const y    = e.clientY - rect.top;

        // Ignorar si el cursor esta fuera de los limites de la imagen
        if (x < 0 || y < 0 || x > rect.width || y > rect.height) {
            zoomResult.style.display = 'none';
            return;
        }

        // Calcular posicion del background para el recuadro de zoom
        const bgX = -((x * ZOOM_RATIO) - ZOOM_SIZE / 2);
        const bgY = -((y * ZOOM_RATIO) - ZOOM_SIZE / 2);

        zoomResult.style.display         = 'block';
        zoomResult.style.backgroundImage = `url(${img.src})`;
        zoomResult.style.backgroundSize  = `${rect.width * ZOOM_RATIO}px ${rect.height * ZOOM_RATIO}px`;
        zoomResult.style.backgroundPosition = `${bgX}px ${bgY}px`;
        zoomResult.style.width  = `${ZOOM_SIZE}px`;
        zoomResult.style.height = `${ZOOM_SIZE}px`;

        // Posicionar el recuadro cerca del cursor, evitando que salga de pantalla
        const offsetX = 24;
        const offsetY = -ZOOM_SIZE / 2;
        let posX = e.clientX + offsetX;
        let posY = e.clientY + offsetY;

        if (posX + ZOOM_SIZE > window.innerWidth - 10) posX = e.clientX - ZOOM_SIZE - offsetX;
        if (posY < 10) posY = 10;
        if (posY + ZOOM_SIZE > window.innerHeight - 10) posY = window.innerHeight - ZOOM_SIZE - 10;

        zoomResult.style.left = `${posX}px`;
        zoomResult.style.top  = `${posY}px`;
    });

    document.addEventListener('mouseleave', () => {
        zoomResult.style.display = 'none';
    }, true);
}

// ── Carga de datos existentes desde el backend ────────────────────────────────

window.cacheLiberacionGlobal = {};

/**
 * Consulta la API y pre-llena el formulario con datos guardados previamente.
 * Estructura medidas_plantilla: claves en formato "{row}_{col}" (ej: "plantilla_V", "templadera_x1").
 */
async function _libCargarDatos(ot) {
    if (!window.almacenRoutes?.getLiberacion) return;

    try {
        const url  = `${window.almacenRoutes.getLiberacion}?ot=${encodeURIComponent(ot)}`;
        const resp = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await resp.json();
        
        // Guardamos el cache completo de registros independientes
        window.cacheLiberacionGlobal = data.registros_por_tipo || {};
        
        if (!data.success) return;

        const lastLib = data.liberacion;

        // Pre-seleccionar tipo y actualizar visibilidad de tablas (esto desencadenara libCambiarTipo y rellenara inputs)
        const selectTipo = document.getElementById('lib-tipo');
        if (selectTipo && lastLib && lastLib.tipo_modelo) {
            selectTipo.value = lastLib.tipo_modelo;
            libCambiarTipo(lastLib.tipo_modelo);
        }
    } catch (err) {
        console.error('Error al cargar datos de liberacion:', err);
    }
}

/**
 * Rellena los inputs con un objeto "lib" especifico de un tipo de modelo.
 */
function _libRellenarInputs(lib) {
    if (!lib) return;

    try {
        // Modelo (Macho/Hembra): id = lib-modelo-{ITEM}-{col}
        if (lib.medidas_modelo && typeof lib.medidas_modelo === 'object') {
            Object.entries(lib.medidas_modelo).forEach(([item, cols]) => {
                if (!cols) return;
                ['dibujo', 'macho', 'hembra'].forEach(col => {
                    const inp = document.getElementById(`lib-modelo-${item}-${col}`);
                    if (inp && cols[col] != null) inp.value = cols[col];
                });
            });
        }

        // Plantilla/Templadera (Matriz): clave = "{row}_{col}", id = lib-plt-{row}-{col}-{dim}
        if (lib.medidas_plantilla && typeof lib.medidas_plantilla === 'object') {
            Object.entries(lib.medidas_plantilla).forEach(([key, cols]) => {
                if (!cols) return;
                // key formato: "plantilla_V", "templadera_x1", etc.
                const m = key.match(/^(plantilla|templadera)_(.+)$/);
                if (!m) return;
                const [, row, col] = m;
                ['dibujo', 'fisico'].forEach(dim => {
                    const inp = document.getElementById(`lib-plt-${row}-${col}-${dim}`);
                    if (inp && cols[dim] != null) inp.value = cols[dim];
                });
            });
        }

        // Fondo: id = lib-fondo-{key}-{dim}
        if (lib.medidas_fondo && typeof lib.medidas_fondo === 'object') {
            Object.entries(lib.medidas_fondo).forEach(([item, cols]) => {
                if (!cols) return;
                ['dibujo', 'fisico'].forEach(dim => {
                    const inp = document.getElementById(`lib-fondo-${item}-${dim}`);
                    if (inp && cols[dim] != null) inp.value = cols[dim];
                });
            });
        }

        // Obturador: id = lib-obturador-{key}-{dim}
        if (lib.medidas_obturador && typeof lib.medidas_obturador === 'object') {
            Object.entries(lib.medidas_obturador).forEach(([item, cols]) => {
                if (!cols) return;
                ['dibujo', 'fisico'].forEach(dim => {
                    const inp = document.getElementById(`lib-obturador-${item}-${dim}`);
                    if (inp && cols[dim] != null) inp.value = cols[dim];
                });
            });
        }

        const obsModelo    = document.getElementById('lib-obs-modelo');
        const obsPlantilla = document.getElementById('lib-obs-plantilla');
        const obsFondo     = document.getElementById('lib-obs-fondo');
        const obsObturador = document.getElementById('lib-obs-obturador');
        const rechEl       = document.getElementById('lib-motivo-rechazo');

        if (obsModelo)    obsModelo.value    = lib.observaciones_modelo || '';
        if (obsPlantilla) obsPlantilla.value = lib.observaciones_plantilla || '';
        if (obsFondo)     obsFondo.value     = lib.observaciones_fondo || '';
        if (obsObturador) obsObturador.value = lib.observaciones_obturador || '';
        if (rechEl)       rechEl.value       = lib.motivo_rechazo || '';

        // Truncar y formatear todos los campos numericos despues de cargar
        document.querySelectorAll('.lib-num-input, .lib-num-input-sm').forEach(inp => {
            formatInputTruncated(inp);
        });

    } catch (err) {
        console.error('Error al rellenar inputs de liberacion:', err);
    }
}

// ── Envio del formulario ──────────────────────────────────────────────────────

/**
 * Antes del submit, rellena con 0.000 todos los inputs de tablas ocultas
 * para garantizar consistencia en la base de datos.
 */
function _libZeroFillOcultos() {
    document.querySelectorAll('input[data-lib-hidden="1"]').forEach(inp => {
        inp.value = '0.000';
    });

    // Limpiar observaciones de las tablas ocultas
    const t1 = document.getElementById('lib-tabla-1');
    if (t1 && t1.style.display === 'none') {
        const obs = document.getElementById('lib-obs-modelo');
        if (obs) obs.value = '';
    }
    const t2 = document.getElementById('lib-tabla-2');
    if (t2 && t2.style.display === 'none') {
        const obs = document.getElementById('lib-obs-plantilla');
        if (obs) obs.value = '';
    }
    const tf = document.getElementById('lib-tabla-fondo');
    if (tf && tf.style.display === 'none') {
        const obs = document.getElementById('lib-obs-fondo');
        if (obs) obs.value = '';
    }
    const to = document.getElementById('lib-tabla-obturador');
    if (to && to.style.display === 'none') {
        const obs = document.getElementById('lib-obs-obturador');
        if (obs) obs.value = '';
    }
}

/**
 * Envia el formulario de liberacion al backend.
 *
 * @param {'guardar'|'aprobar'|'rechazar'} accion
 */
async function _libSubmit(accion) {
    const ot = document.getElementById('lib-ot')?.value;
    if (!ot) return;

    // Validacion del select tipo
    const tipoVal = document.getElementById('lib-tipo')?.value;
    if (!tipoVal) {
        almacenToast('Selecciona el Tipo de Modelo antes de continuar.', 'error');
        return;
    }

    // Validacion obligatoria de motivo de rechazo
    if (accion === 'rechazar') {
        const motivo = document.getElementById('lib-motivo-rechazo')?.value?.trim();
        if (!motivo) {
            almacenToast('El campo "Motivo de Rechazo" es obligatorio para rechazar la liberacion.', 'error');
            document.getElementById('lib-motivo-rechazo')?.focus();
            return;
        }
    }

    // Rellenar con 0.000 los campos de tablas que no aplican al tipo seleccionado
    _libZeroFillOcultos();

    // Truncar y formatear todos los inputs a 3 decimales
    document.querySelectorAll('.lib-num-input, .lib-num-input-sm').forEach(inp => {
        formatInputTruncated(inp);
    });

    const form = document.getElementById('formLiberacion');
    const fd   = new FormData(form);
    fd.set('accion', accion);
    fd.set('ot', ot);

    // Bloquear botones durante la peticion
    const btns = document.querySelectorAll('#lib-actions button');
    btns.forEach(b => { b.disabled = true; });

    try {
        const resp = await fetch(window.almacenRoutes.submitLiberacion, {
            method : 'POST',
            headers: {
                'Accept'          : 'application/json',
                'X-CSRF-TOKEN'    : document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: fd,
        });

        const data = await resp.json();

        if (data.success) {
            almacenToast(data.message, 'success');

            // Descargar PDF automaticamente con nombre estetico
            if (data.pdf_url) {
                const enlace    = document.createElement('a');
                enlace.href     = data.pdf_url;
                enlace.download = data.pdf_filename ?? 'Liberacion_Modelos.pdf';
                enlace.style.display = 'none';
                document.body.appendChild(enlace);
                enlace.click();
                document.body.removeChild(enlace);
            }

            if (accion === 'guardar') {
                // Actualizar badge de estado en tabla sin recargar pagina
                if (data.ot && data.nuevo_estado) {
                    _libActualizarBadgeEstado(data.ot, data.nuevo_estado);
                }
            } else {
                // ── Máquina de estados: disparar evento de liberación final ──
                // Permite que el state machine muestre el estado final (aprobado/rechazado)
                // antes del page reload, para que el usuario vea el feedback visual.
                const otFinal = data.ot || ot;
                document.dispatchEvent(new CustomEvent('modeloLiberado', {
                    detail: { ot: otFinal, accion }
                }));

                setTimeout(() => {
                    cerrarModalLiberacion();
                    window.location.reload();
                }, 1800);
            }
        } else {
            almacenToast(data.message || 'Ocurrio un error inesperado.', 'error');
        }
    } catch (err) {
        console.error('Error al enviar liberacion:', err);
        almacenToast('Error de red al enviar el formulario.', 'error');
    } finally {
        btns.forEach(b => { b.disabled = false; });
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// ── MÁQUINA DE ESTADOS VISUAL — Estado del Modelo  (v4 — FSM Completa) ─────────
// ═══════════════════════════════════════════════════════════════════════════════
/**
 * ModeloStateMachine (v4)
 * ─────────────────────────────────────────────────────────────────────────────
 * FSM de 8 estados exactos en 3 niveles jerárquicos.
 *
 * REGLA DE ORO: Una vez alcanzado un nivel, los estados de nivel inferior
 * son ignorados. La transición solo avanza, nunca retrocede.
 *
 * ┌───────┬────────────┬──────────────┬──────────────────────────────────────┐
 * │ NIVEL │ Estado     │ Imagen       │ Disparador                           │
 * ├───────┼────────────┼──────────────┼──────────────────────────────────────┤
 * │   1   │ recibido   │ Recibido.png │ Alerta inicial del servidor          │
 * │   1   │ revisando  │ Revisando.png│ Clic en "Ver Archivos"               │
 * │   1   │ editando   │ Editando.png │ Clic en "Aprobar/Rechazar Lib."      │
 * ├───────┼────────────┼──────────────┼──────────────────────────────────────┤
 * │   2   │ guardado   │ Guardado.png │ Clic en "Guardar"                    │
 * │   2   │ descargado │ Descarga.png │ PDF generado y descargado            │
 * │   2   │ espera     │ documento.png│ Correo enviado / Dpto. confirmó      │
 * ├───────┼────────────┼──────────────┼──────────────────────────────────────┤
 * │   3   │ aprobado   │ Aprobado.png │ Liberación aprobada (servidor)       │
 * │   3   │ rechazado  │ Rechazado.png│ Liberación rechazada (servidor)      │
 * └───────┴────────────┴──────────────┴──────────────────────────────────────┘
 */
const ModeloStateMachine = (() => {

    function _baseUrl() {
        let b = window.baseUrl || (window.location.origin + '/');
        return b.endsWith('/') ? b : b + '/';
    }

    // ── Registro de estados ───────────────────────────────────────────────────
    const ESTADOS = {
        // ── Nivel 1: Transitorios ──────────────────────────────────────────
        recibido   : { img: 'Recibido.png',  cls: 'badge-modelo-recibido',   nivel: 1, prio:  1, title: 'Recibido — En espera de revisión'              },
        revisando  : { img: 'Revisando.png', cls: 'badge-modelo-revisando',  nivel: 1, prio:  2, title: 'Revisando — Archivos abiertos'                 },
        editando   : { img: 'Editando.png',  cls: 'badge-modelo-editando',   nivel: 1, prio:  3, title: 'Editando — Tomando decisión de liberación'     },
        // ── Nivel 2: Permanentes ───────────────────────────────────────────
        guardado   : { img: 'Guardado.png',  cls: 'badge-modelo-guardado',   nivel: 2, prio:  4, title: 'Guardado — Datos capturados como borrador'     },
        descargado : { img: 'Descarga.png',  cls: 'badge-modelo-descargado', nivel: 2, prio:  5, title: 'Descargado — Reporte PDF generado y descargado' },
        espera     : { img: 'Espera.png',    cls: 'badge-modelo-espera',     nivel: 2, prio:  6, title: 'En Espera — Departamento confirmó, procesando'  },
        // ── Nivel 3: Terminales ────────────────────────────────────────────
        aprobado   : { img: 'aprobado.png',  cls: 'badge-modelo-ok',         nivel: 3, prio: 99, title: 'Aprobado — Modelo liberado por Calidad'         },
        rechazado  : { img: 'Rechazado.png', cls: 'badge-modelo-rechazado',  nivel: 3, prio: 99, title: 'Rechazado — Liberación rechazada por Calidad'   },
        // ── Alias de compatibilidad (backend / v3 legacy) ─────────────────
        pendiente  : { img: 'Guardado.png',  cls: 'badge-modelo-guardado',   nivel: 2, prio:  4, title: 'Guardado — Datos capturados como borrador'      },
        enviando   : { img: 'Espera.png',    cls: 'badge-modelo-espera',     nivel: 2, prio:  6, title: 'En Espera — Departamento confirmó, procesando'  },
        en_proceso : { img: 'Guardado.png',  cls: 'badge-modelo-guardado',   nivel: 2, prio:  4, title: 'Guardado — Datos capturados como borrador'      },
        documento  : { img: 'Espera.png',    cls: 'badge-modelo-espera',     nivel: 2, prio:  6, title: 'En Espera — Departamento confirmó, procesando'  },
    };

    /** Mapa alias → estado canónico para la caché interna */
    const _CANONICAL = {
        pendiente: 'guardado', enviando: 'espera',
        en_proceso: 'guardado', documento: 'espera',
    };

    /** Caché: ot → estado canónico actual */
    const _cache = {};

    // ── Aplicar estado al DOM ─────────────────────────────────────────────────
    function _render(ot, estado, cfg) {
        const el = document.getElementById(`status-modelo-${ot}`);
        if (!el) { console.warn(`[FSM] Contenedor no encontrado: #status-modelo-${ot}`); return; }
        const src = _baseUrl() + 'images/' + cfg.img;
        el.innerHTML = `<span class="${cfg.cls}" title="${cfg.title}"><img src="${src}" alt="${cfg.title}" style="width:38px;height:38px;object-fit:contain;"></span>`;
        console.info(`[FSM] "${ot}": → ${estado} (nivel ${cfg.nivel})`);
    }

    // ── Transición normal (respeta jerarquía) ─────────────────────────────────
    function transicion(ot, estado) {
        const cfg = ESTADOS[estado];
        if (!cfg) { console.warn(`[FSM] Estado desconocido: "${estado}"`); return false; }

        const actual    = _cache[ot];
        const cfgActual = actual ? ESTADOS[actual] : null;

        // Regla 1 — Terminales son permanentes
        if (cfgActual?.nivel === 3) {
            console.info(`[FSM] "${ot}": BLOQUEADO — terminal "${actual}" (→ ${estado})`);
            return false;
        }
        // Regla 2 — No retroceder prioridad
        if (cfg.prio <= (cfgActual?.prio ?? 0)) {
            console.info(`[FSM] "${ot}": BLOQUEADO — retroceso (${actual}[${cfgActual?.prio}] → ${estado}[${cfg.prio}])`);
            return false;
        }

        _cache[ot] = _CANONICAL[estado] ?? estado;
        _render(ot, estado, cfg);
        return true;
    }

    // ── Forzar terminal (solo desde servidor) ─────────────────────────────────
    function _forzarTerminal(ot, estado) {
        const cfg = ESTADOS[estado];
        if (!cfg || cfg.nivel !== 3) { console.warn(`[FSM] _forzarTerminal: "${estado}" no es terminal`); return false; }
        _cache[ot] = estado;
        _render(ot, estado, cfg);
        console.info(`[FSM] "${ot}": TERMINAL FORZADO → ${estado} ★`);
        return true;
    }

    // ── Inicialización desde DOM ──────────────────────────────────────────────
    /** Lee los badges ya renderizados por Blade y sincroniza el caché interno */
    function init() {
        const IMG_TO_ESTADO = {
            'recibido.png'  : 'recibido',   'revisando.png' : 'revisando',
            'editando.png'  : 'editando',   'guardado.png'  : 'guardado',
            'descarga.png'  : 'descargado', 'espera.png'    : 'espera',
            'aprobado.png'  : 'aprobado',   'rechazado.png' : 'rechazado',
            // alias legacy que puedan venir del DOM en versiones anteriores
            'documento.png' : 'espera',     'enviando.png'  : 'espera',
        };
        document.querySelectorAll('[id^="status-modelo-"]').forEach(el => {
            const ot  = el.id.replace('status-modelo-', '');
            const img = el.querySelector('img');
            if (!img || _cache[ot]) return;
            const filename = img.src.split('/').pop().toLowerCase();
            const estado   = IMG_TO_ESTADO[filename];
            if (estado) { _cache[ot] = estado; console.info(`[FSM] init: "${ot}" → ${estado}`); }
        });
    }

    // ── API semántica ─────────────────────────────────────────────────────────
    function getEstado(ot)          { return _cache[ot] ?? null; }
    function getNivel(ot)           { return ESTADOS[_cache[ot]]?.nivel ?? 0; }

    function onAlertaEnviada(ot)    { transicion(ot, 'recibido');    }
    function onVerArchivos(ot)      { transicion(ot, 'revisando');   }
    function onAbrirDecision(ot)    { transicion(ot, 'editando');    }
    function onGuardar(ot)          { transicion(ot, 'guardado');    }
    function onDescargado(ot)       { transicion(ot, 'descargado');  }
    function onCorreoEnviado(ot)    { transicion(ot, 'espera');      }
    function onConfirmarModelo(ot)  { transicion(ot, 'espera');      }
    function onEnEspera(ot)         { transicion(ot, 'espera');      }
    function onAprobado(ot)         { _forzarTerminal(ot, 'aprobado');  }
    function onRechazado(ot)        { _forzarTerminal(ot, 'rechazado'); }

    return {
        transicion, _forzarTerminal, init,
        getEstado, getNivel,
        onAlertaEnviada, onVerArchivos, onAbrirDecision,
        onGuardar, onDescargado, onCorreoEnviado,
        onConfirmarModelo, onEnEspera, onAprobado, onRechazado,
        ESTADOS,
    };
})();

window.ModeloStateMachine = ModeloStateMachine;

// ═══════════════════════════════════════════════════════════════════════════════
// ── HOOKS — Integración con el DOM existente ───────────────────────────────────
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * INIT — Sincroniza el caché con el estado inicial servido por Blade.
 * Se ejecuta en DOMContentLoaded para leer las imágenes ya renderizadas.
 */
document.addEventListener('DOMContentLoaded', () => ModeloStateMachine.init());

/**
 * HOOK 1 — _libActualizarBadgeEstado
 * Puente de compatibilidad con el callback del backend (accion='guardar').
 * pendiente → guardado | aprobado → aprobado | rechazado → rechazado
 */
(function _hookLibBadge() {
    window._libActualizarBadgeEstado = function (ot, nuevoEstado) {
        const mapa = { pendiente: 'guardado', guardado: 'guardado', en_proceso: 'guardado' };
        const estado = mapa[nuevoEstado] ?? nuevoEstado;
        const esTerminal = (nuevoEstado === 'aprobado' || nuevoEstado === 'rechazado');
        if (esTerminal) ModeloStateMachine._forzarTerminal(ot, estado);
        else            ModeloStateMachine.transicion(ot, estado);
    };
})();

/**
 * HOOK 2 — btn-toggle-files → revisando (Nivel 1)
 * Solo si el panel se está ABRIENDO y el nivel actual < 2.
 */
(function _hookToggleFiles() {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-toggle-files');
        if (!btn) return;
        const ot = btn.dataset.ot;
        if (!ot) return;
        // Solo disparar si el nivel actual es < 2 (no sobreescribir permanentes/terminales)
        if (ModeloStateMachine.getNivel(ot) >= 2) return;
        const panel     = document.getElementById(btn.dataset.target);
        const estaAbierto = panel?.classList.contains('open');
        if (!estaAbierto) ModeloStateMachine.onVerArchivos(ot);
    }, true);
})();

/**
 * HOOK 3 — abrirModalLiberacion → editando (Nivel 1)
 * Solo si el nivel actual es < 2.
 */
(function _hookAbrirModal() {
    const _orig = window.abrirModalLiberacion;
    window.abrirModalLiberacion = function (ot, tipo) {
        if (ModeloStateMachine.getNivel(ot) < 2) {
            ModeloStateMachine.onAbrirDecision(ot);
        }
        return _orig.call(this, ot, tipo);
    };
})();

/**
 * HOOK 4 — Botones de #lib-actions (MutationObserver)
 *   lib-btn-guardar  → guardado   (Nivel 2, click inmediato)
 *   lib-btn-accion   → espera     (Nivel 2, correo enviado de forma optimista)
 *
 * El estado definitivo aprobado/rechazado llega por el evento 'modeloLiberado'.
 */
(function _hookLibActions() {
    const obs = new MutationObserver(() => {
        const btnGuardar = document.getElementById('lib-btn-guardar');
        if (btnGuardar && !btnGuardar.dataset.fsmHooked) {
            btnGuardar.dataset.fsmHooked = '1';
            btnGuardar.addEventListener('click', () => {
                const ot = document.getElementById('lib-ot')?.value;
                if (ot) ModeloStateMachine.onGuardar(ot);
            }, true);
        }

        const btnAccion = document.getElementById('lib-btn-accion');
        if (btnAccion && !btnAccion.dataset.fsmHooked) {
            btnAccion.dataset.fsmHooked = '1';
            btnAccion.addEventListener('click', () => {
                const ot = document.getElementById('lib-ot')?.value;
                // Feedback optimista: muestra "espera" mientras el servidor procesa.
                // El evento 'modeloLiberado' sobreescribirá con aprobado/rechazado.
                if (ot) ModeloStateMachine.onCorreoEnviado(ot);
            }, true);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const actionsEl = document.getElementById('lib-actions');
        if (actionsEl) obs.observe(actionsEl, { childList: true });
    });
})();

/**
 * HOOK 5 — URL.createObjectURL (detección de descarga de PDF)
 * Cuando la liberación genera un PDF blob, la prioridad avanza a "descargado".
 * Solo se activa si el modal de liberación (#lib-ot) está activo.
 */
(function _hookPdfDescarga() {
    const _origCreate = URL.createObjectURL;
    URL.createObjectURL = function (blob) {
        const url = _origCreate.call(URL, blob);
        try {
            const ot = document.getElementById('lib-ot')?.value;
            if (ot && blob?.type === 'application/pdf') {
                // Pequeño delay para que el anchor de descarga se procese primero
                setTimeout(() => ModeloStateMachine.onDescargado(ot), 200);
            }
        } catch (_) { /* silenciar errores no relacionados */ }
        return url;
    };
})();

/**
 * HOOK 6 — confirmarModelo (Vista Almacén)
 * Cuando Almacén confirma el modelo físico → espera (Nivel 2).
 */
(function _hookConfirmarModelo() {
    window.confirmarModelo = function (ot, id_hash) {
        if (!confirm(`¿Confirmas que actualmente cuentas con el modelo físico para la OT ${ot}?`)) return;
        fetch(window.almacenRoutes.confirmarModelo, {
            method : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ ot }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostrarToast(data.message);
                ModeloStateMachine.onConfirmarModelo(ot);
                if (id_hash) {
                    const container = document.getElementById('control-modelo-' + id_hash);
                    if (container) {
                        container.style.opacity = '0.5';
                        container.style.pointerEvents = 'none';
                    }
                }
            } else {
                mostrarToast(data.message || 'Error al actualizar estado', true);
            }
        })
        .catch(err => { console.error(err); mostrarToast('Error de conexión', true); });
    };
})();

/**
 * HOOK 7 — evento 'modeloLiberado' (disparado por _libSubmit tras éxito del servidor)
 * Actualiza al estado terminal definitivo (aprobado/rechazado),
 * sobreescribiendo el "espera" provisional del HOOK 4.
 */
(function _hookModeloLiberado() {
    document.addEventListener('modeloLiberado', (e) => {
        const { ot, accion } = e.detail ?? {};
        if (!ot || !accion) return;
        if (accion === 'aprobar')  ModeloStateMachine.onAprobado(ot);
        if (accion === 'rechazar') ModeloStateMachine.onRechazado(ot);
    });
})();
