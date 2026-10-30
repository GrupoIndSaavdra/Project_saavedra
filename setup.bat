@echo off
setlocal
cd /d "%~dp0"
echo ==========================================================
echo    GRUPO INDUSTRIAL SAAVEDRA - INSTALADOR AUTOMATICO     
echo ==========================================================
echo Ejecutando instalador en PowerShell...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0setup.ps1" %*
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] La instalacion fallo con codigo de error %ERRORLEVEL%.
    pause
    exit /b %ERRORLEVEL%
)
echo.
echo Presione cualquier tecla para salir...
pause >nul
