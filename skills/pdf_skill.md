# 📄 Guía de Generación de PDFs (PDF Skill) - Máximo Nivel

> **📁 Directorio de Referencia:** `resources/views/pdf/ y Controladores que usen dompdf`
> *Usa los archivos en este directorio como base o inspiración al crear/modificar funcionalidades relacionadas con esta skill.*


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
Aunque es posible usar un bloque `<style>`, `domPDF` interpreta mucho mejor los estilos Inline para elementos críticos (márgenes, bordes de tabla).

```html
<!-- Mejor compatibilidad en domPDF -->
<td style="border: 1px solid #000; background-color: #f0f0f0; padding: 5px;">Dato</td>
```

## 5. Saltos de Página Controlados en Tablas
Si una tabla es muy larga, puede que se corte mal en medio de una fila (el texto de una celda se divide a la mitad).
- Para obligar a saltar de hoja:
```css
.page-break {
    page-break-after: always;
}
```
- Para evitar que filas individuales de una tabla se partan a la mitad entre dos páginas, aplica este estilo en el `tr`:
```css
tr {
    page-break-inside: avoid;
}
```
- Evita alturas fijas (`height`) en celdas de tablas de datos largos, permitiendo que la tabla crezca naturalmente.

## 6. Configuración de Fuentes (Fonts)
`domPDF` no soporta fuentes cargadas dinámicamente vía `@import` o enlaces web externos con fiabilidad (ej. Google Fonts directas de la CDN).
- **Mejor opción:** Usa fuentes estándar del sistema (como `Helvetica`, `Arial`, `Times-Roman` o `Courier`) para una máxima portabilidad.
- **Fuentes personalizadas:** Si necesitas una fuente específica del corporativo (como *Orbitron* o *Inter*), debes descargar el archivo `.ttf` en el servidor y declararlo con `@font-face` localmente:
```css
@font-face {
    font-family: 'Orbitron';
    src: url('{{ public_path("fonts/Orbitron-Regular.ttf") }}') format('truetype');
    font-weight: normal;
    font-style: normal;
}
```

## 7. Configuración del Controlador e Imágenes Remotas
Si vas a consumir imágenes de servidores externos (como generadores de QRs en línea: `api.qrserver.com`), debes habilitar la opción `isRemoteEnabled` en el objeto de configuración de Dompdf en tu controlador:

```php
use Barryvdh\DomPDF\Facade\Pdf;

public function generarReporte() {
    $pdf = Pdf::loadView('reportes.vista', compact('datos'));
    
    // Configuración para permitir imágenes externas y scripts PHP embebidos
    $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
    $pdf->getDomPDF()->set_option('isPhpEnabled', true);
    
    // Forzar hoja Carta en Horizontal (Landscape)
    $pdf->setPaper('letter', 'landscape');
    
    return $pdf->stream('Reporte.pdf'); 
}
```

---

## 8. Controladores de PDF en el Proyecto (Referencia Real)

| Controlador | Vista PDF | Descripción |
|---|---|---|
| `DibujosFundicionPdfController.php` | - | Genera PDFs de dibujos técnicos de modelos de fundición |
| `AyudasVisualesFundicionPdfController.php` | - | PDFs de ayudas visuales de fundición |
| `DibujosPdfController.php` | - | PDFs de dibujos técnicos de maquinado |
| `AyudasVisualesPdfController.php` | - | PDFs de ayudas visuales de maquinado |
| `ManualesPdfController.php` | - | PDFs de manuales técnicos |
| `ReporteProduccionController.php` | `pdf/pre_orden.blade.php` | Pre-órdenes de fundición en formato PDF |

### Vista PDF Real del Proyecto (`pre_orden.blade.php`)
La única vista en `resources/views/pdf/` es la pre-orden de fundición. Su estructura usa tablas puras (sin Flex/Grid). Al crear nuevas vistas PDF, usa esta como base:
```blade
{{-- resources/views/pdf/pre_orden.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #000; padding: 4px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td width="20%"><img src="{{ public_path('images/lg_saavedra.png') }}" width="80"></td>
            <td width="60%" align="center"><strong>PRE-ORDEN DE FUNDICIÓN</strong></td>
            <td width="20%">OT: {{ $ot }}</td>
        </tr>
    </table>
    {{-- Contenido principal con tablas... --}}
</body>
</html>
```
