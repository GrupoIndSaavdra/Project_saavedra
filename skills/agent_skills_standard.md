# Skill Standard — Agent Skills & Adquisición Dinámica de Conocimiento

> **Directorio de Referencia:** `skills/agent_skills_standard.md`
> *Compatibilidad universal con el CLI oficial npx skills y el estándar abierto SKILL.md.*

---

## PROPÓSITO

Esta skill define el protocolo de **adquisición, creación y gestión dinámica de habilidades (Agent Skills)** en `Project_saavedra`. 
Permite a cualquier agente de IA consultar, instalar externamente (`npx skills add`), crear localmente (`npx skills init` / `SKILL.md`) o actualizar habilidades según las necesidades evolutivas del proyecto de forma totalmente automática.

---

## PROTOCOLO DE ADQUISICIÓN AUTOMÁTICA DE SKILLS

Cuando recibas una tarea que requiera integraciones, tecnologías o patrones no cubiertos en las skills locales existentes (ej. Redis, WebSockets, Docker, optimizaciones de DomPDF, librerías de Excel, APIs externas, etc.), debes ejecutar este protocolo de 3 pasos:

### 1. Búsqueda y Auto-Descubrimiento
Ejecuta en el terminal:
```powershell
npx -y skills find [término_o_tecnología]
```
*Ejemplo:* `npx -y skills find redis` o `npx -y skills find laravel-excel`

### 2. Instalación o Creación Local
- **Si existe un skill comunitario relevante:**
  ```powershell
  npx -y skills add <autor>/<repositorio>
  ```
- **Si es un patrón propio del proyecto:**
  ```powershell
  npx -y skills init <nombre_de_skill>
  ```
  O crea directamente el archivo en `skills/<nombre_de_skill>.md`.

### 3. Registro Automático en `general_skill.md`
Una vez instalada o creada la habilidad, agrégala inmediatamente a la tabla de índice en `skills/general_skill.md` para que quede disponible permanentemente en las siguientes consultas.

---

## COMANDOS RÁPIDOS DEL CLI `npx skills`

| Acción | Comando | Descripción |
|---|---|---|
| **Buscar** | `npx -y skills find <query>` | Busca habilidades públicas en el ecosistema comunitario |
| **Instalar** | `npx -y skills add <repo>` | Añade una habilidad externa directamente al proyecto |
| **Listar** | `npx -y skills list` | Muestra todas las habilidades instaladas en el espacio |
| **Inicializar** | `npx -y skills init <nombre>` | Genera la plantilla base con estándar `SKILL.md` |

---

## ESTRUCTURA ESTÁNDAR PARA ARCHIVOS EN `skills/`

Toda habilidad creada o mantenida en el proyecto debe respetar el formato `SKILL.md`:

```markdown
---
name: nombre-de-la-skill
description: Resumen claro de cuándo y cómo el agente debe activar esta habilidad.
version: 1.0.0
---

# Nombre de la Habilidad

## Cuándo usar esta habilidad
- Disparadores o contextos donde aplica esta habilidad.

## Instrucciones y Patrones Estándar
1. Regla o paso 1...
2. Regla o paso 2...

## Ejemplos de Código
```php
// Ejemplo representativo del patrón
```
```

---

## MANTENIMIENTO Y DEPURACIÓN DE SKILLS

- **Depuración (Pruning):** Si una skill resulta innecesaria, vacía o su contenido ya fue absorbido por otra skill principal, elimina el archivo `.md` sobrante de `skills/` y actualiza el índice en `general_skill.md`.
- **Consolidación de Aprendizajes:** El archivo `aprendizajes_temp.md` generado por `php artisan app:analizar-aprendizajes` debe asimilarse en las skills correspondientes y posteriormente eliminarse para mantener el directorio limpio.
