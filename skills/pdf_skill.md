# 📄 Guía de Generación de PDFs (PDF Skill) - Máximo Nivel

`domPDF` es una herramienta excelente en Laravel, pero su motor de renderizado HTML/CSS equivale a un navegador muy antiguo (piensa en Internet Explorer 8). Debes hackear visualmente la estructura para lograr resultados modernos.

## 1. La Regla de Oro: ¡Todo es una Tabla!
Nunca uses `div` para crear layouts de columnas. No uses flexbox, grid, ni floats.
**SOLO TABLAS.**

```html
<!-- ESTO FALLARÁ MÍSERAMENTE EN DOMPDF -->
<div style="display: flex; justify-content: space-between;">
    <div>Columna Izquierda</div>
    <div>Columna Derecha</div>
</div>

<!-- ✅ ESTO ES LO CORRECTO EN DOMPDF -->
<table width="100%" style="border-collapse: collapse;">
    <tr>
        <td width="50%" align="left" valign="top">Columna Izquierda</td>
        <td width="50%" align="right" valign="top">Columna Derecha</td>
    </tr>
</table>
```

## 2. Inyección de Imágenes y Logos
Nunca uses URLs relativas o el helper `asset()` si tu servidor no tiene DNS resuelto para sí mismo. Usa rutas absolutas del disco duro del servidor con `public_path()`.

```blade
<!-- Imagen con ruta física absoluta en el servidor -->
<img src="{{ public_path('images/lg_saavedra.png') }}" width="150" height="auto">
```
*Tip:* Siempre define el atributo `width`. `domPDF` puede enloquecer tratando de averiguar el tamaño natural de un PNG HD.

## 3. Paginación Numérica Automática (Scripts PHP Embebidos)
`domPDF` soporta inyectar scripts de PHP en el momento del renderizado para agregar números de página en el pie de página (footer) o en el encabezado.

```blade
<!-- Pon esto antes del cierre del </body> -->
<script type="text/php">
    if ( isset($pdf) ) {
        // Coordenadas x, y, texto, fuente, tamaño, color rgb
        $pdf->page_text(500, 800, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 10, array(0,0,0));
    }
</script>
```

## 4. Estilos Inline vs Estilos Bloque
Aunque es posible usar una hoja de estilos ligada o un bloque `<style>`, `domPDF` interpreta mucho mejor los estilos Inline para elementos críticos (márgenes, bordes de tabla).

```html
<!-- Mejor compatibilidad en domPDF -->
<td style="border: 1px solid #000; background-color: #f0f0f0; padding: 5px;">Dato</td>
```

## 5. Saltos de Página Controlados
Si una tabla es muy larga, puede que se corte mal en medio de una fila.
Para obligar a saltar de hoja:
```css
.page-break {
    page-break-after: always;
}
.avoid-break {
    page-break-inside: avoid; /* Intenta no partir este bloque a la mitad */
}
```

## 6. Configuración del Controlador (Orientación)
Si el reporte tiene más de 6-7 columnas de datos, la hoja vertical (`portrait`) no va a servir.

```php
// En el controlador:
$pdf = FacadePdf::loadView('reportes.vista', compact('datos'));

// Forzar hoja Carta en Horizontal (Landscape)
$pdf->setPaper('letter', 'landscape');

// Para visualizar en navegador en lugar de forzar descarga:
return $pdf->stream('Reporte.pdf'); 
```
