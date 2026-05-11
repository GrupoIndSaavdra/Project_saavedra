/**
 * almacen_fundicion.js
  * Lógica de la vista de Almacén/Calidad para Dibujos de Fundición.
  */
 console.log('ALMACEN_FUNDICION_JS_V2_LOADED');

document.addEventListener('DOMContentLoaded', () => {
    initToggleFiles();
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

    // Resetear estado multi-orden y ocultar botón añadir
    resetMultiOrderState();
    const btnAdd = document.getElementById('btn-add-clase-po');
    if (btnAdd) btnAdd.style.display = 'none';

    // Separar OT y Moldura si vienen juntas (ej. "6473 - VINERA...")
    let otNum = ot;
    let molduraName = '';
    if (ot.includes(' - ')) {
        const parts = ot.split(' - ');
        otNum = parts[0].trim();
        molduraName = parts.slice(1).join(' - ').trim();
    }

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
function createRowElement(claseNombrePredefinida = '', isSecondOrder = false) {
    const tr = document.createElement('tr');

    let selectedId = '';
    if (claseNombrePredefinida) {
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

    tr.innerHTML = `
        <td>
            <select name="tipo_modelo[]" class="form-control" required>
                <option value="" disabled selected>Selecciona uno</option>
                <option value="Suelto">Suelto</option>
                <option value="Placa">Placa</option>
            </select>
        </td>
        <td>
            <input type="text" name="impresiones[]" class="form-control" style="text-align:center;" placeholder="Ej. 1" required>
        </td>
        <td>
            <input type="number" name="cantidad[]" class="form-control" style="text-align:center;" min="1" placeholder="0" required>
        </td>
        <td>
            <select name="id_clase[]" class="form-control po-clase-select" required onchange="generarCodigoFila(this)">
                ${options}
            </select>
        </td>
        <td>
            <input type="text" name="codigo_modelo[]" class="form-control po-codigo-input" readonly>
        </td>
        <td style="text-align:center; color: #94a3b8; font-size: 0.75em; font-style: italic; vertical-align: middle;">
            (ver cab.)
        </td>
        <td style="text-align:center;">
            <button type="button" class="btn-img-action" onclick="${deleteHandler}" title="Quitar esta clase de la lista">
                <img src="${window.baseUrl || ''}${ (window.baseUrl || '').endsWith('/') ? '' : '/' }images/quitar.png" alt="Quitar" style="width: 30px;">
            </button>
        </td>
    `;

    const select = tr.querySelector('.po-clase-select');
    if (select.value) {
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
        syncClassOptions('alm-tbody-preorden');
        
        // Mostrar botón de añadir si se elimina una fila
        const btnAdd = document.getElementById('btn-add-clase-po');
        if (btnAdd) btnAdd.style.display = 'inline-block';
    } else {
        mostrarToast('Debe haber al menos una clase en la pre-orden', true);
    }
};

// ── Cálculo de códigos ──

/**
 * Función centralizada para calcular el código de modelo
 */
function calculateModelCode(ot, nombreClase) {
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

    return `${sigla}${otNum}`;
}

window.generarCodigoFila = function (select) {
    const row = select.closest('tr');
    const inputCodigo = row.querySelector('.po-codigo-input');
    const ot = document.getElementById('po-ot').value;

    if (!select.value) {
        inputCodigo.value = '';
    } else {
        const nombreClase = select.options[select.selectedIndex].dataset.nombre || select.options[select.selectedIndex].text;
        inputCodigo.value = calculateModelCode(ot, nombreClase);
    }

    // Sincronizar opciones disponibles en toda la tabla
    const tbody = row.closest('tbody');
    if (tbody) syncClassOptions(tbody.id);
};

/**
 * Sincroniza las opciones de todos los selects de un tbody:
 * oculta de cada select las clases ya elegidas en las otras filas.
 * @param {string} tbodyId - ID del tbody ('alm-tbody-preorden' o 'alm-tbody-preorden2')
 */
function syncClassOptions(tbodyId) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;

    const selects = Array.from(tbody.querySelectorAll('.po-clase-select'));

    // Recopilar los valores seleccionados en TODAS las filas
    const selectedValues = selects
        .map(s => s.value)
        .filter(v => v !== '');

    selects.forEach(select => {
        const myValue = select.value;

        // Reconstruir las opciones: mostrar solo las que no están seleccionadas en OTRAS filas
        Array.from(select.options).forEach(option => {
            if (option.value === '') return; // Siempre mostrar el placeholder
            const takenByOther = selectedValues.includes(option.value) && option.value !== myValue;
            option.hidden = takenByOther;
            option.disabled = takenByOther;
        });
    });
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
        const rawClassName = classSelect.options[classSelect.selectedIndex].text;

        rows.push({
            tipo_modelo:  row.querySelector('[name="tipo_modelo[]"]').value,
            impresiones:  row.querySelector('[name="impresiones[]"]').value,
            cantidad:     row.querySelector('[name="cantidad[]"]').value,
            id_clase:     classSelect.value,
            clase_nombre: rawClassName.startsWith('Modelo ') ? rawClassName : `Modelo ${rawClassName}`,
            codigo_modelo: row.querySelector('[name="codigo_modelo[]"]').value,
        });
    }

    // Limpiar OT: solo el número (quitar prefijos "OT ", espacios, etc.)
    const otRaw = document.getElementById(formIds.ot).value;
    const otClean = otRaw.replace(/[^0-9]/g, '') || otRaw;

    return {
        proveedor: document.getElementById(formIds.proveedor).value,
        fecha: document.getElementById(formIds.fecha).value,
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
                mostrarToast('Pre-Orden generada y descargada con éxito');
                if (payload && payload.ot_raw) updateModelStatusUI(payload.ot_raw, 'pendiente');
                if (onSuccess) onSuccess();
            } else if (data.success) {
                mostrarToast(data.message);
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
        fecha: 'po-fecha',
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

