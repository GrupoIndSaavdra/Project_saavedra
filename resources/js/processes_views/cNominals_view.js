import { Process } from "./Process.js";

let selectNames = ["workOrder"];
let array = window.workOrders;
window.originalTableValues = null;

console.log(window.workOrders);
insertSelect(window.workOrders, "workOrder", 1);

function updateSelectColors() {
    selectNames.forEach((name, index) => {
        let select = document.querySelector(`.select-${name}`);
        if (!select || select.value === "") return;
        
        let currentPath = [];
        for (let j = 0; j <= index; j++) {
            let s = document.querySelector(`.select-${selectNames[j]}`);
            if (s && s.value) {
                currentPath.push(s.value);
            }
        }
        
        let nodeData = window.workOrders;
        for (let p of currentPath) {
            if (nodeData && nodeData[p]) nodeData = nodeData[p];
            else nodeData = null;
        }
        
        let isComplete = nodeData ? isNodeCompleted(nodeData) : false;
        let hasDraft = hasUnsavedDraft(currentPath);
        
        if (isComplete && !hasDraft) {
            select.classList.remove("cnom-select-white", "cnom-select-blue"); select.classList.add("cnom-select-green");
        } else {
            select.classList.remove("cnom-select-green", "cnom-select-blue"); select.classList.add("cnom-select-white");
        }
    });
    
    updateSubmitButton();
}

function updateSubmitButton() {
    let btn = document.querySelector(".btn-submit");
    if (!btn) return;
    
    let path = getCurrentFilterPath();
    if (path.length === 0) return;
    
    let draftKey = "draft_cnominals_" + path.join("_");
    let isDifferent = true;
    let elements = document.querySelectorAll(".scrollable-table input, .scrollable-table select, .scrollable-table textarea");
    if (elements.length > 0) {
        let currentValues = Array.from(elements).map(el => el.type === 'checkbox' || el.type === 'radio' ? el.checked : el.value);
        if (window.originalTableValues && window.originalTableValues.key === draftKey) {
            isDifferent = JSON.stringify(currentValues) !== JSON.stringify(window.originalTableValues.values);
        }
    }

    let nodeData = window.workOrders;
    for (let p of path) {
        if (nodeData && nodeData[p]) nodeData = nodeData[p];
        else nodeData = null;
    }
    let isComplete = nodeData ? isNodeCompleted(nodeData) : false;

    // Si ya está completo en BD y NO tiene cambios locales, se bloquea el botón
    if (isComplete && !isDifferent) {
        btn.disabled = true;
        btn.classList.add("cnom-btn-disabled");
        btn.value = "Sin Cambios";
    } else {
        btn.disabled = false;
        btn.classList.remove("cnom-btn-disabled");
        btn.value = "Guardar";
    }
}

function insertSelect(arrayParam, name, id) {
    let wrapper = document.querySelector(".wrapper");
    if (arrayParam) {
        let options = Object.keys(arrayParam);
        createSelect(options, name, id, arrayParam);
    } else {
        let div_alert = document.createElement("div");
        div_alert.className = "alert alert-warning";
        div_alert.innerHTML = "No hay ordenes de trabajo registradas";
        wrapper.appendChild(div_alert);
    }
}

function hasUnsavedDraft(pathArray) {
    let prefix = "draft_cnominals_" + pathArray.join("_");
    for (let i = 0; i < localStorage.length; i++) {
        let key = localStorage.key(i);
        if (key && key.startsWith(prefix)) {
            return true;
        }
    }
    return false;
}

function isNodeCompleted(nodeData) {
    if (Array.isArray(nodeData)) {
        return nodeData.length > 0;
    } else if (typeof nodeData === 'object' && nodeData !== null) {
        let keys = Object.keys(nodeData);
        if (keys.length === 0) return false;
        
        for (let key of keys) {
            if (!isNodeCompleted(nodeData[key])) {
                return false;
            }
        }
        return true;
    }
    return false;
}

function createSelect(options, name, id, parentArray) {
    // Calcular la ruta actual basándonos en los selects anteriores
    let currentPath = [];
    for (let j = 0; j < parseInt(id) - 1; j++) {
        let s = document.querySelector(`.select-${selectNames[j]}`);
        if (s && s.value) {
            currentPath.push(s.value);
        }
    }

    //Crear un div para el select y el label
    let div = document.createElement("div");
    div.className = `form-group ${name} animated-div`;

    //Crear un select
    let select = document.createElement("select");
    select.name = name;
    select.className = `form-control select-${name}`;
    select.id = id; // Agregar id al select

    //Insertar opcion vacia
    let option = document.createElement("option");
    option.value = "";
    option.innerHTML = " Selecciona una opcion ";
    option.classList.add("cnom-select-white");
    select.appendChild(option);

    //Agregar opciones al select
    for (let i = 0; i < options.length; i++) {
        option = document.createElement("option");
        option.value = options[i];
        
        let childPath = [...currentPath, options[i]];
        let childNodeData = parentArray[options[i]];
        let isComplete = isNodeCompleted(childNodeData);
        let hasDraft = hasUnsavedDraft(childPath);

        // Si está completo en BD y NO tiene borrador local, va verde. Si tiene borrador, va blanco.
        if (isComplete && !hasDraft) {
            option.classList.remove("cnom-select-white"); option.classList.add("cnom-select-green");
            option.innerHTML = options[i] + " ✔"; 
        } else {
            option.classList.remove("cnom-select-green"); option.classList.add("cnom-select-white");
            option.innerHTML = options[i];
        }
        select.appendChild(option);
    }

    //Agregar evento al select
    select.addEventListener("change", function () {
        deleteSelects(selectNames.slice(parseInt(select.id)));
        if (select.value != "") {
            let currentNodeData = parentArray[select.value];
            let selectedPath = [...currentPath, select.value];
            let isComplete = isNodeCompleted(currentNodeData);
            let hasDraft = hasUnsavedDraft(selectedPath);

            if (isComplete && !hasDraft) {
                select.classList.remove("cnom-select-white", "cnom-select-blue"); select.classList.add("cnom-select-green");
            } else {
                select.classList.remove("cnom-select-green", "cnom-select-blue"); select.classList.add("cnom-select-white");
            }
            
            hideTable();
            addSelect(select);
        } else {
            // Se cambia el color del select
            select.classList.remove("cnom-select-green", "cnom-select-white"); select.classList.add("cnom-select-blue");
            hideTable();
        }
    });

    //Crear un label para el select
    let label = document.createElement("label");
    label.className = `title`;
    label.innerHTML = getLabelText(name);

    div.appendChild(select);
    div.appendChild(label);

    let row = name === "workOrder"? document.querySelector(".row-principal"): document.querySelector(".row");
    row.appendChild(div);
}

function addSelect(select) {
    //Obtener los valores de las opciones del siguiente select
    let newArray = array;
    selectNames.forEach((name) => {
        let select = document.querySelector(`.select-${name}`);
        if (select.value == "") {
            return;
        } else {
            newArray = newArray[select.value];
        }
    });

    //Obtener el siguiente select
    switch (select.id) {
        case "1":
            selectNames.push("class");
            break;
        case "2":
            selectNames.push("process");
            break;
        case "3":
            if (select.value == "Copiado") {
                selectNames.push("subProcess");
            } else if (select.value == "Operacion Equipo") {
                selectNames.push("operation");
            } else {
                insertTable(newArray, select.value);
                return;
            }
            break;
        case "4":
            let selectProcess = document.querySelector(`.select-process`);
            insertTable(newArray, selectProcess.value, select.value);
            return;
        default:
            insertTable(newArray, select.value);
            return;
    }
    insertSelect( newArray, selectNames[selectNames.length - 1], parseInt(select.id) + 1 );
}

function deleteSelects(elements) {
    elements.forEach((element) => {
        selectNames = selectNames.filter((name) => name !== element);
        let elementHTML = document.querySelector(`.${element}`);
        if (elementHTML) {
            elementHTML.classList.add("remove-div");
            setTimeout(() => {
                elementHTML.remove();
            }, 500);
        }
    });
}

function getLabelText(name) {
    const text = {
        workOrder: "Orden de trabajo",
        class: "Clase",
        process: "Proceso",
        subProcess: "Subproceso",
        operation: "Operación",
    };
    return text[name];
}

function insertTable(array, processSelected, subProcessSelected = null) {
    //Obtener el div donde se insertara la tabla
    let scrollable_table = document.querySelector(".scrollable-table");
    scrollable_table.classList.add("visible");

    let btn_submit = document.querySelector(".btn-submit");
    btn_submit.classList.add("visible");

    //Posicionar el foormulario de busqueda
    let wrapper = document.querySelector(".wrapper");
    wrapper.classList.remove("cnom-wrapper-absolute");
    wrapper.classList.add("cnom-wrapper-relative");

    //Crear la tabla
    if (array.length === 0) {
        // let parent = scrollable_table.parentNode;
        // let div_alert = document.createElement("div");
        // div_alert.className = "alert alert-warning";
        // div_alert.innerHTML = "No hay procesos registrados para esta orden de trabajo";
        // parent.appendChild(div_alert);
    }
    let process = new Process(processSelected, subProcessSelected, array[0], array[1]);
    scrollable_table.appendChild(process.createProcess());
}
function hideTable() {
    //Posicionar el formulario de busqueda
    let wrapper = document.querySelector(".wrapper");
    wrapper.classList.remove("cnom-wrapper-relative");
    wrapper.classList.add("cnom-wrapper-absolute");

    //Ocultar la tabla
    let scrollable_table = document.querySelector(".scrollable-table");
    scrollable_table.innerHTML = "";
    scrollable_table.classList.remove("visible");
    let btn_submit = document.querySelector(".btn-submit");
    btn_submit.classList.remove("visible");
}

// --- AUTO-SAVE Y RESTAURACIÓN DE FILTROS ---

document.addEventListener("DOMContentLoaded", function () {
    // Si la página se abrió navegando (ej. clic en el menú lateral) y NO recargando (F5) ni desde el AJAX
    const navEntries = performance.getEntriesByType("navigation");
    if (navEntries.length > 0 && navEntries[0].type === "navigate") {
        sessionStorage.removeItem("last_cnominals_path");
    }

    // Mostrar mensaje de éxito si venimos de un guardado AJAX exitoso
    const successMsg = sessionStorage.getItem("success_cnominals");
    if (successMsg) {
        setTimeout(() => {
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: successMsg,
                    confirmButtonText: 'Continuar',
                    confirmButtonColor: '#0a8504'
                });
            } else {
                alert(successMsg);
            }
        }, 500); // Dar tiempo a que cargue la librería si es asíncrona
        sessionStorage.removeItem("success_cnominals");
    }

    // Restaurar filtros si venimos de un guardado o recarga
    let savedPath = sessionStorage.getItem("last_cnominals_path");
    if (savedPath) {
        savedPath = JSON.parse(savedPath);
        if (savedPath.length > 0) {
            restorePath(savedPath, 0);
        }
    }
});

function restorePath(path, currentIndex) {
    if (currentIndex >= path.length) return;

    const expectedSelectId = (currentIndex + 1).toString();

    // Esperar a que el select aparezca en el DOM (polling con rAF, max ~2s)
    let attempts = 0;
    const maxAttempts = 120; // ~2s a 60fps

    function trySet() {
        let select = document.getElementById(expectedSelectId);
        if (select) {
            // El select existe — asignamos el valor
            select.value = path[currentIndex];
            // Solo disparamos change si el valor quedó asignado
            if (select.value === path[currentIndex]) {
                select.dispatchEvent(new Event("change"));
                // Avanzar al siguiente nivel
                setTimeout(() => {
                    restorePath(path, currentIndex + 1);
                }, 80); // 80ms para dar tiempo al DOM después del change
            } else {
                // Valor no encontrado en las opciones del select, abortar restauración
                console.warn("[restorePath] Valor no encontrado en select:", path[currentIndex]);
            }
        } else if (attempts < maxAttempts) {
            attempts++;
            requestAnimationFrame(trySet);
        } else {
            console.warn("[restorePath] Timeout esperando select id=" + expectedSelectId);
        }
    }

    requestAnimationFrame(trySet);
}


function getCurrentFilterPath() {
    let selects = document.querySelectorAll(".animated-div select");
    let path = [];
    selects.forEach(s => {
        if (s.value) path.push(s.value);
    });
    return path;
}

// Guardar la ruta y mandar el form por AJAX (Evita errores de sesión 419)
document.querySelector(".form-search").addEventListener("submit", async function (e) {
    e.preventDefault(); // Detener el envío tradicional que recarga y rompe si hay error

    let path = getCurrentFilterPath();
    if (path.length === 0) return;

    // 1. Mostrar estado de carga visual
    const btn = document.querySelector(".btn-submit");
    if (typeof setVisualLoading === "function") {
        setVisualLoading(btn, true);
    } else {
        btn.disabled = true;
        btn.dataset.originalText = btn.value;
        btn.value = "Guardando...";
    }

    try {
        let formData = new FormData(this);
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        
        // 2. Hacer la petición asíncrona
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: formData
        });

        // 3. Manejar error de sesión u otros errores
        if (!response.ok) {
            if (response.status === 419 || response.status === 401) {
                throw new Error("Tu sesión ha expirado por inactividad. Abre otra pestaña, inicia sesión y luego presiona Guardar aquí de nuevo.");
            }
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Error ${response.status} del servidor`);
        }

        const data = await response.json();

        // 4. Éxito: Guardar path para restaurar filtros, limpiar borrador y recargar
        sessionStorage.setItem("last_cnominals_path", JSON.stringify(path));
        let draftKey = "draft_cnominals_" + path.join("_");
        localStorage.removeItem(draftKey);
        
        // Usamos sessionStorage temporal para mostrar el SweetAlert tras recargar
        sessionStorage.setItem("success_cnominals", data.message || "Datos guardados correctamente");
        window.location.reload();

    } catch (error) {
        console.error('[Guardar Cotas]', error);
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: 'Error de Sesión',
                text: error.message,
                icon: 'error',
                confirmButtonColor: '#9c0303'
            });
        } else {
            alert(error.message);
        }
    } finally {
        if (typeof setVisualLoading === "function") {
            setVisualLoading(btn, false);
        } else {
            btn.disabled = false;
            btn.value = btn.dataset.originalText || "Guardar";
        }
    }
});

// Guardar la ruta cada vez que cambien un filtro (para sobrevivir a F5 manual)
document.addEventListener("change", function(e) {
    if (e.target.closest(".animated-div")) {
        setTimeout(() => {
            let path = getCurrentFilterPath();
            sessionStorage.setItem("last_cnominals_path", JSON.stringify(path));
        }, 100);
    }
});



// Autoguardado (Borrador) y Detección de Cambios en Vivo
document.querySelector(".form-search").addEventListener("input", function (e) {
    if (e.target.closest(".scrollable-table")) {
        let path = getCurrentFilterPath();
        if (path.length === 0) return;
        
        let draftKey = "draft_cnominals_" + path.join("_");
        
        // Guardar todos los valores de inputs de la tabla por índice
        let elements = document.querySelectorAll(".scrollable-table input, .scrollable-table select, .scrollable-table textarea");
        let currentValues = Array.from(elements).map(el => {
            if (el.type === 'checkbox' || el.type === 'radio') {
                return el.checked;
            }
            return el.value;
        });

        // Comparar con los valores originales de la BD
        let isDifferent = true;
        if (window.originalTableValues && window.originalTableValues.key === draftKey) {
            isDifferent = JSON.stringify(currentValues) !== JSON.stringify(window.originalTableValues.values);
        }
        
        if (isDifferent) {
            localStorage.setItem(draftKey, JSON.stringify(currentValues));
        } else {
            localStorage.removeItem(draftKey);
        }

        // Refrescar el color de los selects dinámicamente
        updateSelectColors();
    }
});

const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.addedNodes.length > 0) {
            let path = getCurrentFilterPath();
            if (path.length > 0) {
                let draftKey = "draft_cnominals_" + path.join("_");
                
                // SNAPSHOT: Capturar los valores originales justo cuando la tabla es inyectada por Process.js
                let elements = document.querySelectorAll(".scrollable-table input, .scrollable-table select, .scrollable-table textarea");
                if (elements.length > 0) {
                    window.originalTableValues = {
                        key: draftKey,
                        values: Array.from(elements).map(el => el.type === 'checkbox' || el.type === 'radio' ? el.checked : el.value)
                    };
                }

                // Inicializar colores y estado del botón "Guardar" al cargar la tabla (debe ir ANTES de cualquier return)
                updateSelectColors();

                let draftStr = localStorage.getItem(draftKey);
                
                // Si venimos de un guardado exitoso, limpiamos el borrador
                if (window.successSave) {
                    localStorage.removeItem(draftKey);
                    window.successSave = false; // Solo limpiar la primera vez
                    updateSelectColors(); // Re-evaluar
                    return;
                }
                
                if (!draftStr) return;
                
                let draft = JSON.parse(draftStr);
                let inputsToFill = document.querySelectorAll(".scrollable-table input, .scrollable-table select, .scrollable-table textarea");
                
                if (draft && draft.length === inputsToFill.length) {
                    inputsToFill.forEach((el, index) => {
                        if (draft[index] !== null && draft[index] !== undefined && draft[index] !== "") {
                            if (el.type === 'checkbox' || el.type === 'radio') {
                                el.checked = draft[index];
                            } else {
                                el.value = draft[index];
                            }
                            el.dispatchEvent(new Event("input", { bubbles: true }));
                        }
                    });
                    
                    // Notificar al usuario silenciosamente (Toast)
                    if (typeof Swal !== "undefined") {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'Borrador recuperado automáticamente',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                }
            }
        }
    });
});

observer.observe(document.querySelector(".scrollable-table"), { childList: true });
