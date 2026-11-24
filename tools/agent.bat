@echo off
setlocal
cd /d "%~dp0.."

set "CMD=%~1"
if "%CMD%"=="" goto help

shift

if /i "%CMD%"=="rg" goto run_rg
if /i "%CMD%"=="grep" goto run_rg
if /i "%CMD%"=="sg" goto run_sg
if /i "%CMD%"=="ast" goto run_sg
if /i "%CMD%"=="skeleton" goto run_skeleton
if /i "%CMD%"=="graph" goto run_graph
if /i "%CMD%"=="repomap" goto run_repomap
if /i "%CMD%"=="tags" goto run_tags
if /i "%CMD%"=="diff" goto run_diff
if /i "%CMD%"=="compress" goto run_compress
if /i "%CMD%"=="tokens" goto run_tokens
if /i "%CMD%"=="schema" goto run_schema
if /i "%CMD%"=="learn" goto run_learn
if /i "%CMD%"=="aprendizajes" goto run_learn
if /i "%CMD%"=="sync" goto run_sync
if /i "%CMD%"=="backup" goto run_backup
if /i "%CMD%"=="schedule" goto run_schedule_install
if /i "%CMD%"=="schedule-install" goto run_schedule_install
if /i "%CMD%"=="schedule-work" goto run_schedule_work
if /i "%CMD%"=="install" goto run_install
if /i "%CMD%"=="setup" goto run_install
if /i "%CMD%"=="help" goto help
if /i "%CMD%"=="--help" goto help
if /i "%CMD%"=="-h" goto help

echo [ERROR] Comando no reconocido: %CMD%
echo.
goto help

:run_rg
"C:\Users\Jaxer020406\AppData\Local\copilot\pkg\win32-x64\1.0.80\ripgrep\bin\win32-x64\rg.exe" %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_sg
npx --no-install ast-grep %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_skeleton
python tools/ast_skeleton.py %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_graph
python tools/dependency_graph.py %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_repomap
python tools/repomap.py %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_tags
python tools/generate_tags.py %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_diff
python tools/clean_diff.py %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_compress
python tools/compress_context.py %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_tokens
python tools/count_tokens.py %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_schema
php tools/introspect_schema.php %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_learn
php artisan app:analizar-aprendizajes %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_sync
php artisan saavedra:sync %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_backup
php artisan saavedra:backup-local %1 %2 %3 %4 %5 %6 %7 %8 %9
exit /b %errorlevel%

:run_schedule_install
echo ====================================================================
echo   CONFIGURANDO TAREA AUTOMATICA EN WINDOWS TASK SCHEDULER
echo ====================================================================
set "PHP_BIN=C:\php\php.exe"
if not exist "%PHP_BIN%" (
    for /f "delims=" %%I in ('where php 2^>nul') do (
        if not defined PHP_BIN_DETECTED set "PHP_BIN=%%I" & set "PHP_BIN_DETECTED=1"
    )
)

echo  PHP Binario: %PHP_BIN%
echo  Proyecto:    %CD%
echo.

schtasks /create /tn "Saavedra_Master_Scheduler" /tr "\"%PHP_BIN%\" \"%CD%\artisan\" schedule:run" /sc minute /mo 1 /ru "SYSTEM" /f
if %errorlevel% neq 0 (
    echo.
    echo  [ERROR] No se pudo crear la tarea. Abre la consola de CMD como Administrador.
) else (
    echo.
    echo  [EXITO] Tarea 'Saavedra_Master_Scheduler' creada correctamente en Windows.
    echo         Se ejecutara cada 1 minuto de forma continua e invisible.
)
exit /b %errorlevel%

:run_schedule_work
echo ====================================================================
echo   INICIANDO SCHEDULER EN TIEMPO REAL (schedule:work)
echo ====================================================================
php artisan schedule:work
exit /b %errorlevel%

:run_install
powershell -NoProfile -ExecutionPolicy Bypass -File setup.ps1 -OnlyTools
exit /b %errorlevel%

:help
echo ====================================================================
echo   PROJECT SAAVEDRA - CLI UNIFICADO DE AGENTE (tools\agent.bat)
echo ====================================================================
echo.
echo  Uso: tools\agent.bat [comando] [argumentos...]
echo.
echo  Comandos Disponibles:
echo    rg [query] [ruta]        - Ripgrep ultrarrapido en Rust
echo    sg [args...]             - AST-grep busqueda y modificaciones estructurales
echo    skeleton [archivo]       - Esqueleto sintactico Tree-sitter (-90%% tokens)
echo    graph [-q nombre]        - Grafo de dependencias NetworkX y analisis de impacto
echo    repomap [--save]         - Mapa de arquitectura del repositorio (PageRank)
echo    tags [-s simbolo]        - Indice instantaneo de simbolos Ctags
echo    diff [--staged]          - Git diff limpio y atomico (-U1)
echo    compress [archivo]       - Compresor de logs y prompts (estilo LLMLingua)
echo    tokens [archivo/dir]     - Medidor de presupuesto de tokens con tiktoken
echo    schema                   - Introspeccion de MySQL a prisma/schema.prisma
echo    learn                    - Escanea commits y logs para actualizar aprendizajes_temp.md
echo    sync [args...]           - Sincronizacion bidireccional de BD (local a nube)
echo    backup [args...]         - Respaldo comprimido .sql.gz de base de datos
echo    schedule                 - Instala la tarea automatica de Windows Task Scheduler
echo    schedule-work            - Ejecuta el demonio del scheduler en vivo en consola
echo    install                  - Instalar o actualizar dependencias y grafos de IA
echo ====================================================================
exit /b 0
