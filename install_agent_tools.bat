@echo off
setlocal EnableDelayedExpansion

echo ====================================================================
echo   PROJECT SAAVEDRA - INSTALADOR DE HERRAMIENTAS DE AGENTE Y AST
echo ====================================================================
echo.

:: 1. Verificar PHP
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP no esta disponible en el PATH. Instala o configura PHP 8.2+
    pause
    exit /b 1
)
echo [OK] PHP detectado correctamente.

:: 2. Verificar Python
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Python no esta disponible en el PATH. Instala Python 3.10+
    pause
    exit /b 1
)
echo [OK] Python detectado correctamente.

:: 3. Verificar Node y NPM
node -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Node.js no esta disponible en el PATH. Instala Node 18+
    pause
    exit /b 1
)
echo [OK] Node.js detectado correctamente.

echo.
echo --------------------------------------------------------------------
echo  [Paso 1/4] Instalando dependencias Python (tools/requirements.txt)...
echo --------------------------------------------------------------------
python -m pip install --quiet --upgrade pip
python -m pip install --quiet -r tools/requirements.txt
if %errorlevel% neq 0 (
    echo [AVISO] Hubo un detalle al instalar con pip, intentando modo usuario...
    python -m pip install --user -r tools/requirements.txt
)
echo [OK] Librerias Python instaladas con exito.

echo.
echo --------------------------------------------------------------------
echo  [Paso 2/4] Instalando AST-grep en devDependencies de NPM...
echo --------------------------------------------------------------------
call npm install --save-dev @ast-grep/cli
echo [OK] AST-grep (@ast-grep/cli) instalado con exito.

echo.
echo --------------------------------------------------------------------
echo  [Paso 3/4] Introspeccionando esquema MySQL a Prisma...
echo --------------------------------------------------------------------
php tools/introspect_schema.php

echo.
echo --------------------------------------------------------------------
echo  [Paso 4/4] Construyendo Grafo de Dependencias, Repomap y Tags...
echo --------------------------------------------------------------------
python tools/dependency_graph.py --rebuild
python tools/repomap.py --save
python tools/generate_tags.py --build

echo.
echo ====================================================================
echo   INSTALACION COMPLETADA EXITOSAMENTE!
echo ====================================================================
echo.
echo  Archivos clave generados:
echo   - prisma/schema.prisma     (Esquema unificado de tablas de MySQL)
echo   - repomap.txt              (Mapa del repo por PageRank en ~1,400 tokens)
echo   - tags                     (Indice de simbolos de clases y metodos)
echo   - storage/code_dependency_graph.json (Grafo DAG de NetworkX)
echo.
echo  Comandos rapidos disponibles:
echo   - tools\rg.bat "texto"              (Ripgrep ultrarrapido)
echo   - tools\sg.bat run -p "patron"      (AST-grep estructural)
echo   - python tools/ast_skeleton.py <f>  (Esqueleto AST sin method bodies)
echo   - python tools/dependency_graph.py -q <nombre> (Consulta de impacto)
echo   - python tools/generate_tags.py -s <simbolo>   (Buscar simbolo)
echo   - python tools/compress_context.py <archivo>   (Comprimir logs)
echo   - python tools/count_tokens.py <f>             (Contar tokens)
echo.
echo Presiona cualquier tecla para salir...
pause >nul

