/**
 * Utilidades de UI compartidas para el módulo de producción
 */

export function createDivOpacity() {
    let div_padre = document.createElement("div");
    div_padre.className = "div-opacity";
    div_padre.id = "div-opacity";
    return div_padre;
}
window.createDivOpacity = createDivOpacity;

export function createInputPassword(name = "passwordAdmin", placeholderText = "Contraseña de Administrador") {
    let form_group = document.createElement("div");
    form_group.className = "form-group-password";
    form_group.classList.add("z-11000");

    let inputPassword = document.createElement("input");
    inputPassword.type = "password";
    inputPassword.name = name;
    inputPassword.placeholder = placeholderText;
    inputPassword.className = "normal-input input-password";
    inputPassword.required = true;
    inputPassword.autocomplete = "current-password";

    let submit = document.createElement("button");
    submit.type = "submit";
    submit.className = "btn-submit-password" + (name === "passwordQuality" ? " btn-quality" : "");
    const resultIconUrl = window.baseUrl ? (window.baseUrl + "/images/Light_Icons/Resultado-Light.png") : "/images/Light_Icons/Resultado-Light.png";
    submit.innerHTML = `
        <img src="${resultIconUrl}" class="btn-verify-icon" alt="Verificar">
        <span>Verificar</span>
    `;

    let inputMeta = document.createElement("input");
    inputMeta.type = "hidden";
    inputMeta.name = "meta";
    if (window.arrayData && window.arrayData["meta"]) {
        inputMeta.value = window.arrayData["meta"].id;
    }

    let inputWrapper = document.createElement("div");
    inputWrapper.className = "password-input-wrapper pos-relative align-center";

    // Icono de candado a la izquierda
    let lockIcon = document.createElement("span");
    lockIcon.className = "password-lock-icon";
    lockIcon.innerHTML = `
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
    `;

    const eyeOpen = `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
    const eyeClosed = `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;

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
window.createInputPassword = createInputPassword;
