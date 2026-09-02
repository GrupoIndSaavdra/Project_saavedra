// ── LEGEND ZOOM TOOLTIP (Almacén & Calidad) ──────────────────────────────────
// Tooltip flotante estilo Mercado Libre que aparece al hacer hover en los
// items de la leyenda de estados de modelo.

function initLegendZoom() {
    const tooltip = document.getElementById('legend-zoom-tooltip');
    const zoomCircle = document.getElementById('legend-zoom-circle');
    const zoomImg = document.getElementById('legend-zoom-img');
    const zoomLabel = document.getElementById('legend-zoom-label');

    if (!tooltip) return;

    document.querySelectorAll('.legend-compact-item').forEach(item => {
        item.addEventListener('mouseenter', (e) => {
            const circle = item.querySelector('span');
            const img = circle ? circle.querySelector('img') : null;
            const label = item.querySelectorAll('span')[1];

            if (!circle || !img || !label) return;

            const bgColor = circle.style.backgroundColor || window.getComputedStyle(circle).backgroundColor;
            const borderColor = circle.style.borderColor || window.getComputedStyle(circle).borderColor;
            const textColor = label.style.color || window.getComputedStyle(label).color;
            const imgSrc = img.src;
            const textContent = label.textContent;

            tooltip.style.borderColor = borderColor;
            zoomCircle.style.backgroundColor = bgColor;
            zoomCircle.style.borderColor = borderColor;
            zoomCircle.style.borderStyle = 'solid';
            zoomCircle.style.borderWidth = '3px';
            zoomImg.src = imgSrc;
            zoomLabel.textContent = textContent;
            zoomLabel.style.color = textColor;

            tooltip.style.display = 'flex';
            requestAnimationFrame(() => {
                tooltip.style.opacity = '1';
                tooltip.style.transform = 'scale(1.05)';
            });
        });

        item.addEventListener('mousemove', (e) => {
            const offsetX = 20;
            const offsetY = 20;
            let posX = e.clientX + offsetX;
            let posY = e.clientY + offsetY;

            const tooltipWidth = 170;
            const tooltipHeight = 180;

            if (posX + tooltipWidth > window.innerWidth - 10) {
                posX = e.clientX - tooltipWidth - offsetX;
            }
            if (posY + tooltipHeight > window.innerHeight - 10) {
                posY = e.clientY - tooltipHeight - offsetY;
            }

            tooltip.style.left = `${posX}px`;
            tooltip.style.top = `${posY}px`;
        });

        item.addEventListener('mouseleave', () => {
            tooltip.style.opacity = '0';
            tooltip.style.transform = 'scale(0.95)';
            setTimeout(() => {
                if (tooltip.style.opacity === '0') {
                    tooltip.style.display = 'none';
                }
            }, 100);
        });
    });
}

if (document.readyState !== 'loading') {
    initLegendZoom();
} else {
    document.addEventListener('DOMContentLoaded', initLegendZoom);
}
