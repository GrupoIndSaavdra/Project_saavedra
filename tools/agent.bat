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
echo    install                  - Instalar o actualizar dependencias y grafos de IA
echo ====================================================================
exit /b 0
