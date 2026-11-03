# 🚀 Guía Maestra de Instalación, Configuración y Onboarding (Devs & Agentes IA)

> **Grupo Industrial Saavedra — Project Saavedra**  
> Esta guía explica paso a paso cómo clonar, instalar, configurar y arrancar el entorno de desarrollo y las herramientas de IA de forma óptima para cualquier nuevo programador o agente de Inteligencia Artificial.

---

## 📋 1. REQUISITOS PREVIOS DEL SISTEMA

Antes de iniciar la instalación, asegúrate de tener instalado en tu máquina:

| Herramienta | Versión Mínima | Propósito en el Proyecto |
|---|---|---|
| **PHP** | `>= 8.1` | Motor backend Laravel. Requiere extensiones: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `fileinfo`, `curl`. |
| **Composer** | `v2.x` | Gestor de paquetes PHP y dependencias Laravel / Livewire. |
| **Node.js & NPM** | `>= 18.x` | Entorno frontend, compilador Vite, Tailwind CSS y AST-grep. |
| **MySQL / MariaDB** | `5.7+` o `8.x` | Base de datos relacional del sistema (`saavedra` o `gis`). |
| **Python** | `>= 3.9` | Utilizado por las herramientas del agente de IA (Tree-sitter, NetworkX, Tiktoken). |
| **Git** | Cualquier versión moderna | Control de versiones y análisis de historial de aprendizajes. |

---

## ⚡ 2. INSTALACIÓN EXPRESS EN UN SOLO PASO (RECOMENDADO)

Si estás en **Windows**, el proyecto cuenta con un instalador automático que realiza toda la configuración (backend, frontend, llaves y entorno de IA) en 1 minuto:

```powershell
# 1. Clonar el repositorio
git clone <URL_DEL_REPOSITORIO>
cd Project_saavedra

# 2. Ejecutar el instalador único
.\setup.bat
```

> 💡 **¿Qué hace `setup.bat` automáticamente?**
> 1. Instala dependencias PHP con Composer (incluye Laravel Livewire v3).
> 2. Genera `.env` a partir de `.env.example` y crea la `APP_KEY`.
> 3. Publica la configuración de Livewire (`config/livewire.php`).
> 4. Instala dependencias de Node.js con NPM (Tailwind CSS v3, Vite, `@ast-grep/cli`).
> 5. Compila los assets frontend con `npm run build`.
> 6. Instala las librerías de Python de `tools/requirements.txt`.
> 7. Introspecciona la base de datos MySQL activa a `prisma/schema.prisma`.
> 8. Reconstruye el grafo de dependencias NetworkX, el `repomap.txt` y los tags de Ctags (`tags`).
> 9. Analiza los logs y commits recientes para sincronizar el buffer de aprendizajes (`aprendizajes_temp.md`).

---

## 🛠️ 3. INSTALACIÓN MANUAL (PASO A PASO)

Si prefieres ejecutar los comandos manualmente o te encuentras en un entorno Linux/macOS:

### Paso 1: Dependencias Backend
```bash
composer install --prefer-dist
```

### Paso 2: Archivo de Entorno y Llave
```bash
cp .env.example .env
php artisan key:generate
```

### Paso 3: Dependencias Frontend y Compilación
```bash
npm install
npm run build
```

### Paso 4: Enlace Simbólico de Almacenamiento (Archivos y Dibujos)
```bash
php artisan storage:link
```

### Paso 5: Herramientas de Inteligencia Artificial
```bash
pip install -r tools/requirements.txt
php tools/introspect_schema.php
python tools/dependency_graph.py --rebuild
python tools/repomap.py --save
python tools/generate_tags.py --build
php artisan app:analizar-aprendizajes
```

---

## ⚙️ 4. CONFIGURACIONES BÁSICAS CRÍTICAS (`.env`)

Abre el archivo [`.env`](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/.env) y ajusta los siguientes parámetros según tu entorno local:

### A. Conexión a Base de Datos
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saavedra       # O 'gis' según el dump que utilices
DB_USERNAME=root
DB_PASSWORD=               # Tu contraseña local de MySQL
```
> ⚠️ **Importante:** Importa el respaldo SQL más reciente en tu MySQL local antes de ejecutar la introspección del agente o arrancar el sistema.

### B. Entorno y Debug
```dotenv
APP_NAME="Grupo Industrial Saavedra"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### C. Almacenamiento y Archivos de O.T.
```dotenv
FILESYSTEM_DISK=local
```
Los archivos de dibujos de fundición, manuales técnicos y ayudas visuales se almacenan en `storage/app/public/` y se sirven a través del symlink `public/storage/`.

---

## 🏃 5. CÓMO ARRANCAR EL PROYECTO (DÍA A DÍA)

Para trabajar localmente necesitas mantener 2 terminales abiertas:

- **Terminal 1 (Servidor Backend Laravel):**
  ```powershell
  php artisan serve
  ```
  *La aplicación estará disponible en `http://127.0.0.1:8000`.*

- **Terminal 2 (Compilador en Vivo Vite + Tailwind CSS):**
  ```powershell
  npm run dev
  ```

---

## 🤖 6. MANUAL PARA AGENTES DE IA Y NUEVOS PROGRAMADORES

Cualquier asistente de IA (Antigravity, Gemini, Cursor, Copilot, Claude) que trabaje en este proyecto **DEBE regirse por los estándares ya consolidados**:

### A. Fuente Única de la Verdad: [`AGENTS.md`](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/AGENTS.md)
Toda regla de negocio, estructura de carpetas, matriz de perfiles y directiva técnica se encuentra en [`AGENTS.md`](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/AGENTS.md). **No inventes convenciones ni supongas flujos.**

### B. Diccionario Visual GIS (Sección 5.E de `AGENTS.md`)
Prohibido inventar CSS custom o clases arbitrarias de Tailwind cuando ya existen clases corporativas en `resources/css/global.css`:
- **Botón verde (Guardar/Crear):** `<button class="btns">`
- **Botón azul (Ver/Detalle/Info):** `<button class="btns blue">`
- **Botón rojo (Eliminar/Cancelar):** `<button class="btns danger">`
- **Contenedores de cristal:** `<div class="glass-panel">`
- **Tablas:** `<table class="gis-table">` con cabecera `<div class="table-card-header">`
- **Filtros:** `<div class="filters-bar">` con `.filter-group`

### C. Glosario de Planta Saavedra (Sección 8 de `AGENTS.md`)
- **O.T. (Orden de Trabajo):** Expediente central de producción (`Orden_trabajo`).
- **Clases:** Modelos maquinados (`MOLDE`, `FONDO`, `BOMBILLO`, `CORONA`, `OBTURADOR`, `PLATO`, `EMBUDO`).
- **PTA:** Procedimiento de soldadura especializada para recubrimiento endurecido.
- **SCAR:** Reporte de no-conformidad o rechazo de calidad.
- **`_R1`:** Sufijo para piezas y tablas de reproceso.

### D. CLI Rápido del Agente (`tools\agent.bat`)
Para que las respuestas de la IA sean instantáneas, usa siempre el CLI unificado:
```powershell
# Buscar una función, método o clase al instante
.\tools\agent.bat tags -s "nombreMetodo"

# Buscar texto rápido con Ripgrep en Rust
.\tools\agent.bat rg "texto_a_buscar"

# Escanear errores recientes y alimentar el buffer de aprendizajes
.\tools\agent.bat learn

# Actualizar el esquema de BD en Prisma tras un cambio en MySQL
.\tools\agent.bat schema

# Re-indexar todo el proyecto tras un 'git pull'
.\tools\agent.bat install
```

---

## 🔒 7. REGLAS DE ORO DE SEGURIDAD Y DESARROLLO

1. **Transacciones en Base de Datos:** Toda operación que modifique más de un registro o tabla DEBE estar dentro de `DB::beginTransaction()` con `try/catch`, `DB::commit()` y `DB::rollBack()`.
2. **Seguridad CSRF:** Todo formulario Blade tradicional debe incluir `@csrf` y toda petición asíncrona `fetch()` debe adjuntar el header `'X-CSRF-TOKEN'`.
3. **Verificación Post-Edición Obligatoria:**
   - Para PHP: `php -l ruta/al/archivo.php`
   - Para JS: `node --check ruta/al/archivo.js`
4. **Respuestas Quirúrgicas:** Cambios mínimos y directos al grano, preservando comentarios funcionales y respetando la filosofía YAGNI (cero wrappers innecesarios).

