# Grupo Industrial Saavedra — Project Saavedra

Sistema integral de gestión de producción, órdenes de trabajo (OT), almacén de fundición, calidad, maquinados y control de soldadura PTA para Grupo Industrial Saavedra.

---

## 🚀 Arranque Rápido (Quickstart)

### Requisitos:
- **PHP** >= 8.1 (extensiones: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `fileinfo`, `curl`)
- **Composer** v2+
- **Node.js** >= 18+ y NPM
- **MySQL / MariaDB**
- **Python** 3.9+ (para herramientas de análisis de código y AST)

### Instalación en 1 Solo Paso (Windows):
```powershell
git clone <URL_DEL_REPOSITORIO>
cd Project_saavedra
.\setup.bat
```

### Ejecución Diaria:
- **Servidor Backend:** `php artisan serve` (http://localhost:8000)
- **Frontend / Vite:** `npm run dev`
- **CLI del Agente de IA:** `.\tools\agent.bat help`

---

## 📚 Documentación de Arquitectura y Agentes de IA

- **[AI_ONBOARDING.md](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/AI_ONBOARDING.md):** Guía completa de instalación, variables `.env`, arquitectura de base de datos y onboarding para nuevos desarrolladores y sus agentes de IA.
- **[AGENTS.md](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/AGENTS.md):** Fuente única de la verdad del sistema: mapa express de arquitectura, matriz de perfiles, catálogo de skills, diccionario visual GIS y glosario de planta.
