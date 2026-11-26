# ==============================================================================
# Configurador Automatico de Windows Task Scheduler - Project Saavedra
# ==============================================================================

param(
    [string]$PhpPath = "",
    [switch]$NoGui
)

$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "====================================================================" -ForegroundColor Cyan
Write-Host "   CONFIGURANDO TAREA AUTOMATICA EN WINDOWS TASK SCHEDULER" -ForegroundColor Yellow
Write-Host "====================================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Resolver ruta del proyecto
$projectDir = (Resolve-Path "$PSScriptRoot\..").Path

# 2. Detectar PHP
$php = $PhpPath
if (-not $php -or -not (Test-Path $php)) {
    $phpCmd = Get-Command php -ErrorAction SilentlyContinue
    if ($phpCmd) {
        $php = $phpCmd.Source
    }
}

if (-not $php -or -not (Test-Path $php)) {
    $candidates = @(
        "C:\xampp\php\php.exe",
        "C:\php\php.exe"
    )
    if (Test-Path "C:\laragon\bin\php") {
        $laragonPhp = Get-ChildItem "C:\laragon\bin\php" -Filter "php.exe" -Recurse -ErrorAction SilentlyContinue | 
                      Sort-Object FullName -Descending | Select-Object -First 1
        if ($laragonPhp) {
            $candidates = @($laragonPhp.FullName) + $candidates
        }
    }

    foreach ($cand in $candidates) {
        if (Test-Path $cand) {
            $php = $cand
            break
        }
    }
}

if (-not $php -or -not (Test-Path $php)) {
    Write-Host "[ERROR] No se pudo encontrar el ejecutable de PHP." -ForegroundColor Red
    Write-Host "Asegurate de tener PHP instalado en XAMPP, Laragon o en el PATH." -ForegroundColor Yellow
    exit 1
}

Write-Host "  [+] PHP Detectado: $php" -ForegroundColor Green
Write-Host "  [+] Directorio:    $projectDir" -ForegroundColor Green
Write-Host "  [+] Tarea:         Saavedra_Master_Scheduler" -ForegroundColor Green
Write-Host ""

# 3. Construir definicion XML nativa
$xml = @"
<?xml version="1.0" encoding="UTF-16"?>
<Task version="1.2" xmlns="http://schemas.microsoft.com/windows/2004/02/mit/task">
  <RegistrationInfo>
    <Description>Ejecutor maestro de Laravel Task Scheduler (Project Saavedra - Planta Tecamac)</Description>
    <Author>Grupo Industrial Saavedra</Author>
  </RegistrationInfo>
  <Triggers>
    <TimeTrigger>
      <Repetition>
        <Interval>PT5M</Interval>
        <StopAtDurationEnd>false</StopAtDurationEnd>
      </Repetition>
      <StartBoundary>2020-01-01T00:00:00</StartBoundary>
      <Enabled>true</Enabled>
    </TimeTrigger>
  </Triggers>
  <Settings>
    <MultipleInstancesPolicy>IgnoreNew</MultipleInstancesPolicy>
    <DisallowStartIfOnBatteries>false</DisallowStartIfOnBatteries>
    <StopIfGoingOnBatteries>false</StopIfGoingOnBatteries>
    <StartWhenAvailable>true</StartWhenAvailable>
    <AllowStartOnDemand>true</AllowStartOnDemand>
    <Enabled>true</Enabled>
    <ExecutionTimeLimit>PT15M</ExecutionTimeLimit>
  </Settings>
  <Actions Context="Author">
    <Exec>
      <Command>$php</Command>
      <Arguments>artisan schedule:run</Arguments>
      <WorkingDirectory>$projectDir</WorkingDirectory>
    </Exec>
  </Actions>
</Task>
"@

$tempXml = Join-Path $env:TEMP "saavedra_scheduler_config.xml"
try {
    [System.IO.File]::WriteAllText($tempXml, $xml, [System.Text.Encoding]::Unicode)

    $output = & schtasks /create /tn "Saavedra_Master_Scheduler" /xml "$tempXml" /f 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "schtasks fallo al registrar el XML: $output"
    }

    Write-Host "====================================================================" -ForegroundColor Cyan
    Write-Host "  [EXITO TOTAL] Tarea 'Saavedra_Master_Scheduler' configurada al 100%." -ForegroundColor Green
    Write-Host ""
    Write-Host "  - Programa o script:  $php" -ForegroundColor Gray
    Write-Host "  - Argumentos:         artisan schedule:run" -ForegroundColor Gray
    Write-Host "  - Iniciar en:         $projectDir" -ForegroundColor Gray
    Write-Host "  - Repeticion:         Cada 5 minutos (:00, :05, :10 ... :55, indefinido)" -ForegroundColor Gray
    Write-Host "  - Energia:            Sin restriccion de corriente alterna" -ForegroundColor Gray
    Write-Host "  - Concurrencia:       No iniciar nueva instancia si ya corre" -ForegroundColor Gray
    Write-Host "  - Auto-recuperacion:  Ejecutar lo antes posible si hubo fallo" -ForegroundColor Gray
    Write-Host "====================================================================" -ForegroundColor Cyan
    Write-Host ""

    if (-not $NoGui) {
        Write-Host "Abriendo el Programador de Tareas (taskschd.msc)..." -ForegroundColor Yellow
        Start-Process "taskschd.msc"
    }
}
catch {
    Write-Host "[ERROR] Fallo al crear la tarea programada: $_" -ForegroundColor Red
    exit 1
}
finally {
    if (Test-Path $tempXml) {
        Remove-Item $tempXml -Force -ErrorAction SilentlyContinue
    }
}

