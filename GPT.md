# Directivas para GPT / Codex — Project Saavedra

> [AGENTS.md](AGENTS.md) es la fuente única de directivas del proyecto. Ahí se definen la arquitectura, la matriz de perfiles, las reglas técnicas y el Diccionario Visual GIS.

1. Lee [AGENTS.md](AGENTS.md) antes de trabajar. Usa el Mapa Express para localizar el módulo y consulta la guía de [.agents](.agents/skills/project-saavedra/SKILL.md) y las skills de `skills/` cuando el flujo sea complejo o haya dudas sobre una convención. Para instalar o arrancar el entorno, consulta [AI_ONBOARDING.md](AI_ONBOARDING.md).
2. Si el mapa no basta, usa `.\tools\agent.bat tags -s <simbolo>` para localizar símbolos o `.\tools\agent.bat rg "<texto>"` para buscar texto.
3. Haz cambios puntuales y reutiliza los patrones del módulo. Para la interfaz, aplica el Diccionario Visual GIS de la sección 5.E, incluidas las clases `.btns`, `.glass-panel`, `.gis-table` y `.filters-bar`, y los helpers `gisToast`, `gisConfirm` y `gisAlert` donde correspondan.
4. Guarda los archivos en UTF-8 sin BOM. Valida los archivos PHP con `php -l` y los JavaScript con `node --check` cuando los modifiques.
5. Sigue la sección 7 de `AGENTS.md`. Deja los cambios locales para revisión. Haz commits o pushes solo por instrucción del desarrollador y requiere su autorización para integrar cambios en `Server/v1.0.0`.
