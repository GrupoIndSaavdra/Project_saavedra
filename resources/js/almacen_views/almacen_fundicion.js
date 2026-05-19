/**
 * almacen_fundicion.js
  * Lógica de la vista de Almacén/Calidad para Dibujos de Fundición.
  */
console.log('ALMACEN_FUNDICION_JS_V2_LOADED');

document.addEventListener('DOMContentLoaded', () => {
    initToggleFiles();
    initCustomFileInputs();
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
                mostrarToast('Pre-Orden generada y descargada con éxito. Actualizando bandeja...');
                if (payload && payload.ot_raw) updateModelStatusUI(payload.ot_raw, 'pendiente');
                setTimeout(() => { window.location.reload(); }, 1500);
                if (onSuccess) onSuccess();
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

