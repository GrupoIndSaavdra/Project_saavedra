/**
 * almacen_fundicion.js
 * Lógica de la vista de Almacén/Calidad para Dibujos de Fundición.
 */

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
    
    const iconPath = esError ? '/images/delete.png' : '/images/ready.png';
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
                if (container) {
                    container.innerHTML = `
                    <span class="badge-modelo-ok" title="Modelo disponible">
                        <img src="/images/aprobado.png" alt="OK" style="width: 35px; height: 35px;">
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

/**
 * Clases originales cargadas al abrir el modal (para detectar cuáles se eliminaron).
 * Cada elemento: { claseId, claseNombre }
 */
let originalClasses = [];

const normalizeStr = (str) => {
    if (!str) return '';
    return str.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
};

// ── Helpers para leer el estado actual de la tabla ──

/**
 * Devuelve un array con { claseId, claseNombre } de las filas actuales del tbody indicado.
 */
function getCurrentClasses(tbodyId = 'alm-tbody-preorden') {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return [];
    return Array.from(tbody.rows).map(row => {
        const sel = row.querySelector('.po-clase-select');
        return {
            claseId: sel ? sel.value : '',
            claseNombre: sel ? (sel.options[sel.selectedIndex]?.dataset?.nombre || sel.options[sel.selectedIndex]?.text || '') : ''
        };
    });
}

/**
 * Guarda el estado inicial de las clases al abrir el modal.
 * Debe llamarse después de que JS pueble el tbody.
 */
function captureOriginalClasses() {
    originalClasses = getCurrentClasses('alm-tbody-preorden');
}

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

                // Guardar estado original DESPUÉS de poblar la tabla
                captureOriginalClasses();

            } else {
                mostrarToast('Error al cargar datos de la OT', true);
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

// ── Gestión del estado multi-orden ──

/**
 * Resetea todo el estado relacionado con el flujo de segunda pre-orden.
 */
function resetMultiOrderState() {
    originalClasses = [];

    // Ocultar pestañas y volver a página 1
    document.getElementById('po-tabs-nav').style.display = 'none';
    document.getElementById('po-page-1').style.display = '';
    document.getElementById('po-page-2').style.display = 'none';
    document.getElementById('po-tab-btn-1').classList.add('active');
    document.getElementById('po-tab-btn-2').classList.remove('active');

    // Limpiar el form de la segunda pre-orden
    const form2 = document.getElementById('formPreOrden2');
    if (form2) form2.reset();
    const tbody2 = document.getElementById('alm-tbody-preorden2');
    if (tbody2) tbody2.innerHTML = '';
}

/**
 * Activa el sistema de pestañas y llena la segunda pre-orden con las clases eliminadas.
 * @param {Array} removedClasses - Array de { claseId, claseNombre } que no están en la orden 1.
 */
function activateSecondOrder(removedClasses) {
    // Copiar datos comunes de la OT
    document.getElementById('po2-ot').value = document.getElementById('po-ot').value;
    document.getElementById('po2-moldura').value = document.getElementById('po-moldura').value;
    document.getElementById('po2-fecha').value = document.getElementById('po-fecha').value;
    document.getElementById('po2-fecha-entrega').value = document.getElementById('po-fecha-entrega').value;
    document.getElementById('po2-folio').value = document.getElementById('po-folio').value;

    // Auto-seleccionar el proveedor ALTERNATIVO al de la pre-orden 1
    const sel1 = document.getElementById('po-proveedor');
    const sel2 = document.getElementById('po2-proveedor');
    const selectedVal = sel1.value;
    // Buscar la primera opción válida que no sea la ya seleccionada
    const alternativeOpt = Array.from(sel2.options).find(opt => opt.value !== '' && opt.value !== selectedVal);
    if (alternativeOpt) {
        sel2.value = alternativeOpt.value;
    }

    // Poblar tabla de la segunda pre-orden
    const tbody2 = document.getElementById('alm-tbody-preorden2');
    tbody2.innerHTML = '';
    const fragment = document.createDocumentFragment();

    removedClasses.forEach(({ claseNombre }) => {
        if (claseNombre && claseNombre !== 'Selecciona clase' && claseNombre !== '') {
            fragment.appendChild(createRowElement(claseNombre, true));
        }
    });

    // Si no hay clases recuperables (todas estaban vacías), poner una fila vacía
    if (fragment.childNodes.length === 0) {
        fragment.appendChild(createRowElement('', true));
    }

    tbody2.appendChild(fragment);

    // Mostrar las pestañas y navegar a la 2
    document.getElementById('po-tabs-nav').style.display = 'flex';
    switchPoTab(2);
}

/**
 * Cambia de pestaña en el modal.
 * @param {number} tabNum - 1 o 2
 */
window.switchPoTab = function (tabNum) {
    document.getElementById('po-page-1').style.display = tabNum === 1 ? '' : 'none';
    document.getElementById('po-page-2').style.display = tabNum === 2 ? '' : 'none';
    document.getElementById('po-tab-btn-1').classList.toggle('active', tabNum === 1);
    document.getElementById('po-tab-btn-2').classList.toggle('active', tabNum === 2);
};

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
                <img src="/images/quitar.png" alt="Quitar" style="width: 30px;">
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

window.agregarFilaPreOrden2 = function () {
    document.getElementById('alm-tbody-preorden2').appendChild(createRowElement('', true));
    syncClassOptions('alm-tbody-preorden2');
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

window.eliminarFilaPreOrden2 = function (btn) {
    const row = btn.closest('tr');
    const tbody = document.getElementById('alm-tbody-preorden2');
    if (tbody.rows.length > 1) {
        row.remove();
        syncClassOptions('alm-tbody-preorden2');
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

/**
 * Modal de confirmación premium (reemplaza confirm() nativo).
 * @param {string} title - Título del modal
 * @param {string} message - Cuerpo del mensaje
 * @param {Function} onYes - Callback si el usuario acepta
 * @param {Function} onNo  - Callback si el usuario cancela
 */
function showConfirmModal(title, message, onYes, onNo) {
    const existing = document.getElementById('po-confirm-overlay');
    if (existing) existing.remove();

    let baseUrl = window.baseUrl || (window.location.origin + '/');
    if (!baseUrl.endsWith('/')) baseUrl += '/';

    const overlay = document.createElement('div');
    overlay.id = 'po-confirm-overlay';
    overlay.className = 'alm-modal'; // Usar la misma clase base que el modal principal

    overlay.innerHTML = `
        <div class="alm-modal-content" style="max-width: 900px; margin-top: 7vh;">
            <div class="alm-modal-header">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" id="po-confirm-close">
                        <img class="img-cerrar" src="${baseUrl}images/cerrar.png">
                    </button>
                </div>
                <h3>${title}</h3>
            </div>
            <div class="alm-modal-body" style="text-align: center; padding: 3em 2.5em;">
                <div style="margin-bottom: 2em;">
                    <img src="${baseUrl}images/Aviso.png" style="width: 80px; height: 80px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.1));" alt="Aviso">
                </div>
                <div style="font-size: 1.25em; color: #475569; line-height: 1.6; margin-bottom: 2.5em; font-family: 'Poppins', sans-serif;">
                    ${message}
                </div>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <button class="btn-alert-confirm" id="po-confirm-yes">
                        Sí, crear segunda pre-orden
                    </button>
                    <button type="button" class="btn-alert-cancel" id="po-confirm-no">
                        No, solo enviar esta
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    // Abrir con el estilo fluido
    requestAnimationFrame(() => overlay.classList.add('open'));

    function close() {
        overlay.classList.remove('open');
        setTimeout(() => overlay.remove(), 400);
    }

    overlay.querySelector('#po-confirm-yes').onclick = () => { close(); if (typeof onYes === 'function') onYes(); };
    overlay.querySelector('#po-confirm-no').onclick = () => { close(); if (typeof onNo === 'function') onNo(); };
    overlay.querySelector('#po-confirm-close').onclick = () => { close(); if (typeof onNo === 'function') onNo(); };
}

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
        fecha_entrega: document.getElementById(formIds.fecha_entrega).value,
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

    // Detectar clases eliminadas respecto al estado original
    const currentIds = getCurrentClasses('alm-tbody-preorden').map(c => c.claseId);
    const removedClasses = originalClasses.filter(orig => {
        if (!orig.claseId) return false; // ignorar filas vacías
        return !currentIds.includes(orig.claseId);
    });

    const hasRemovedClasses = removedClasses.length > 0;

    // Si hay clases eliminadas y todavía no se activó la segunda pre-orden
    const secondOrderActive = document.getElementById('po-tabs-nav').style.display !== 'none';

    if (hasRemovedClasses && !secondOrderActive) {
        const removedNames = removedClasses
            .map(c => c.claseNombre || c.claseId)
            .filter(Boolean)
            .join(', ');

        const btn_ref = btn;
        const originalText_ref = originalText;

        showConfirmModal(
            'Clases eliminadas detectadas',
            `Eliminaste <strong>${removedClasses.length}</strong> clase(s) de la lista original:<br><em style="color:#033966;">${removedNames}</em><br><br>¿Deseas generar una segunda pre-orden para estas clases con otro proveedor?`,
            // onYes
            () => {
                btn_ref.disabled = true;
                btn_ref.innerText = 'Procesando...';
                const payload1 = buildPayload('alm-tbody-preorden', {
                    proveedor: 'po-proveedor', fecha: 'po-fecha', folio: 'po-folio',
                    ot: 'po-ot', moldura: 'po-moldura', fecha_entrega: 'po-fecha-entrega',
                    observaciones: 'po-observaciones'
                });
                submitPreOrden(payload1, btn_ref, originalText_ref, () => {
                    activateSecondOrder(removedClasses);
                    if (typeof toastpremium === 'function') {
                        toastpremium('Pre-Orden 1 generada. Completa la Pre-Orden 2.', 'warning');
                    }
                });
            },
            // onNo
            () => {
                btn_ref.disabled = true;
                btn_ref.innerText = 'Procesando...';
                const payload = buildPayload('alm-tbody-preorden', {
                    proveedor: 'po-proveedor', fecha: 'po-fecha', folio: 'po-folio',
                    ot: 'po-ot', moldura: 'po-moldura', fecha_entrega: 'po-fecha-entrega',
                    observaciones: 'po-observaciones'
                });
                submitPreOrden(payload, btn_ref, originalText_ref, () => cerrarModalPreOrden());
            }
        );

        return;
    }

    // Flujo normal: enviar y cerrar
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

// ── Envío Pre-Orden 2 ──

document.getElementById('formPreOrden2').addEventListener('submit', function (e) {
    e.preventDefault();

    const btn = document.getElementById('btn-submit-preorden2');
    const originalText = btn.innerText;

    btn.disabled = true;
    btn.innerText = 'Procesando...';

    const payload = buildPayload('alm-tbody-preorden2', {
        proveedor: 'po2-proveedor',
        fecha: 'po2-fecha',
        folio: 'po2-folio',
        ot: 'po2-ot',
        moldura: 'po2-moldura',
        fecha_entrega: 'po2-fecha-entrega',
        observaciones: 'po2-observaciones'
    });

    submitPreOrden(payload, btn, originalText, () => {
        cerrarModalPreOrden();
    });
});

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

