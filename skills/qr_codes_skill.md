# 🔍 Guía de Generación y Lectura de Códigos QR (QR Codes Skill)

Los códigos QR son el medio principal para el rastreo físico de materiales en planta (como soldaduras, lotes y botes). Su correcta generación y lectura previenen fallos operativos.

---

## 1. Estructura Estándar del Contenido del QR
Todo código QR generado por el sistema debe contener una cadena en formato **JSON** con los siguientes datos mínimos de identificación. Esto permite al backend identificar el tipo de entidad al escanearla:

```json
{
  "tipo": "bote",
  "id": 12,
  "matricula": "1401261534ALLTBS-002",
  "lote_id": 1,
  "numero_bote": 2
}
```

---

## 2. El Desafío de los Lectores de Hardware (Emulación de Teclado)
Los lectores de códigos de barras/QR físicos actúan emulando un teclado en los ordenadores de la planta. Dependiendo de la configuración del teclado del sistema operativo (Español vs. Inglés), el JSON puede llegar corrupto con caracteres especiales (Ej: `{"` se convierte en `¨[` o `:` se convierte en `Ñ`).

Todo endpoint que reciba lecturas de QR físicas **DEBE** implementar el método de parseo y limpieza para corregir la emulación del teclado.

### Patrón de Parseo Obligatorio en el Controlador:
```php
private function parseQRContent($qrContent)
{
    // 1. Intentar parsear como JSON directo (si se escribe o copia limpio)
    $qrData = json_decode($qrContent, true);
    if ($qrData && isset($qrData['tipo'])) {
        return $qrData;
    }

    // 2. Mapear y reemplazar caracteres corruptos comunes del lector de hardware
    $cleaned = $qrContent;
    $cleaned = str_replace('¨[', '{', $cleaned);
    $cleaned = str_replace('[Ñ[', ':"', $cleaned);
    $cleaned = str_replace('[Ñ', ':', $cleaned);
    $cleaned = str_replace('Ñ[', ':"', $cleaned);
    $cleaned = str_replace('[,', '",', $cleaned);
    $cleaned = str_replace("'", '-', $cleaned);
    $cleaned = str_replace('?', '_', $cleaned);

    // Reemplazar corchetes restantes que deberían ser comillas
    $cleaned = preg_replace('/\[([a-zA-Z_]+)\[/', '"$1":', $cleaned);
    $cleaned = preg_replace('/\[([0-9]+)/', '$1', $cleaned);
    $cleaned = str_replace('[', '"', $cleaned);

    // Asegurar cierre de llaves JSON
    $cleaned = rtrim($cleaned, ',');
    if (substr($cleaned, -1) !== '}') {
        $cleaned .= '"}';
    }

    // 3. Re-intentar parsear el JSON limpio
    $qrData = json_decode($cleaned, true);
    if ($qrData && isset($qrData['tipo'])) {
        return $qrData;
    }

    // 4. Si el JSON está severamente dañado, extraer campos clave usando Expresiones Regulares
    $data = [];
    if (preg_match('/tipo[^a-z]*([a-z_]+)/i', $qrContent, $matches)) {
        $data['tipo'] = strtolower($matches[1]);
    }
    if (preg_match('/[^a-z]id[^0-9]*(\d+)/i', $qrContent, $matches)) {
        $data['id'] = (int) $matches[1];
    }
    if (preg_match('/matricula[^a-zA-Z0-9]*([a-zA-Z0-9\-\']+)/i', $qrContent, $matches)) {
        $data['matricula'] = str_replace("'", '-', $matches[1]);
    }
    if (preg_match('/lote[_\?]?id[^0-9]*(\d+)/i', $qrContent, $matches)) {
        $data['lote_id'] = (int) $matches[1];
    }
    
    return !empty($data) ? $data : null;
}
```

---

## 3. Estándar de Generación de QR (Vistas y PDFs)
Para mostrar códigos QR en pantallas o reportes PDF, utiliza la API de QR Server o el generador instalado en el proyecto.

- **Generación en Blade/PDF mediante API (SVG Ligero):**
```html
<img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&format=svg&data={{ urlencode($qrContentJSON) }}" 
     alt="Código QR" 
     style="width: 100px; height: 100px;">
```

---

## 4. Diseño de la Interfaz de Escaneo en el Frontend
Dado que los operadores en planta usan lectores de mano rápidos:
1. Diseña un campo de texto oculto o enfocado automáticamente para recibir la lectura del QR.
2. Agrega un script para mantener el foco en el input sin importar dónde haga clic el usuario.
3. Los lectores envían automáticamente una tecla `Enter` al final de la lectura, lo que provoca la sumisión automática del formulario.

```html
<!-- Input enfocado que recibe la emulación de teclado del lector -->
<form id="form-escaner" action="{{ route('soldadura.escanear') }}" method="POST">
    @csrf
    <input type="text" id="qr_content" name="qr_content" class="input-escaner" placeholder="Escanee el QR aquí..." autofocus required autocomplete="off">
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const qrInput = document.getElementById('qr_content');
        
        // Mantener el foco de manera forzada en el campo del escáner
        document.addEventListener('click', () => {
            qrInput.focus();
        });
        
        // Enfocar de inmediato
        qrInput.focus();
    });
</script>
```
