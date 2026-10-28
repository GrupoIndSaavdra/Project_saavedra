// ══════════════════════════════════════════════════════════
// MÓDULO DASHBOARD — Renderizador de Secciones y Procesos de OT
// ══════════════════════════════════════════════════════════
import {
    PTACardComponent,
    FundicionChecklistCard,
    PlaneacionChecklistCard,
    TermicoChecklistCard
} from "./checklistCards.js";

class Dashboard {
    constructor(wOrderArray) {
        this.wOrderArray = wOrderArray;
    }
    //Función para general carrusel
    generateSection($workOrder, $class, $processes) {
        let divHeader = document.createElement("div");
        divHeader.className = "header";
        let workOrderDiv = document.createElement("div");
        workOrderDiv.className = "work-order";
        let h2 = document.createElement("h2");
        h2.className = "work-order-title text-header";
        h2.innerHTML = "Orden de trabajo: " + $workOrder;
        let moldingLabel = document.createElement("label");
        moldingLabel.className = "molding-label text-header";
        label.innerHTML = "Moldura: " + $workOrder["moldura"];
        let classLabel = document.createElement("label");
        classLabel.className = "class-label text-header";
        classLabel.innerHTML = "Clase: " + $class;

        //Insertar los elementos en el div
        workOrderDiv.appendChild(h2);
        workOrderDiv.appendChild(moldingLabel);
        workOrderDiv.appendChild(classLabel);
        divHeader.appendChild(workOrderDiv);
        section.appendChild(divHeader);
        return section;
    }
    createSections(otIdsToRender = null) {
        let body = document.querySelector("body");
        let otIds = (otIdsToRender && otIdsToRender.length > 0)
            ? otIdsToRender
            : (window.orderedOtIds && window.orderedOtIds.length > 0
                ? window.orderedOtIds
                : Object.keys(this.wOrderArray));

        let loadMoreWrapper = document.getElementById("load-more-ot-wrapper");

        otIds.forEach((wOrderName) => {
            let workOrder = this.wOrderArray[wOrderName];
            if (!workOrder) return;

            Object.values(workOrder["classes"]).forEach((classArray, indexClass) => {
                let section = document.createElement("section");
                section.className = "section";
                section.id = `ot-section-${wOrderName}`;
                section.setAttribute("data-ot-id", wOrderName);
                section.setAttribute("data-molding", workOrder["molding"] || "");
                let className = Object.keys(workOrder["classes"])[indexClass];
                let headerSection = this.generateHeaderofWorkOrder(wOrderName, workOrder["molding"], className, classArray);
                let processesSection = document.createElement("div");
                processesSection.className = "processes-section";

                Object.values(classArray["processes"]).forEach((processesArray, indexProcess) => {
                    let processName = Object.keys(classArray["processes"])[indexProcess]

                    // ── Cálculo de piezas disponibles (limitPieces) ──────────────────────────
                    // Se toma la consignación original y se le descuentan TODAS las bajas
                    // acumuladas de los procesos ANTERIORES a este.
                    // Esto garantiza que si en cepillado hay 1 baja de 128,
                    // el siguiente proceso tiene 127 disponibles (no las que ya se procesaron).
                    let accumulatedBad = 0;
                    for (let p = 0; p < indexProcess; p++) {
                        let prevProcKey = Object.keys(classArray["processes"])[p];
                        accumulatedBad += (classArray["processes"][prevProcKey]["pieces"]["bad"] || 0);
                    }
                    let limitPieces = classArray["pieces"] - accumulatedBad;

                    processesSection.appendChild(this.generateProcessSection(processesArray, processName, limitPieces, classArray["pieces"], indexProcess + 1));
                });

                // ── Card PTA: instanciar SIEMPRE si la clase tiene soldaduraPTA activo ─
                // Se usa el flag `hasPTA` enviado por el servidor (campo soldaduraPTA != 0
                // en la tabla de procesos), que es la fuente de verdad más confiable.
                // Si aún no hay piezas (ptaData=null), el componente arrancará en modo
                // dormido y se montará solo en cuanto el polling detecte la primera pieza.
                if (classArray["hasPTA"] === true) {
                    let userProfile = document.getElementById("profile");
                    // Perfiles permitidos: 1=Master, 2=Operador, 3=Admin, 4=Calidad, 5=Almacén
                    if (userProfile && (userProfile.value == "1" || userProfile.value == "2" || userProfile.value == "3" || userProfile.value == "4" || userProfile.value == "5")) {
                        const classId = classArray["id"];
                        const ptaData = (window.ptaCardsData && window.ptaCardsData[wOrderName] && window.ptaCardsData[wOrderName][classId])
                            ? window.ptaCardsData[wOrderName][classId]
                            : { totalPTA: 0, liberadas: 0 };
                        this.generatePTASection(ptaData, wOrderName, classId, classArray, processesSection);
                    }
                }

                section.appendChild(headerSection);

                // ── Checklist de Fundición (entre header y procesos) ──────────
                // Guard de perfil: 1=Master, 2=Admin, 4=Calidad, 5=Almacén.
                let userProfileChecklist = document.getElementById("profile");
                // Perfiles permitidos: 1=Master, 2=Admin, 4=Calidad, 5=Almacén
                if (
                    userProfileChecklist &&
                    (userProfileChecklist.value === "1" || userProfileChecklist.value === "2" || userProfileChecklist.value === "3" ||
                        userProfileChecklist.value === "4" || userProfileChecklist.value === "5")
                ) {
                    section.classList.add('section--has-checklist');
                    const checklistWrapper = document.createElement('div');
                    checklistWrapper.className = 'fundicion-checklist-wrapper';
                    checklistWrapper.hidden = false;
                    checklistWrapper.style.gap = '20px';
                    checklistWrapper.style.flexWrap = 'nowrap';
                    checklistWrapper.style.alignItems = 'flex-start';

                    if (window.fundicionChecklist && window.fundicionChecklist[wOrderName] && window.fundicionChecklist[wOrderName][className]) {
                        const levantamientoCard = new FundicionChecklistCard(
                            wOrderName,
                            window.fundicionChecklist[wOrderName][className],
                            checklistWrapper,
                            className   // instanceId único por sección de clase
                        );
                        if (levantamientoCard.root) {
                            levantamientoCard.root.style.flex = '1';
                            levantamientoCard.root.style.minWidth = '300px';
                        }
                    } else {
                        // Render empty state card
                        const emptyCard = document.createElement('div');
                        emptyCard.className = 'fundicion-checklist-card empty-checklist-card inactive-process';
                        emptyCard.style.borderLeftColor = '#424141';
                        emptyCard.style.flex = '1';
                        emptyCard.style.minWidth = '300px';
                        emptyCard.style.pointerEvents = 'none';
                        emptyCard.innerHTML = `
                                <div class="checklist-header" style="border-bottom: none; margin-bottom: 0; padding-bottom: 0; justify-content: center;">
                                    <span class="checklist-title" style="color: #ffffff;">
                                        Levantamiento de OT No Disponible
                                    </span>
                                </div>
                                <div class="empty-checklist-text" style="font-size: 0.95rem; color: rgba(255, 255, 255, 0.7); margin-top: 0.8rem; text-align: center;">
                                    Esta orden de trabajo es antigua o no requiere el levantamiento de OT.
                                </div>
                            `;
                        checklistWrapper.appendChild(emptyCard);
                    }

                    // Card 2: Tratamiento Térmico (Reactiva)
                    const termicoCard = new TermicoChecklistCard(
                        wOrderName,
                        className,
                        { pieces: classArray["pieces"] || 0, tratadas: classArray["tratadas"] || 0 },
                        checklistWrapper
                    );

                    // Card 3: Planeación (Reactiva)
                    const planeacionCard = new PlaneacionChecklistCard(
                        wOrderName,
                        classArray["id"],
                        checklistWrapper
                    );
                    if (planeacionCard.root) {
                        planeacionCard.root.style.flex = '1';
                        planeacionCard.root.style.minWidth = '300px';
                    }

                    // ── [FIN] DUMMY CARDS Y CARDS REACTIVAS ──

                    section.appendChild(checklistWrapper);
                }
                // ─────────────────────────────────────────────────────────────

                section.appendChild(processesSection);
                if (loadMoreWrapper && loadMoreWrapper.parentNode) {
                    loadMoreWrapper.parentNode.insertBefore(section, loadMoreWrapper);
                } else {
                    body.appendChild(section);
                }
            });
        });
    }
    generateHeaderofWorkOrder(wOrderName, moldingName, className, classArray) {
        let valueText = [
            [
                `${wOrderName} ${moldingName}`,
                `${className}`,
                `<span class="header-label">Fecha de inicio:</span> <span class="header-value highlight-date">${classArray["startDate"]}</span>`,
                `<span class="header-label">Fecha de término:</span> <span class="header-value highlight-date">${classArray["endDate"]}</span>`,
            ],
            [
                `<span class="header-label" style="display: block; font-size: 0.85em; color: #94a3b8; margin-bottom: 5px; letter-spacing: 0.5px;">Pedido:</span> <span class="header-value highlight-value" style="display: block; font-size: 1.4em; font-weight: bold; text-align: center;">${classArray["order"]}</span>`,
                `<span class="header-label" style="display: block; font-size: 0.85em; color: #94a3b8; margin-bottom: 5px; letter-spacing: 0.5px;">Pedido + consignación:</span> <span class="header-value highlight-value" style="display: block; font-size: 1.4em; font-weight: bold; text-align: center;">${classArray["pieces"]}</span>`,
                `<span class="header-label" style="display: block; font-size: 0.85em; color: #94a3b8; margin-bottom: 5px; letter-spacing: 0.5px;">Piezas entregadas:</span> <span class="header-value highlight-value" style="display: block; font-size: 1.4em; font-weight: bold; color: #10b981; text-align: center;">${classArray["entregadas"] || 0}</span>`,
                `<span class="header-label" style="display: block; font-size: 0.85em; color: #94a3b8; margin-bottom: 5px; letter-spacing: 0.5px;">Piezas completadas:</span> <span class="header-value completed-value" style="display: block; font-size: 1.4em; font-weight: bold; text-align: center;">${this.getCompletedPieces(classArray)}</span>`,
            ],
        ];
        let classText = [
            ["workOrder-text", "class-text", "start-date-text", "end-date-text"],
            ["order-text", "pieces-text", "delivered-pieces-text", "completed-pieces-text"],
        ];

        let header_section = document.createElement("div");
        header_section.className = "header-section";

        let a = document.createElement("a");
        let baseUrl = window.baseUrl ? window.baseUrl : '';
        a.href = `${baseUrl}/finishOrder/${wOrderName}/${className}`;
        a.className = "finish-order";
        a.innerHTML = "Finalizar pedido";

        a.addEventListener("click", async (e) => {
            e.preventDefault();
            if (a.dataset.loading === "true") return;

            // Fricción Positiva: Confirmación con verbo de acción y sin asunciones
            const confirmResult = await window.gisConfirm({
                title: `¿Finalizar pedido de ${className}?`,
                text: `La clase se marcará como terminada y se retirará del progreso de producción. Solo se finalizará si todas las piezas requeridas están completas en todos sus procesos.`,
                confirmButtonText: "Finalizar pedido",
                confirmButtonColor: "#033966",
                cancelButtonText: "Cancelar"
            });

            if (!confirmResult.isConfirmed) return;

            a.dataset.loading = "true";
            let originalText = a.innerHTML;
            a.innerHTML = "Procesando...";
            a.style.opacity = "0.7";
            a.classList.add("pointer-events-none");

            try {
                let response = await fetch(a.href, {
                    headers: {
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                let data = await response.json().catch(() => ({}));
                const isError = !response.ok || data.status === "error" || (Array.isArray(data) && data[0] === "error");
                const mensaje = data.message || (Array.isArray(data) ? data[1] : null) || (isError ? "No se pudo finalizar el pedido debido a un error en el servidor." : "Se ha finalizado el pedido correctamente.");

                if (isError) {
                    await window.gisAlert({
                        title: "Completar piezas requeridas",
                        text: mensaje,
                        confirmButtonText: "Entendido",
                        confirmButtonColor: "#033966"
                    });
                } else {
                    await window.gisAlert({
                        title: "Pedido finalizado",
                        text: mensaje,
                        confirmButtonText: "Entendido",
                        confirmButtonColor: "#033966"
                    });
                    location.reload();
                }

            } catch (err) {
                console.error("Error al finalizar pedido:", err);
                await window.gisAlert({
                    title: "Error de comunicación",
                    text: "No fue posible conectar con el servidor para finalizar el pedido.",
                    confirmButtonText: "Entendido",
                    confirmButtonColor: "#033966"
                });
            } finally {
                a.innerHTML = originalText;
                a.style.opacity = "1";
                a.classList.remove("pointer-events-none");
                delete a.dataset.loading;
            }
        });

        for (let i = 0; i < valueText.length; i++) {
            let div = document.createElement("div");

            if (i === 1) {
                // Fila 2: Renderizarla como un div completo con clase "title-div"
                div.className = "title-div";
                div.classList.add("pip-card-div-1");

                // Título
                let titleDiv = document.createElement("div");
                titleDiv.classList.add("pip-card-title-1");
                titleDiv.style.width = "100%";

                let titleSpan = document.createElement("span");
                titleSpan.style.color = "#fff";
                titleSpan.style.fontSize = "1.25rem";
                titleSpan.style.fontWeight = "800";
                titleSpan.hidden = false;
                titleSpan.style.textTransform = "uppercase";
                titleSpan.style.letterSpacing = "0.03em";
                titleSpan.textContent = "Recepción de Material";
                titleDiv.appendChild(titleSpan);
                div.appendChild(titleDiv);

                // Contenedor interno para los valores (abajo, distribuidos horizontalmente)
                let itemsContainer = document.createElement("div");
                itemsContainer.hidden = false;
                itemsContainer.classList.add("pip-items-container");
                itemsContainer.style.width = "100%";

                for (let j = 0; j < valueText[i].length; j++) {
                    let h3 = document.createElement("h3");
                    h3.className = classText[i][j];
                    h3.style.margin = "0";
                    h3.hidden = false;
                    h3.style.flexDirection = "column";
                    h3.style.alignItems = "center";
                    h3.style.justifyContent = "center";
                    h3.innerHTML = valueText[i][j];
                    itemsContainer.appendChild(h3);
                }
                div.appendChild(itemsContainer);
            } else {
                // Fila 1 (Izquierda): Datos de la OT y Clase alineados en una sola fila con cabecera principal
                div.className = "title-div";
                div.classList.add("pip-card-div-2"); // Permite posicionamiento absoluto del icono de prioridad

                // Inyectar icono de prioridad según orden de OTs
                let rawOtIdForPriority = parseInt(wOrderName, 10);
                let otPriority = -1;
                if (window.orderedOtIds) {
                    otPriority = window.orderedOtIds.map(x => parseInt(x, 10)).indexOf(rawOtIdForPriority) + 1;
                }
                if (otPriority > 0) {
                    let pBadge = document.createElement("div");
                    pBadge.className = "pip-p-badge";
                    pBadge.style.position = "absolute";
                    pBadge.style.top = "10px";
                    pBadge.style.left = "14px";
                    pBadge.style.display = "flex";
                    pBadge.style.flexDirection = "row";
                    pBadge.style.alignItems = "center";
                    pBadge.style.gap = "10px";
                    pBadge.style.zIndex = "5";

                    // Gradual visual decay: starts after priority 5
                    let badgeOpacity = 1.0;
                    let badgeScale = 1.0;
                    if (otPriority > 5) {
                        badgeOpacity = Math.max(0.45, 1.0 - (otPriority - 5) * 0.05);
                        badgeScale = Math.max(0.75, 1.0 - (otPriority - 5) * 0.025);
                    }
                    pBadge.style.opacity = badgeOpacity;
                    pBadge.style.transform = `scale(${badgeScale})`;
                    pBadge.style.transformOrigin = "left center";

                    let priorityImgPath = "";
                    let isPlataPriority = false;
                    if (otPriority === 1) priorityImgPath = `${window.baseUrl}/images/Color_Icons/Generales/uno.png`;
                    else if (otPriority === 2) priorityImgPath = `${window.baseUrl}/images/Color_Icons/Generales/dos.png`;
                    else if (otPriority === 3) priorityImgPath = `${window.baseUrl}/images/Color_Icons/Generales/tres.png`;
                    else if (otPriority === 4) priorityImgPath = `${window.baseUrl}/images/Color_Icons/Generales/cuatro.png`;
                    else if (otPriority === 5) priorityImgPath = `${window.baseUrl}/images/Color_Icons/Generales/cinco.png`;
                    else {
                        priorityImgPath = `${window.baseUrl}/images/Color_Icons/Generales/plata.png`;
                        isPlataPriority = true;
                    }

                    // Neon glow effect (gold for 1-5, silver for 6+)
                    let glowFilter = isPlataPriority
                        ? "drop-shadow(0 0 10px rgba(203, 213, 225, 0.85)) drop-shadow(0 1px 3px rgba(0,0,0,0.35))"
                        : "drop-shadow(0 0 10px rgba(251, 191, 36, 0.85)) drop-shadow(0 1px 3px rgba(0,0,0,0.35))";

                    pBadge.innerHTML = `
                        <div style="position: relative; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <img src="${priorityImgPath}" alt="${otPriority}" style="width: 100%; height: 100%; object-fit: contain; filter: ${glowFilter};">
                        </div>
                        <span style="color: #ffffff; font-weight: 900; font-size: 1.65rem; text-shadow: 0 2px 5px rgba(0, 0, 0, 0.95); line-height: 1;">${otPriority}</span>
                    `;
                    div.appendChild(pBadge);
                }

                // Cabecera principal: Información de la OT
                let titleDiv = document.createElement("div");
                titleDiv.style.borderBottom = "none";
                titleDiv.style.paddingBottom = "0.6rem";
                titleDiv.style.textAlign = "center";
                titleDiv.style.width = "100%";
                titleDiv.style.marginBottom = "0.2rem";

                let titleSpan = document.createElement("span");
                titleSpan.style.color = "#fff";
                titleSpan.style.fontSize = "1.25rem";
                titleSpan.style.fontWeight = "800";
                titleSpan.hidden = false;
                titleSpan.style.textTransform = "uppercase";
                titleSpan.style.letterSpacing = "0.03em";
                titleSpan.textContent = "Información de la OT";
                titleDiv.appendChild(titleSpan);
                div.appendChild(titleDiv);

                // Fila de datos: OT (izquierda) y Clase (derecha) - sin líneas de separación
                let otClassRow = document.createElement("div");
                otClassRow.hidden = false;
                otClassRow.style.flexDirection = "row";
                otClassRow.style.justifyContent = "space-between";
                otClassRow.style.alignItems = "center";
                otClassRow.style.width = "100%";
                otClassRow.style.marginBottom = "0.4rem";

                // OT text
                let otH3 = document.createElement("h3");
                otH3.className = "workOrder-text";
                otH3.classList.add("pip-ot-h3");
                otH3.innerHTML = `<span style="font-size: 0.75em; color: #94a3b8; font-weight: 600; text-transform: uppercase; margin-right: 5px; letter-spacing: 0.5px;">OT:</span>${wOrderName} ${moldingName}`;

                // Clase text
                let classH3 = document.createElement("h3");
                classH3.className = "class-text";
                classH3.classList.add("pip-class-h3");
                classH3.innerHTML = `<span style="font-size: 0.75em; color: #94a3b8; font-weight: 600; text-transform: uppercase; margin-right: 5px; letter-spacing: 0.5px;">Clase:</span>${className}`;

                otClassRow.appendChild(otH3);
                otClassRow.appendChild(classH3);
                div.appendChild(otClassRow);

                // Fechas (Start Date and End Date) - las fechas sí llevan línea de separación (Start Date tiene línea, End Date no en el CSS)
                for (let j = 2; j < valueText[i].length; j++) {
                    let h3 = document.createElement("h3");
                    h3.className = classText[i][j];
                    h3.style.margin = "0";
                    h3.innerHTML = valueText[i][j];
                    div.appendChild(h3);
                }
            }

            header_section.appendChild(div);

            if (i === 0) {
                header_section.appendChild(a);
            }
        }

        return header_section;
    }

    getCompletedPieces(classArray) {
        //Obtener las piezas del ultimo proceso de la clase
        if (!classArray["processes"] || Object.keys(classArray["processes"]).length === 0) {
            return 0;
        }
        let completedPieces;
        let lastProcess = Object.keys(classArray["processes"])[Object.keys(classArray["processes"]).length - 1];
        if (lastProcess == "Soldadura PTA" || lastProcess == "Soldadura") {
            // Si ultimo proceso es Soldadura o Soldadura PTA
            let otherProcess = lastProcess == "Soldadura PTA" ? "Soldadura" : "Soldadura PTA";
            // Si se incluyen los dos procesos en la clase, sumar las piezas buenas
            if (Object.keys(classArray["processes"]).includes(otherProcess)) {
                completedPieces =
                    classArray["processes"][lastProcess]["pieces"]["good"] +
                    classArray["processes"][otherProcess]["pieces"]["good"];
            } else {
                completedPieces = classArray["processes"][lastProcess]["pieces"]["good"];
            }
        } else {
            completedPieces = Object.values(classArray["processes"])[Object.keys(classArray["processes"]).length - 1][
                "pieces"
            ]["good"];
        }
        return completedPieces;
    }
    generateProcessSection(processesArray, processName, limitPieces, pedido, processNumber) {
        let processSection = document.createElement("div");
        processSection.className = "process-section";
        processSection.style.position = "relative"; // Permitir que el badge de cotas sea absoluto

        let cotasBadge = document.createElement("img");
        cotasBadge.className = "cotas-badge";
        const baseUrl = window.baseUrl || (window.location.origin + '/');
        const slash = baseUrl.endsWith('/') ? '' : '/';

        cotasBadge.style.position = "absolute";
        cotasBadge.style.top = "10px";
        cotasBadge.style.right = "10px";
        cotasBadge.style.width = "32px";
        cotasBadge.style.height = "32px";
        cotasBadge.style.zIndex = "5";

        if (processesArray["requiresCotas"] === false) {
            cotasBadge.src = `${baseUrl}${slash}images/Color_Icons/Generales/Aprobado.png`;
            cotasBadge.title = "No requiere cotas";
            cotasBadge.classList.add("cotas-badge-glow");
        } else if (processesArray["hasCotas"]) {
            cotasBadge.src = `${baseUrl}${slash}images/Color_Icons/Generales/icono_cotas.png`;
            cotasBadge.title = "Cotas registradas correctamente";
            cotasBadge.classList.add("cotas-badge-glow");
        } else {
            cotasBadge.src = `${baseUrl}${slash}images/Color_Icons/Generales/icono_sin_cotas.png`;
            cotasBadge.title = "Falta registrar cotas";
            cotasBadge.style.opacity = "0.7";
        }
        processSection.appendChild(cotasBadge);

        // Badge con número de proceso en la esquina superior izquierda
        let numberBadge = document.createElement("span");
        numberBadge.className = "process-number-badge";
        numberBadge.textContent = String(processNumber).padStart(2, '0');
        processSection.appendChild(numberBadge);

        let processTitle = document.createElement("h3");
        processTitle.className = "process-title";
        processTitle.innerHTML = processName;
        processSection.appendChild(processTitle);

        // "Total disponible" = las piezas que entran a este proceso (buenas del proceso anterior,
        // o la consignación si es el primero). Las bajas de ESTE proceso se ven en la barra roja.
        let limitLabel = document.createElement("label");
        limitLabel.className = "limit-label";
        limitLabel.style.fontSize = "12px";
        limitLabel.style.color = "#fff";
        limitLabel.innerHTML = `Total disponible: ${limitPieces}`;
        processSection.appendChild(limitLabel);

        let pieces = [processesArray["pieces"]["good"], processesArray["pieces"]["bad"]];

        // Si no hay piezas procesadas (buenas ni malas), oscurecer los textos
        if (pieces[0] === 0 && pieces[1] === 0) {
            processSection.classList.add("inactive-process");
        }

        // El 100% de la barra verde es sobre limitPieces (piezas disponibles de entrada).
        // Esto garantiza que si todos los procesos previos están bien y este también,
        // la barra llega exactamente a 100%.
        let goodPercentage = limitPieces == 0 ? (pieces[0] > 0 ? 100 : 0) : (pieces[0] * 100) / limitPieces;
        goodPercentage = Math.min(100, goodPercentage); // Clamp a 100%

        let hue = 30 + (goodPercentage * 0.9);
        processSection.style.setProperty('--glow-hue', hue);

        // Tooltip descriptivo
        if (goodPercentage === 0) {
            processSection.title = "Proceso aún no iniciado. Esperando primeras piezas.";
        } else if (goodPercentage < 50) {
            processSection.title = `Progreso bajo (${goodPercentage.toFixed(1)}%). Se requiere atención.`;
        } else if (goodPercentage < 100) {
            processSection.title = `Progreso estable (${goodPercentage.toFixed(1)}%). Buen ritmo de trabajo.`;
        } else {
            processSection.title = "¡Proceso completado exitosamente al 100%!";
        }

        // Borde verde si el proceso está concluido (100%)
        if (goodPercentage >= 100) {
            processSection.style.borderColor = '#4ade80';
            processSection.style.boxShadow = 'none';
            numberBadge.style.color = '#4ade80';
            numberBadge.style.borderColor = 'rgba(74, 222, 128, 0.4)';
            numberBadge.style.background = 'rgba(74, 222, 128, 0.15)';
        } else if (goodPercentage > 0) {
            processSection.style.borderColor = `hsl(${hue}, 100%, 50%)`;
            processSection.style.boxShadow = 'none';
            numberBadge.style.color = `hsl(${hue}, 100%, 60%)`;
            numberBadge.style.borderColor = `hsla(${hue}, 100%, 50%, 0.4)`;
            numberBadge.style.background = `hsla(${hue}, 100%, 50%, 0.15)`;
        } else {
            processSection.style.borderColor = '';
            processSection.style.boxShadow = '';
            numberBadge.style.color = '#38bdf8';
            numberBadge.style.borderColor = 'rgba(56, 189, 248, 0.35)';
            numberBadge.style.background = 'rgba(56, 189, 248, 0.12)';
        }

        for (let i = 0; i < pieces.length; i++) {
            // Crear barra de progreso con el estilo de track y píldora de PTA
            let progressBar = document.createElement("div");
            progressBar.className = i == 0 ? "progress-bar bar-good-track" : "progress-bar bar-bad-track";

            let progress = document.createElement("div");
            progress.className = i == 0 ? "good-progress progress" : "bad-progress progress";

            // Ambas barras (buenas y malas) se calculan sobre el total disponible de ENTRADA (limitPieces)
            let percentage = limitPieces == 0 ? (pieces[i] > 0 ? 100 : 0) : (pieces[i] * 100) / limitPieces;
            let displayPercentage = percentage;
            percentage = Math.min(100, percentage); // Clamp para el width visual

            progress.style.width = `${percentage}%`;

            // Gradiente vibrante dinámico para buenas y malas
            if (i == 0 && percentage > 0) {
                if (percentage >= 100) {
                    progress.style.background = "linear-gradient(90deg, #10b981 0%, #059669 100%)";
                } else {
                    let hue = 30 + (percentage * 0.9);
                    progress.style.background = `linear-gradient(90deg, hsl(${hue}, 100%, 45%) 0%, hsl(${hue}, 100%, 35%) 100%)`;
                }
            } else if (i == 1 && percentage > 0) {
                progress.style.background = "linear-gradient(90deg, #ef4444 0%, #b91c1c 100%)";
            }

            displayPercentage = displayPercentage != 0 ? displayPercentage.toFixed(1) : 0;
            let div = document.createElement("div");
            div.className = "progress-percentage";
            // Formato idéntico al de PTA con viñeta (•) y texto nítido
            div.innerHTML = pieces[i] == 1
                ? `${displayPercentage}% • ${pieces[i]} pieza`
                : `${displayPercentage}% • ${pieces[i]} piezas`;

            progressBar.appendChild(progress);
            progressBar.appendChild(div);
            processSection.appendChild(progressBar);
        }

        //Agregar evento al div de progreso
        processSection.addEventListener("click", () => {
            this.generateDivBadPieces(processName, processesArray["piecesBadData"]);

        });
        return processSection;
    }

    // ── Card especial para Resultados de Soldadura PTA ──────────────────────
    generatePTASection(ptaData, otId, classId, classArray, container) {
        // Monta el componente reactivo directamente en el container dado.
        // El componente gestiona su propio ciclo de vida.
        new PTACardComponent(otId, classId, ptaData, container, classArray);
    }
    generateDivBadPieces(processName, badPieces) {
        //Creacion del div de opacidad de fondo
        let div = document.createElement("div");
        div.className = "opacity-div";

        //Creacion del div en donde se mostrara la tabla de las piezas malas
        let modal = document.createElement("div");
        modal.className = "modal";

        //Creacion del titulo del proceso al que se da click
        let modalTitle = document.createElement("h2");
        modalTitle.className = "modal-title";
        modalTitle.innerHTML = `Proceso: ${processName}`;
        modal.appendChild(modalTitle);

        //Creacion del boton de cerrar el modal
        let modalClose = document.createElement("button");
        modalClose.className = "modal-close";

        let imageClose = document.createElement("img");
        imageClose.className = "img-close";
        imageClose.src = window.cerrarImgUrl;
        modalClose.appendChild(imageClose);

        modalClose.addEventListener("click", function () {
            document.body.removeChild(div);
            document.body.classList.remove("overflow-hidden"); document.body.classList.add("overflow-auto");
        });
        modal.appendChild(modalClose);

        //Creacion de la tabla de las piezas malas
        let tableWrapper = document.createElement("div");
        tableWrapper.className = "bad-pieces-table-wrapper";
        let table = this.createTableBadPieces(badPieces, processName);
        tableWrapper.appendChild(table);
        modal.appendChild(tableWrapper);

        div.addEventListener("click", function (e) {
            if (e.target === div) {
                document.body.removeChild(div);
                document.body.classList.remove("overflow-hidden"); document.body.classList.add("overflow-auto");
            }
        });
        div.appendChild(modal);
        document.body.appendChild(div);
        document.body.classList.remove("overflow-auto"); document.body.classList.add("overflow-hidden");
    }
    createTableBadPieces(badPieces, processName) {
        let table = document.createElement("table");
        table.className = "bad-pieces-table";
        let thead = document.createElement("thead");
        let headerRow = document.createElement("tr");
        let headers =
            processName == "Operacion Equipo"
                ? ["Pieza", "Numero de juego", "Operador", "Proceso", "Operacion", "Error"]
                : ["Pieza", "Numero de juego", "Operador", "Proceso", "Error"];

        //Insertar encabezados de la tabla
        headers.forEach((header) => {
            let th = document.createElement("th");
            th.innerHTML = header;
            th.style.width = (100 / headers.length) + "%"; // Ajustar el ancho de las columnas
            headerRow.appendChild(th);
        });

        //Insertar los datos de cada una de las piezas malas
        //prettier-ignore
        let tbody = document.createElement("tbody");
        if (Object.keys(badPieces).length > 0) {
            // Para Soldadura PTA, agrupar por número de juego (setNumber)
            if (processName.includes("Soldadura PTA")) {
                let setGroups = {};
                Object.values(badPieces).forEach((piece) => {
                    let key = piece["setNumber"];
                    if (!setGroups[key]) {
                        setGroups[key] = {
                            pieces: [],
                            setNumber: piece["setNumber"],
                            operator: piece["operator"],
                            process: piece["process"],
                            error: piece["error"],
                        };
                    }
                    setGroups[key].pieces.push(piece["piece"]);
                });
                Object.values(setGroups).forEach((group) => {
                    let row = document.createElement("tr");
                    let pieceData = [
                        group.pieces.join(", "),
                        group.setNumber,
                        group.operator,
                        group.process,
                        group.error,
                    ];
                    pieceData.forEach((data) => {
                        let td = document.createElement("td");
                        td.innerHTML = data;
                        row.appendChild(td);
                    });
                    tbody.appendChild(row);
                });
            } else {
                Object.values(badPieces).forEach((piece) => {
                    let row = document.createElement("tr");
                    let pieceData =
                        processName == "Operacion Equipo"
                            ? [
                                piece["piece"],
                                piece["setNumber"],
                                piece["operator"],
                                piece["process"],
                                piece["operation"],
                                piece["error"],
                            ]
                            : [piece["piece"], piece["setNumber"], piece["operator"], piece["process"], piece["error"]];
                    pieceData.forEach((data) => {
                        let td = document.createElement("td");
                        td.innerHTML = data;
                        row.appendChild(td);
                    });
                    tbody.appendChild(row);
                });
            }
        } else {
            let row = document.createElement("tr");
            let td = document.createElement("td");
            td.colSpan = headers.length;
            td.classList.add("no-bad-pieces");
            td.innerHTML = "No hay piezas malas registradas para este proceso.";
            row.appendChild(td);
            tbody.appendChild(row);
        }
        thead.appendChild(headerRow);
        table.appendChild(thead);
        table.appendChild(tbody);
        return table;
    }
}




export { Dashboard };
