document.addEventListener('DOMContentLoaded', function() {
    const idOperador = document.getElementById('id_operador');
    const idSoldadura = document.getElementById('id_soldadura');
    const fechaEntrega = document.getElementById('fecha_entrega');
    const cantidadEntregada = document.getElementById('cantidad_entregada');
    const btnGenerar = document.getElementById('btnGenerar');

    // Validación en tiempo real
    function validarFormulario() {
        const operador = idOperador.value.trim();
        const soldadura = idSoldadura.value.trim();
        const fecha = fechaEntrega.value.trim();
        const cantidad = cantidadEntregada.value.trim();
        
        if (operador && soldadura && fecha && cantidad) {
            btnGenerar.disabled = false;
            btnGenerar.classList.remove('btn-secondary');
            btnGenerar.classList.add('btn-primary');
        } else {
            btnGenerar.disabled = true;
            btnGenerar.classList.remove('btn-primary');
            btnGenerar.classList.add('btn-secondary');
        }
    }

    // Event listeners
    [idOperador, idSoldadura, fechaEntrega, cantidadEntregada].forEach(field => {
        field.addEventListener('change', validarFormulario);
        field.addEventListener('input', validarFormulario);
    });

    // Animación del botón al enviar
    btnGenerar.addEventListener('click', function(e) {
        if (!this.disabled) {
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generando...';
            // No deshabilitar el botón aquí para permitir el envío del formulario
        } else {
            e.preventDefault(); // Solo prevenir si está deshabilitado
        }
    });

    // Inicializar validaciones
    validarFormulario();

    // Copiar texto del QR al portapapeles
    window.copiarTextoQR = function() {
        const textoMostrado = document.querySelector('.text-muted');
        let texto = '';
        
        if (textoMostrado) {
            // Extraer el texto entre comillas
            const match = textoMostrado.textContent.match(/"([^"]*)"/); 
            texto = match ? match[1] : textoQR.value;
        } else {
            texto = textoQR.value;
        }
        
        navigator.clipboard.writeText(texto).then(function() {
            mostrarNotificacion('Texto copiado al portapapeles', 'success');
        }).catch(function() {
            // Fallback para navegadores que no soportan clipboard API
            const textArea = document.createElement('textarea');
            textArea.value = texto;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            mostrarNotificacion('Texto copiado al portapapeles', 'success');
        });
    };

    // Función para mostrar notificaciones
    function mostrarNotificacion(mensaje, tipo = 'info') {
        const notificacion = document.createElement('div');
        notificacion.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
        notificacion.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notificacion.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notificacion);
        
        // Auto-remover después de 3 segundos
        setTimeout(() => {
            if (notificacion.parentNode) {
                notificacion.remove();
            }
        }, 3000);
    }

    // Limpiar formulario
    window.limpiarFormulario = function() {
        idOperador.value = '';
        idSoldadura.value = '';
        fechaEntrega.value = new Date().toISOString().split('T')[0]; // Fecha actual
        cantidadEntregada.value = '';
        validarFormulario();
        
        // Remover QR mostrado si existe
        const qrContainer = document.querySelector('.qr-container');
        if (qrContainer) {
            qrContainer.parentElement.remove();
        }
    };
});