/**
 * Módulo de Gestión de Formulario de Operador
 * Renderizado de selects dinámicos (OT, Clase, Proceso, Subproceso) e integración con pickers
 */

import { openModalCalendar, formatDisplayDate } from '../../components/modalCmCalendar.js';
import { openModalShiftTime, openModalTime, getAutoDetectedShift, formatDisplayTime, getShiftSummaryText, getShiftTriggerHtml } from '../../components/modalCmTime.js';
import { openModalMachine, formatDisplayMachine, getMachinePlaceholderHtml, getMachineTriggerHtml } from '../../components/modalCmMachine.js';
import { formFieldIcons, getFieldLabelHtml } from './config/formFields.config.js';
import { insertWeldingTypeBox } from './ptaManager.js';

function createSelects(labelText, className) {
    let form_grid = document.querySelector(".form-grid");

    let form_group = document.createElement("div");
    form_group.className = "form-group";

    let select = document.createElement("select");
    select.className = `form-control ${className}`;
    select.name = className;
    select.id = className;
    if (className === "workOrder") {
        modifySelects(window.workOrders, select, labelText);
    } else {
        select.disabled = true;
        let optionEmpty = document.createElement("option");
        optionEmpty.value = "";
        optionEmpty.textContent = "No disponible";
        select.appendChild(optionEmpty);
    }

    if (labelText === "Subproceso" || labelText === "Orden de trabajo" || labelText === "Proceso") {
        if (labelText !== "Proceso") {
            form_group.classList.add("full-width");
        }
        form_group.id = "processGroup";
    } else if (labelText === "Subproceso") {
        let form_groupFounded = document.querySelector(".full-width");
        if (form_groupFounded) {
            form_groupFounded.classList.remove("full-width");
            form_groupFounded.classList.add("normal-width");
        }
    }
    let label = document.createElement("label");
    label.className = "form-label";
    label.setAttribute("for", className);
    let iconFile = formFieldIcons[labelText] || formFieldIcons[className];
    label.innerHTML = getFieldLabelHtml(iconFile, labelText);

    select.addEventListener("change", function () {
        disabledSelects(className);
        select.classList.add("cnom-select-white");
        if (select.value) {
            if (className === "subprocess") {
                let submit = document.querySelector(".btn-submit");
                submit.classList.remove("prod-submit-hidden"); submit.classList.add("prod-submit-visible");
            }
        } else {
            if (className === "subprocess") {
                let submit = document.querySelector(".btn-submit");
                if (submit) {
                    submit.classList.remove("prod-submit-visible"); submit.classList.add("prod-submit-hidden");
                }
            }
        }
    });

    form_group.appendChild(select);
    form_group.appendChild(label);
    if (labelText === "Subproceso") {
        let form_groupProcess = document.querySelectorAll("#processGroup");
        form_groupProcess[1].after(form_group);
    } else {
        form_grid.appendChild(form_group);
    }

    return select;
}

function validateProductionForm() {
    let submit = document.querySelector(".btn-submit");
    if (!submit) return;

    let formGrid = document.querySelector(".form-grid");
    if (!formGrid) return;

    // Obtener todos los campos requeridos (selects e inputs)
    let inputs = formGrid.querySelectorAll("input[required], select[required], .workOrder, .class, .process");
    let allFilled = true;

    inputs.forEach(input => {
        // Ignorar campos deshabilitados
        if (input.disabled) return;

        if (!input.value || input.value.trim() === "") {
            allFilled = false;
        }
    });

    let btnsContainer = submit.closest(".form-btns-container") || submit.parentElement;

    // Cambiar estado del botón y ocultar el contenedor para hacer la tarjeta compacta
    if (allFilled) {
        if (btnsContainer) btnsContainer.style.display = "flex";
        submit.classList.remove("prod-submit-hidden");
        submit.classList.add("prod-submit-visible");
    } else {
        if (btnsContainer) btnsContainer.style.display = "none";
        submit.classList.remove("prod-submit-visible");
        submit.classList.add("prod-submit-hidden");
    }
}

function modifySelects(array, select, labelText) {
    select.disabled = false; // Habilitar el select si ya existe
    select.innerHTML = ""; // Limpiar las opciones existentes

    // Crear opciones para cada orden de trabajo

    let arrayElements = labelText == "Proceso" || labelText == "Subproceso" ? Object.values(array) : Object.keys(array);

    arrayElements.forEach((element, key) => {
        // Crear un elemento de opción vacio
        if (key == 0) {
            let optionEmpty = document.createElement("option");
            optionEmpty.value = "";
            optionEmpty.textContent = "Selecciona una opción";
            select.appendChild(optionEmpty);
        }
        // Evitar crear opción si element es "moldura" y labelText es "Clase"
        if (labelText === "Clase" && element === "moldura") {
            return; // o `continue;` si estás en un for tradicional
        }

        // Crear un elemento de opción con el valor de la orden de trabajo
        let option = document.createElement("option");
        option.value = element;
        option.textContent = labelText == "Orden de trabajo" ? `${element} - ${array[element]["moldura"]}` : element;
        select.appendChild(option);
    });
}

function disabledSelects(className) {
    //Deshabilitar selects
    let array = ["subprocess", "process", "class"];
    for (let i = 0; i < array.length; i++) {
        if (array[i] === className) {
            break; // No deshabilitar el select actual
        }
        let select = document.querySelector(`.${array[i]}`);
        if (select) {
            if (array[i] !== "subprocess") {
                select.classList.add("cnom-select-white");
                select.disabled = true;
                select.innerHTML = ""; // Limpiar las opciones existentes
                let optionEmpty = document.createElement("option");
                optionEmpty.value = "";
                optionEmpty.textContent = "No disponible";
                select.appendChild(optionEmpty);

                let submit = document.querySelector(".btn-submit");
                if (submit) {
                    submit.classList.remove("prod-submit-visible");
                    submit.classList.add("prod-submit-hidden");
                    let btnsContainer = submit.closest(".form-btns-container") || submit.parentElement;
                    if (btnsContainer) btnsContainer.style.display = "none";
                }
            } else {
                let parent = select.parentElement;
                if (parent) {
                    let formGroupProcess = document.querySelector("#processGroup");
                    if (formGroupProcess) {
                        formGroupProcess.classList.remove("normal-width");
                        formGroupProcess.classList.add("full-width");
                    }
                    parent.innerHTML = ""; // Limpiar el contenido del div padre
                    parent.remove(); // Eliminar el div padre
                }
            }
        }
    }
}

/**
 * Crea un campo disparador interactivo para Fecha o Tiempo individual
 * Integra modalCmCalendar y modalCmTime con visualización elegante y validación nativa.
 */
function createInteractivePickerField(fieldName, labelText, initialValue = "") {
    const hiddenInput = document.createElement("input");
    hiddenInput.type = "hidden";
    hiddenInput.name = fieldName;
    hiddenInput.id = fieldName;
    hiddenInput.value = initialValue ? String(initialValue).trim() : "";

    const triggerInput = document.createElement("input");
    triggerInput.type = "text";
    triggerInput.readOnly = true;
    triggerInput.required = true;
    triggerInput.className = "form-control normal-input custom-picker-trigger";
    triggerInput.id = `display_${fieldName}`;

    if (fieldName === "date") {
        triggerInput.placeholder = "Elegir fecha";
        triggerInput.tabIndex = 0;
        triggerInput.setAttribute("role", "button");
        triggerInput.title = "Haz clic para seleccionar la fecha";
        if (initialValue) {
            triggerInput.value = formatDisplayDate(initialValue);
        } else {
            triggerInput.value = "";
            triggerInput.classList.add("is-empty");
        }

        const openCalendar = () => {
            openModalCalendar(hiddenInput, triggerInput, null, "Seleccionar Fecha de Producción");
        };

        triggerInput.addEventListener("click", openCalendar);
    } else {
        triggerInput.placeholder = "--:-- hrs";
        if (initialValue) {
            triggerInput.value = formatDisplayTime(initialValue) || `${initialValue} hrs`;
        } else {
            triggerInput.classList.add("is-empty");
        }

        const title = fieldName === "startTime" ? "Seleccionar Hora de Inicio" : "Seleccionar Hora de Término";
        const openTime = () => {
            openModalTime(hiddenInput, triggerInput, title);
        };

        triggerInput.addEventListener("click", openTime);
    }

    // Al cambiar el hiddenInput, sincronizar el trigger si se disparó programáticamente
    hiddenInput.addEventListener("input", () => {
        if (fieldName === "date") {
            triggerInput.value = formatDisplayDate(hiddenInput.value);
        } else {
            triggerInput.value = formatDisplayTime(hiddenInput.value) || `${hiddenInput.value} hrs`;
        }
        triggerInput.classList.remove("is-empty");
        triggerInput.dispatchEvent(new Event("input", { bubbles: true }));
        triggerInput.dispatchEvent(new Event("change", { bubbles: true }));
    });

    return { hiddenInput, triggerInput };
}

function getShiftPlaceholderHtml() {
    return `
        <div style="display: flex; align-items: center; justify-content: center; width: 100%; color: #64748b;">
            <span style="font-weight: 800; font-size: 0.92rem; color: #64748b;">Selecciona un turno</span>
        </div>
    `;
}

/**
 * Crea un único campo/botón interactivo para "Turno y Horario"
 * El usuario debe seleccionar el turno siempre explícitamente en el modal.
 */
function createInteractiveShiftTimeField(initialStart = "", initialEnd = "") {
    const hiddenStart = document.createElement("input");
    hiddenStart.type = "hidden";
    hiddenStart.name = "startTime";
    hiddenStart.id = "startTime";
    hiddenStart.required = true;

    const hiddenEnd = document.createElement("input");
    hiddenEnd.type = "hidden";
    hiddenEnd.name = "endTime";
    hiddenEnd.id = "endTime";
    hiddenEnd.required = true;

    const triggerInput = document.createElement("div");
    triggerInput.className = "form-control normal-input custom-picker-trigger is-shift-picker";
    triggerInput.id = "display_shift_time";
    triggerInput.tabIndex = 0;
    triggerInput.setAttribute("role", "button");
    triggerInput.title = "Haz clic para seleccionar el turno u horario";

    // No autoseleccionar turno si viene vacío: el usuario debe elegirlo
    let finalStart = initialStart ? String(initialStart).trim().substring(0, 5) : "";
    let finalEnd = initialEnd ? String(initialEnd).trim().substring(0, 5) : "";

    // Regla de seguridad para sábado: si es sábado nunca permitir turno noche
    const today = new Date();
    if (today.getDay() === 6 && finalStart === "22:00") {
        finalStart = "";
        finalEnd = "";
    }

    hiddenStart.value = finalStart;
    hiddenEnd.value = finalEnd;

    if (finalStart && finalEnd) {
        triggerInput.innerHTML = getShiftTriggerHtml(finalStart, finalEnd);
        triggerInput.classList.remove("is-empty");
    } else {
        triggerInput.innerHTML = getShiftPlaceholderHtml();
        triggerInput.classList.add("is-empty");
    }

    // Al hacer clic o presionar Enter/Espacio, abrir modal unificado de Turno y Horario
    const openShiftModal = () => {
        openModalShiftTime(hiddenStart, hiddenEnd, triggerInput);
    };
    triggerInput.addEventListener("click", openShiftModal);
    triggerInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            openShiftModal();
        }
    });

    // Sincronizar trigger si se modifican los valores de los hidden inputs
    const syncTrigger = () => {
        if (hiddenStart.value && hiddenEnd.value) {
            triggerInput.innerHTML = getShiftTriggerHtml(hiddenStart.value, hiddenEnd.value);
            triggerInput.classList.remove("is-empty");
        } else {
            triggerInput.innerHTML = getShiftPlaceholderHtml();
            triggerInput.classList.add("is-empty");
        }
        triggerInput.dispatchEvent(new Event("input", { bubbles: true }));
        triggerInput.dispatchEvent(new Event("change", { bubbles: true }));
        validateProductionForm();
    };

    hiddenStart.addEventListener("input", syncTrigger);
    hiddenEnd.addEventListener("input", syncTrigger);

    return { hiddenStart, hiddenEnd, triggerInput };
}

function createInputs() {
    let form_grid = document.querySelector(".form-grid");

    // 1. Campo unificado de Turno y Horario (engloba startTime y endTime en un solo botón/campo)
    let shiftGroup = document.createElement("div");
    shiftGroup.className = "form-group full-width";

    let labelShift = document.createElement("label");
    labelShift.innerHTML = getFieldLabelHtml("Turno-Icon.png", "Turno");
    labelShift.className = "form-label";
    labelShift.setAttribute("for", "display_shift_time");

    const { hiddenStart, hiddenEnd, triggerInput: triggerShift } = createInteractiveShiftTimeField();
    shiftGroup.appendChild(hiddenStart);
    shiftGroup.appendChild(hiddenEnd);
    shiftGroup.appendChild(triggerShift);
    shiftGroup.appendChild(labelShift);
    form_grid.appendChild(shiftGroup);

    // 2. Campo interactivo de Máquina (Columna izquierda)
    let machineGroup = document.createElement("div");
    machineGroup.className = "form-group";

    let labelMachine = document.createElement("label");
    labelMachine.innerHTML = getFieldLabelHtml("Maquina-Icon.png", "Máquina");
    labelMachine.className = "form-label";
    labelMachine.setAttribute("for", "display_machine");

    const hiddenMachine = document.createElement("input");
    hiddenMachine.type = "hidden";
    hiddenMachine.name = "machine";
    hiddenMachine.id = "machine";
    hiddenMachine.required = true;

    const triggerMachine = document.createElement("div");
    triggerMachine.className = "form-control normal-input custom-picker-trigger is-machine-picker is-empty";
    triggerMachine.id = "display_machine";
    triggerMachine.tabIndex = 0;
    triggerMachine.setAttribute("role", "button");
    triggerMachine.title = "Haz clic para seleccionar la máquina";
    triggerMachine.innerHTML = getMachinePlaceholderHtml();

    const openMachine = () => {
        openModalMachine(hiddenMachine, triggerMachine);
    };

    triggerMachine.addEventListener("click", openMachine);
    triggerMachine.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            openMachine();
        }
    });

    const syncMachineValidation = () => {
        validateProductionForm();
    };
    hiddenMachine.addEventListener("input", syncMachineValidation);
    hiddenMachine.addEventListener("change", syncMachineValidation);

    machineGroup.appendChild(hiddenMachine);
    machineGroup.appendChild(triggerMachine);
    machineGroup.appendChild(labelMachine);
    form_grid.appendChild(machineGroup);

    // 3. Campo de Fecha (Columna derecha)
    let dateGroup = document.createElement("div");
    dateGroup.className = "form-group";

    let labelDate = document.createElement("label");
    labelDate.innerHTML = getFieldLabelHtml("Fecha-Icon.png", "Fecha");
    labelDate.className = "form-label";
    labelDate.setAttribute("for", "date");

    const { hiddenInput: hiddenDate, triggerInput: triggerDate } = createInteractivePickerField("date", "Fecha", "");

    // Si el usuario cambia la fecha a sábado o entre semana, verificar bloqueos y revalidar formulario
    hiddenDate.addEventListener("change", () => {
        validateProductionForm();
        if (hiddenDate.value) {
            const parts = hiddenDate.value.split("-").map(Number);
            if (parts.length === 3) {
                const chosenDate = new Date(parts[0], parts[1] - 1, parts[2], 12, 0, 0);
                const isSat = chosenDate.getDay() === 6;
                if (isSat) {
                    if (hiddenStart.value === "22:00") {
                        // Sábado no tiene turno noche -> cambiar a Mañana
                        hiddenStart.value = "06:00";
                        hiddenEnd.value = "14:00";
                    } else if (hiddenStart.value === "13:45" && hiddenEnd.value === "21:45") {
                        // Sábado mixto termina a las 18:45
                        hiddenEnd.value = "18:45";
                    }
                } else {
                    if (hiddenStart.value === "13:45" && hiddenEnd.value === "18:45") {
                        // Entre semana mixto termina a las 21:45
                        hiddenEnd.value = "21:45";
                    }
                }
                triggerShift.innerHTML = getShiftTriggerHtml(hiddenStart.value, hiddenEnd.value);
            }
        }
    });

    dateGroup.appendChild(hiddenDate);
    dateGroup.appendChild(triggerDate);
    dateGroup.appendChild(labelDate);
    form_grid.appendChild(dateGroup);

    let form_principal_data = document.querySelector(".form-principal-data");

    let container = document.createElement("div");
    container.className = "form-btns-container";
    container.style.display = "none"; // Inicia oculto para que el contenedor principal sea compacto

    const resultadoIconSrc = window.imgResultado || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/Resultado-Icon.png` : "/images/Resultado-Icon.png");
    let submit = document.createElement("button");
    submit.type = "submit";
    submit.className = "btn-submit prod-submit-hidden";
    submit.innerHTML = `<img src="${resultadoIconSrc}" class="btn-submit-icon" alt="Registrar"><span>Registrar</span>`;

    container.appendChild(submit);
    form_principal_data.appendChild(container);
}

function createInputsWithValue(values, valuesEnabled = []) {
    let arrayKeyNames = {
        workOrder: "Orden de trabajo",
        class: "Clase",
        process: "Proceso",
        subprocess: "Subproceso",
        machine: "Máquina",
        startTime: "Turno",
        endTime: "Hora de término",
        date: "Fecha",
        consignmentPieces: "Pedido con consignación",
        remainingPieces: "Piezas restantes",
    };
    let form_grid = document.querySelector(".form-grid");
    const isEditMode = (valuesEnabled && valuesEnabled.length > 0) || (values && values.edit == 1);
    const blockedIconUrl = window.imgBloqueado || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/bloqueado.png` : "/images/bloqueado.png");

    // Banner informativo superior en modo edición
    if (isEditMode) {
        let existingBanner = document.querySelector(".edit-meta-notice-banner");
        if (!existingBanner) {
            let banner = document.createElement("div");
            banner.className = "edit-meta-notice-banner";
            banner.innerHTML = `
                <img src="${blockedIconUrl}" class="edit-meta-notice-icon" alt="Bloqueado">
                <span><strong>Modo Edición:</strong> Los campos en gris están <strong>bloqueados</strong> para proteger la integridad del proceso.</span>
            `;
            let formPrincipal = document.querySelector(".form-principal-data");
            if (formPrincipal) {
                formPrincipal.prepend(banner);
            }
        }
    }

    for (const [key, value] of Object.entries(values)) {
        if (key === "endTime") {
            // Ya está englobado dentro de startTime ("Turno y Horario")
            continue;
        }

        let form_group = document.createElement("div");
        form_group.className = (key === "workOrder" || key === "subprocess" || key === "startTime") ? "form-group full-width" : "form-group";

        if (
            key != "operator" &&
            key != "meta" &&
            key != "edit" &&
            key != "cNominals" &&
            key != "machinedPiecesInMeta" &&
            key != "numberPieces" &&
            key != "availableAssemblies" &&
            key != "history" &&
            key != "ptaTableHtml" &&
            key != "tipo_soldadura" &&
            key != "consignmentPieces" &&
            key != "remainingPieces" &&
            value != null &&
            value !== ""
        ) {
            let label = document.createElement("label");
            let labelText = arrayKeyNames[key] || key;
            let iconFile = formFieldIcons[key] || formFieldIcons[labelText];
            let isFieldEnabled = valuesEnabled.includes(key);

            let badgeHtml = (isEditMode && !isFieldEnabled)
                ? `<span class="locked-field-badge" title="Campo bloqueado - No modificable"><img src="${blockedIconUrl}" class="locked-field-icon" alt="Bloqueado"> Bloqueado</span>`
                : '';

            label.innerHTML = getFieldLabelHtml(iconFile, labelText) + badgeHtml;
            label.className = "form-label";
            label.setAttribute("for", key === "startTime" ? "display_shift_time" : key);

            if (isFieldEnabled) {
                if (key === "startTime") {
                    const { hiddenStart, hiddenEnd, triggerInput } = createInteractiveShiftTimeField(values.startTime, values.endTime);
                    form_group.appendChild(hiddenStart);
                    form_group.appendChild(hiddenEnd);
                    form_group.appendChild(triggerInput);
                } else if (key === "date") {
                    const { hiddenInput, triggerInput } = createInteractivePickerField(key, arrayKeyNames[key], value);
                    form_group.appendChild(hiddenInput);
                    form_group.appendChild(triggerInput);
                } else {
                    let input = document.createElement("input");
                    input.name = key;
                    input.id = key;
                    input.type = "text";
                    input.className = "form-control normal-input";
                    input.required = true;
                    input.value = value;
                    input.classList.add("cnom-select-white");
                    form_group.appendChild(input);
                }
            } else {
                form_group.classList.add("form-group-locked");
                let input = document.createElement("input");
                input.name = key;
                input.id = key;
                input.type = "text";
                input.className = "form-control normal-input input-locked";
                input.readOnly = true;
                input.setAttribute("tabindex", "-1");
                input.setAttribute("aria-readonly", "true");
                input.title = "Este campo está bloqueado y no se puede editar";

                if (key == "machine") {
                    let input_hidden = document.createElement("input");
                    input_hidden.type = "hidden";
                    input_hidden.name = key;
                    input_hidden.value = value;
                    form_group.appendChild(input_hidden);
                    input.value = value.replace("_", " y ");
                } else if (key == "date") {
                    input.value = formatDisplayDate(value) || value;
                } else if (key == "startTime") {
                    let shiftDiv = document.createElement("div");
                    shiftDiv.className = "form-control normal-input custom-picker-trigger is-shift-picker input-locked";
                    shiftDiv.id = "display_shift_time";
                    shiftDiv.style.pointerEvents = "none";
                    shiftDiv.setAttribute("aria-readonly", "true");
                    shiftDiv.title = "Este campo está bloqueado y no se puede editar";
                    shiftDiv.innerHTML = getShiftTriggerHtml(values.startTime, values.endTime);
                    form_group.appendChild(shiftDiv);

                    let hiddenStart = document.createElement("input");
                    hiddenStart.type = "hidden";
                    hiddenStart.name = "startTime";
                    hiddenStart.id = "startTime";
                    hiddenStart.value = values.startTime;
                    form_group.appendChild(hiddenStart);

                    form_group.appendChild(label);
                    form_grid.appendChild(form_group);
                    continue;
                } else {
                    input.value = key == "remainingPieces" ? value : value;
                }
                form_group.appendChild(input);
            }

            form_group.appendChild(label);
            form_grid.appendChild(form_group);
        }
    }
    // Actualizar campos de consignación y piezas restantes en la tabla superior
    let consInput = document.getElementById("meta_consignment_pieces");
    if (consInput && values.consignmentPieces !== undefined) {
        consInput.value = values.consignmentPieces + " piezas";
    }
    let remInput = document.getElementById("meta_remaining_pieces");
    if (remInput && values.remainingPieces !== undefined) {
        remInput.value = values.remainingPieces + " piezas";
    }

    // Crear el boton de submit si hay campos habilitados
    if (valuesEnabled.length > 0) {
        let container = document.createElement("div");
        container.className = "form-btns-container";

        // Agregar submit Confirmar
        container.appendChild(createBtnSubmit_editMeta());

        document.querySelector(".form-principal-data").appendChild(container);
    }
}
function createBtnSubmit_editMeta() {
    let submit = document.createElement("button");
    submit.type = "submit";
    submit.className = "btn-submit";
    submit.classList.remove("prod-submit-hidden");
    submit.classList.add("prod-submit-visible");

    const resultadoIconSrc = window.imgResultado || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/Resultado-Icon.png` : "/images/Resultado-Icon.png");

    submit.innerHTML = `
        <img src="${resultadoIconSrc}" class="btn-submit-icon" alt="Confirmar">
        <span>Confirmar</span>
    `;
    return submit;
}

/**
 * Muestra el campo de tipo de soldadura en el formulario del operador (form-grid).
 * Solo se muestra en los procesos que aplican y cuando hay arrayData.
 */


function insertSelects() {
    let form_grid = document.querySelector(".form-grid");
    if (window.workOrders != null) {
        //prettier-ignore
        let selectWO = createSelects("Orden de trabajo", "workOrder");
        let selectClasses = createSelects("Clase", "class");
        let selectProcesses = createSelects("Proceso", "process");

        selectWO.addEventListener("change", function () {
            let selectedValue = selectWO.value;
            if (selectedValue) {
                // "Selección de OT" eliminado: es telemetría de bajo valor
                let classes = window.workOrders[selectedValue];
                modifySelects(classes, document.querySelector(".class"), "Clase");
            }
            validateProductionForm();
        });
        //prettier-ignore
        selectClasses.addEventListener("change", function () {
            let selectedClass = selectClasses.value;
            if (selectedClass) {
                // "Selección de Clase" eliminado: es telemetría de bajo valor
                let processes = window.workOrders[selectWO.value][selectedClass];
                if (processes.length > 0) {
                    modifySelects(processes, document.querySelector(".process"), "Proceso");
                }
            }
            validateProductionForm();
        });
        //prettier-ignore
        selectProcesses.addEventListener("change", function () {
            let selectedProcess = selectProcesses.value;
            if (selectedProcess) {
                // "Selección de Proceso" eliminado: es telemetría de bajo valor
            }
            let submit = document.querySelector(".btn-submit");
            if (selectedProcess && (selectedProcess === "Operacion Equipo" || selectedProcess === "Candado Obturador")) {
                let selectSubprocesses = createSelects("Subproceso", "subprocess");
                modifySelects(["1 operacion", "2 operacion"], selectSubprocesses, "Subproceso");
            } else if (selectedProcess) {
                validateProductionForm();
            } else {
                validateProductionForm();
            }
        });
        createInputs();

        // Agregar listeners para validación en tiempo real
        let formContainer = document.querySelector(".form-principal-data");
        formContainer.addEventListener("change", validateProductionForm);
        formContainer.addEventListener("input", validateProductionForm);
    } else {
        let p = document.createElement("p");
        p.textContent = "No hay órdenes de trabajo disponibles.";
        form_grid.classList.add("form-grid-one");
        form_grid.appendChild(p);
    }
}

function createInputPassword(name = "passwordAdmin", placeholderText = "Contraseña de Administrador") {
    // Creación de contenedor
    let form_group = document.createElement("div");
    form_group.className = "form-group-password";
    form_group.classList.add("z-11000");

    // Input de contraseña
    let inputPassword = document.createElement("input");
    inputPassword.type = "password";
    inputPassword.name = name;
    inputPassword.placeholder = placeholderText;
    inputPassword.className = "normal-input input-password";
    inputPassword.required = true;
    inputPassword.autocomplete = "current-password";

    // Botón de submit con icono corporativo
    let submit = document.createElement("button");
    submit.type = "submit";
    submit.className = "btn-submit-password" + (name === "passwordQuality" ? " btn-quality" : "");
    const resultIconUrl = window.baseUrl ? (window.baseUrl + "/images/Resultado-Icon.png") : "/images/Resultado-Icon.png";
    submit.innerHTML = `
        <img src="${resultIconUrl}" class="btn-verify-icon" alt="Verificar">
        <span>Verificar</span>
    `;

    // Input oculto para meta
    let inputMeta = document.createElement("input");
    inputMeta.type = "hidden";
    inputMeta.name = "meta";
    if (window.arrayData && window.arrayData["meta"]) {
        inputMeta.value = window.arrayData["meta"].id;
    }

    // Contenedor del input para el ojo y candado
    let inputWrapper = document.createElement("div");
    inputWrapper.className = "password-input-wrapper pos-relative align-center";

    // Icono de candado a la izquierda
    let lockIcon = document.createElement("span");
    lockIcon.className = "password-lock-icon";
    lockIcon.innerHTML = `
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
    `;

    // Iconos SVG Premium
    const eyeOpen = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
    const eyeClosed = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;

    // Icono del ojo
    let eyeIcon = document.createElement("span");
    eyeIcon.innerHTML = eyeOpen;
    eyeIcon.className = "eye-toggle";
    eyeIcon.title = "Mostrar / Ocultar contraseña";

    eyeIcon.onclick = () => {
        if (inputPassword.type === "password") {
            inputPassword.type = "text";
            eyeIcon.innerHTML = eyeClosed;
        } else {
            inputPassword.type = "password";
            eyeIcon.innerHTML = eyeOpen;
        }
    };

    inputWrapper.appendChild(lockIcon);
    inputWrapper.appendChild(inputPassword);
    inputWrapper.appendChild(eyeIcon);

    form_group.appendChild(inputWrapper);
    form_group.appendChild(submit);
    form_group.appendChild(inputMeta);

    return form_group;
}



// Exponer globalmente para compatibilidad
window.createSelects = createSelects;
window.validateProductionForm = validateProductionForm;
window.modifySelects = modifySelects;
window.disabledSelects = disabledSelects;
window.createInteractivePickerField = createInteractivePickerField;
window.getShiftPlaceholderHtml = getShiftPlaceholderHtml;
window.createInteractiveShiftTimeField = createInteractiveShiftTimeField;
window.createInputs = createInputs;
window.createInputsWithValue = createInputsWithValue;
window.createBtnSubmit_editMeta = createBtnSubmit_editMeta;
window.insertSelects = insertSelects;
window.createInputPassword = createInputPassword;

export {
    createSelects,
    validateProductionForm,
    modifySelects,
    disabledSelects,
    createInteractivePickerField,
    getShiftPlaceholderHtml,
    createInteractiveShiftTimeField,
    createInputs,
    createInputsWithValue,
    createBtnSubmit_editMeta,
    insertSelects,
    createInputPassword
};
