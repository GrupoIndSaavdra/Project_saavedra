---
name: bat_cli_skill
description: Guía maestra y protocolo obligatorio para los scripts .bat del proyecto (setup.bat y tools\agent.bat), comandos de actualización rápida diaria (install vs help), y regla de oro de cero proliferación de archivos batch en Project Saavedra.
version: 1.0.0
---

# Guía Maestra de Comandos y Scripts Batch (BAT & Agent CLI Skill)

> **Directorio de Referencia:** Raíz del proyecto (`setup.bat`) y [`tools/agent.bat`](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/tools/agent.bat)
> *Protocolo estricto para la ejecución, actualización y mantenimiento de herramientas CLI sin crear scripts dispersos.*

---

## 🛑 REGLA DE ORO DE ARQUITECTURA (CERO PROLIFERACIÓN DE .BAT)

En `Project_saavedra` **SOLO EXISTEN EXACTAMENTE 2 ARCHIVOS `.BAT`** en todo el repositorio:

1. **[`setup.bat`](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/setup.bat)** (En la raíz del proyecto):  
   El **Instalador Único** del sistema completo. Monta Composer, Livewire 3, `.env`, NPM, Vite y las herramientas de IA en una sola corrida.
2. **[`tools/agent.bat`](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/tools/agent.bat)** (En la carpeta `tools/`):  
   El **Runner Único** del Agente de IA. Es el CLI centralizado que ejecuta absolutamente todas las herramientas internas (Ripgrep, AST-grep, Tree-sitter, NetworkX, Ctags, Tiktoken, etc.).

> ⚠️ **DIRECTIVA OBLIGATORIA:** Queda **estrictamente prohibido crear nuevos archivos `.bat` o `.sh` sueltos** (como `rg.bat`, `sg.bat`, `test.bat`, etc.). Si se diseña una nueva utilidad o script, DEBE agregarse como un subcomando dentro de `tools/agent.bat` (y `tools/agent.sh` para Linux).

---

## ⚡ MATRIZ DE DECISIÓN: ¿QUÉ COMANDO CORRER Y CUÁNDO?

| Situación / Requerimiento | Comando Exacto | ¿Qué hace? | Tiempo aprox. |
|---|---|---|---|
| **Día a día: Actualizar todo rápido** | `.\tools\agent.bat install` | Reconstruye esquema de BD, grafo NetworkX, Repomap y símbolos Ctags en un solo paso. | ~5 segundos |
| **Consultar la ayuda / ver comandos** | `.\tools\agent.bat help` *(o solo `.\tools\agent.bat`)* | Muestra la lista interactiva de todos los comandos disponibles. | Instantáneo |
| **Se crearon o modificaron tablas en MySQL** | `.\tools\agent.bat schema` | Introspecciona la base de datos activa (`.env`) y actualiza `prisma/schema.prisma`. | ~2 segundos |
| **Se agregaron nuevas funciones, clases o métodos** | `.\tools\agent.bat tags` | Reconstruye el archivo de índice de símbolos Ctags (`tags`). | ~1 segundo |
| **Se crearon nuevas rutas web o controladores** | `.\tools\agent.bat repomap` | Recalcula el PageRank del sistema y actualiza `repomap.txt`. | ~2 segundos |
| **Instalación desde cero (nueva PC o servidor)** | `.\setup.bat` | Corre la preparación completa (Composer + Livewire + NPM + Vite + Herramientas IA). | ~1-2 minutos |

---

## 🕹️ CATÁLOGO COMPLETO DE SUBCOMANDOS DE `tools\agent.bat`

El ejecutable `tools\agent.bat` se invoca siempre con la sintaxis:
```powershell
.\tools\agent.bat <subcomando> [argumentos...]
```

### 1. `install` (El comando de actualización)
Actualiza e indexa todo el entorno de inteligencia sin tocar Composer ni Vite:
```powershell
.\tools\agent.bat install
```

### 2. `schema` (Introspección MySQL a Prisma)
Lee la base de datos configurada en `.env` (sea `saavedra` en local o `gis` en el servidor) y genera [`prisma/schema.prisma`](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/prisma/schema.prisma):
```powershell
.\tools\agent.bat schema
```

### 3. `graph` (Grafo de Dependencias NetworkX)
Consulta el impacto de modificar cualquier modelo, controlador o vista:
```powershell
# Consultar dependencias de un nodo:
.\tools\agent.bat graph -q Pieza
.\tools\agent.bat graph -q UserController

# Reconstruir el grafo desde cero:
.\tools\agent.bat graph --rebuild
```

### 4. `tags` (Indexador Universal Ctags)
Busca dónde está declarada cualquier función o clase al instante:
```powershell
# Buscar símbolo:
.\tools\agent.bat tags -s altaUsuario

# Reconstruir índice tags:
.\tools\agent.bat tags --build
```

### 5. `skeleton` (Esqueleto Sintáctico AST Tree-sitter)
Extrae las firmas y números de línea de un controlador grande suprimiendo los cuerpos (**ahorro de hasta 92% de tokens**):
```powershell
.\tools\agent.bat skeleton app/Http/Controllers/UserController.php
```

### 6. `rg` (Ripgrep Ultrarrápido en Rust)
Búsqueda textual nativa a máxima velocidad sin saturar el contexto:
```powershell
.\tools\agent.bat rg "gisToast" resources/js/
.\tools\agent.bat rg "altaUsuario" app/
```

### 7. `sg` (AST-grep Búsqueda Estructural)
Búsqueda y modificación de patrones sintácticos AST:
```powershell
.\tools\agent.bat sg run -p 'DB::beginTransaction()' app/Http/Controllers/
```

### 8. `diff` (Git Diff Atómico y Limpio)
Muestra únicamente las líneas cambiadas con contexto mínimo `-U1` sin basura de metadatos:
```powershell
.\tools\agent.bat diff
.\tools\agent.bat diff --staged
```

### 9. `compress` (Compresor de Logs estilo LLMLingua)
Comprime archivos de log de Laravel colapsando stack traces de vendor framework:
```powershell
.\tools\agent.bat compress storage/logs/laravel.log
```

### 10. `tokens` (Contador de Presupuesto Tiktoken)
Calcula el número exacto de tokens de un archivo o carpeta antes de inyectarlo:
```powershell
.\tools\agent.bat tokens app/Models/User.php
```

---

## 🔒 REGLAS TÉCNICAS PARA SCRIPTS BATCH EN WINDOWS

1. **Cero redirecciones en `echo`:** Nunca uses signos `<` o `>` dentro de mensajes de `echo` en archivos `.bat` (ejemplo prohibido: `echo <archivo>`), ya que Windows los interpreta como redirección de entrada/salida y arroja el error *"El sistema no puede encontrar el archivo especificado"*. Si necesitas mostrarlos, deben escaparse con `^<` y `^>`.
2. **Archivos dinámicos fuera de Git:** Tanto `repomap.txt` como `prisma/schema.prisma`, `tags` y `storage/code_dependency_graph.json` se generan de acuerdo a la base de datos de cada máquina (`saavedra` en local vs `gis` en producción). Deben permanecer **siempre en `.gitignore`** para evitar conflictos de merge al hacer `git pull`.
3. **Paridad con Linux:** Cada funcionalidad añadida a `tools/agent.bat` debe tener su equivalente en [`tools/agent.sh`](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/tools/agent.sh) para que los servidores Linux y contenedores funcionen con la misma interfaz.

