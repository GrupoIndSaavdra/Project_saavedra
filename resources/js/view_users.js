document.addEventListener('DOMContentLoaded', function() {
    const contextMenu = document.getElementById('context-menu');
    
    if (!contextMenu) return;

    const tableRows = document.querySelectorAll('table tbody tr');

    // Mostrar el menú contextual cuando se hace clic derecho sobre una fila
    tableRows.forEach(row => {
        row.addEventListener('contextmenu', function(event) {
            event.preventDefault(); // Prevenir el menú contextual predeterminado del navegador

            // Obtener las dimensiones de la ventana
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;

            // Obtener las dimensiones del contenedor
            const container = document.querySelector('.container1');
            const containerRect = container.getBoundingClientRect();

            // Obtener las dimensiones del menú
            let menuWidth = contextMenu.offsetWidth;
            let menuHeight = contextMenu.offsetHeight;

            // Determinar la posición inicial del menú
            let left = event.pageX;
            let top = event.pageY;

            // Ajustar la posición del menú en relación con la columna (evento.target)
            const columnIndex = Array.from(row.cells).indexOf(event.target.closest('td')); // Obtener el índice de la columna
            const columnRect = row.cells[columnIndex].getBoundingClientRect();

            // Colocar el menú junto a la columna (a la derecha)
            left = columnRect.right; // Aparece justo al lado derecho de la columna
            top = columnRect.top;

            // Asegurarse de que el menú no se desborde a la derecha del contenedor
            if (left + menuWidth > containerRect.right) {
                left = containerRect.right - menuWidth - 10; // Asegurarse que no sobrepase el contenedor
            }

            // Asegurarse de que el menú no se desborde abajo del contenedor
            if (top + menuHeight > containerRect.bottom) {
                top = containerRect.bottom - menuHeight - 10; // Asegurarse que no sobrepase el contenedor
            }

            // Asegurarse de que el menú no se desborde a la derecha de la ventana
            if (left + menuWidth > windowWidth) {
                left = windowWidth - menuWidth - 10; // No permitir que se desborde de la ventana
            }

            // Asegurarse de que el menú no se desborde por encima de la ventana
            if (top + menuHeight > windowHeight) {
                top = windowHeight - menuHeight - 10; // No permitir que se desborde de la ventana
            }

            // Muestra el menú contextual en la posición ajustada
            contextMenu.classList.remove("hidden");
            contextMenu.style.left = `${left}px`;
            contextMenu.style.top = `${top}px`;
        });
    });

    // Cerrar el menú contextual si se hace clic fuera de él
    document.addEventListener('click', function(event) {
        if (!contextMenu.contains(event.target)) {
            contextMenu.classList.add("hidden");
        }
    });
});
