<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AnalyzeProjectLearnings extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'app:analizar-aprendizajes
                            {--commits=15 : Número de commits recientes a inspeccionar}
                            {--log-lines=500 : Número de líneas al final de laravel.log a leer}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Escanea el historial de Git y los logs de Laravel en busca de errores y soluciones recientes para retroalimentar las Skills de la IA.';

    /**
     * Ejecutar el comando de consola.
     */
    public function handle()
    {
        $this->info("=== Iniciando Análisis de Aprendizajes de Proyecto ===");

        $numCommits = (int) $this->option('commits');
        $logLinesCount = (int) $this->option('log-lines');

        // 1. ESCANEAR LOGS DE LARAVEL
        $this->comment("1. Escaneando logs de Laravel ({$logLinesCount} líneas)...");
        $errorsFound = $this->scanLaravelLog($logLinesCount);
        $this->info("   Se encontraron " . count($errorsFound) . " patrones de error recientes.");

        // 2. ESCANEAR COMMITS RECIENTES DE GIT
        $this->comment("2. Escaneando últimos {$numCommits} commits en Git...");
        $gitLearnings = $this->scanGitHistory($numCommits);
        $this->info("   Se encontraron " . count($gitLearnings) . " commits con lecciones (FIX/BUG/CHANGE).");

        // 3. GENERAR EL INFORME DRAFT SI HAY APRENDIZAJES
        if (count($errorsFound) > 0 || count($gitLearnings) > 0) {
            $this->generateLearningsFile($errorsFound, $gitLearnings);
            $this->info("✓ Archivo de aprendizajes generado exitosamente en 'skills/aprendizajes_temp.md'.");
        } else {
            $this->warn("! No se detectaron anomalías en logs ni commits especiales recientes. Nada que actualizar.");
            // Si existía el archivo previo, lo limpiamos para no confundir a la IA
            $tempPath = base_path('skills/aprendizajes_temp.md');
            if (file_exists($tempPath)) {
                @unlink($tempPath);
                $this->info("✓ Se eliminó el borrador anterior de aprendizajes ya que no hay novedades.");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Escanea el final del archivo laravel.log en busca de excepciones comunes de PHP/Laravel.
     */
    private function scanLaravelLog(int $linesToRead): array
    {
        $logPath = storage_path('logs/laravel.log');
        if (!file_exists($logPath)) {
            return [];
        }

        // Leer de forma eficiente el final de un archivo grande sin cargarlo entero en memoria
        $file = new \SplFileObject($logPath, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $linesToRead);
        $file->seek($startLine);

        $recentExceptions = [];
        $currentException = null;

        while (!$file->eof()) {
            $line = $file->current();
            $file->next();
            if (empty(trim($line))) {
                continue;
            }

            // Detectar inicio de una traza de error de Laravel [YYYY-MM-DD HH:MM:SS]
            if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $line)) {
                if ($currentException) {
                    $recentExceptions[] = $currentException;
                    $currentException = null;
                }

                // Clasificar líneas de error críticas
                if (preg_match('/(ERROR|CRITICAL|EMERGENCY|Exception|Error): (.*)/i', $line, $matches)) {
                    $currentException = [
                        'timestamp' => substr($line, 1, 19),
                        'severity' => $matches[1],
                        'message' => trim($matches[2]),
                        'trace' => []
                    ];
                }
            } elseif ($currentException && count($currentException['trace']) < 5) {
                // Agregar las primeras 5 líneas de la traza para dar contexto
                $trimmed = trim($line);
                if (!empty($trimmed) && strpos($trimmed, '#') === 0) {
                    $currentException['trace'][] = $trimmed;
                }
            }
        }

        if ($currentException) {
            $recentExceptions[] = $currentException;
        }

        // Agrupar excepciones repetidas para no duplicar en el reporte final
        $uniqueExceptions = [];
        foreach ($recentExceptions as $exc) {
            $key = md5($exc['message']);
            if (!isset($uniqueExceptions[$key])) {
                $uniqueExceptions[$key] = $exc;
                $uniqueExceptions[$key]['count'] = 1;
            } else {
                $uniqueExceptions[$key]['count']++;
            }
        }

        return array_values($uniqueExceptions);
    }

    /**
     * Escanea el historial de Git para buscar commits de corrección y extraer sus diffs resumidos.
     */
    private function scanGitHistory(int $numCommits): array
    {
        $learnings = [];
        
        // Obtener logs estructurados
        $command = "git log -n {$numCommits} --oneline";
        exec($command, $output, $resultCode);

        if ($resultCode !== 0 || empty($output)) {
            return [];
        }

        foreach ($output as $line) {
            if (empty(trim($line))) continue;

            $parts = explode(' ', $line, 2);
            if (count($parts) < 2) continue;

            $hash = $parts[0];
            $message = $parts[1];

            // Comprobar convenciones de commits de corrección
            if (preg_match('/^(FIX|BUG|CHANGE|Refactor)\b/i', $message)) {
                // Obtener archivos modificados
                $filesCommand = "git show --name-only --oneline {$hash}";
                exec($filesCommand, $filesOutput);
                
                // Limpiar la primera línea (que es el oneline)
                array_shift($filesOutput);
                $filesModified = array_filter(array_map('trim', $filesOutput));

                // Obtener diff resumido (máx 30 líneas de cambios para no sobrecargar el prompt)
                $diffCommand = "git show --unified=1 {$hash}";
                exec($diffCommand, $diffOutput);
                
                $diffLines = [];
                foreach ($diffOutput as $dLine) {
                    if (count($diffLines) > 40) {
                        $diffLines[] = "... (diff truncado por brevedad) ...";
                        break;
                    }
                    // Guardar líneas añadidas o removidas relevantes
                    if (strpos($dLine, '+++') === 0 || strpos($dLine, '---') === 0 || strpos($dLine, '@@') === 0 || strpos($dLine, '+') === 0 || strpos($dLine, '-') === 0) {
                        $diffLines[] = $dLine;
                    }
                }

                $learnings[] = [
                    'hash' => $hash,
                    'message' => $message,
                    'files' => $filesModified,
                    'diff' => implode("\n", $diffLines)
                ];
            }
        }

        return $learnings;
    }

    /**
     * Escribe el reporte markdown borrador en skills/aprendizajes_temp.md.
     */
    private function generateLearningsFile(array $errors, array $commits): void
    {
        $date = Carbon::now()->format('d/m/Y H:i:s');
        
        $md = "# Aprendizajes del Proyecto — Buffer de Sincronización\n\n";
        $md .= "> **Generado automáticamente el:** {$date}\n";
        $md .= "> ⚠️ **ATENCIÓN IA:** Este archivo contiene errores detectados en logs y soluciones aplicadas en commits recientes. Modifica las skills correspondientes con estos conocimientos y luego **ELIMINA** este archivo temporal.\n\n";

        if (count($errors) > 0) {
            $md .= "## 🔴 Errores y Excepciones Recientes en Logs\n\n";
            foreach ($errors as $err) {
                $md .= "### [{$err['timestamp']}] {$err['severity']} (Visto {$err['count']} veces)\n";
                $md .= "**Mensaje:** `{$err['message']}`\n\n";
                if (!empty($err['trace'])) {
                    $md .= "**Traza de origen:**\n```text\n" . implode("\n", $err['trace']) . "\n```\n\n";
                }
                $md .= "---\n\n";
            }
        }

        if (count($commits) > 0) {
            $md .= "## 🟢 Soluciones y Cambios de Código Recientes (Commits)\n\n";
            foreach ($commits as $c) {
                $md .= "### Commit: `{$c['hash']}` — {$c['message']}\n";
                $md .= "**Archivos Modificados:**\n";
                foreach ($c['files'] as $f) {
                    $md .= "- `{$f}`\n";
                }
                $md .= "\n**Diff de Cambios Clave:**\n";
                $md .= "```diff\n{$c['diff']}\n```\n\n";
                $md .= "---\n\n";
            }
        }

        $md .= "## 📋 Instrucciones de Procesamiento para la IA\n";
        $md .= "1. Analiza el error en log e identifica qué skill debe prevenirlo en el futuro (ej. si falta un select o where, agrégalo a `logic_skill.md`).\n";
        $md .= "2. Analiza el cambio en Git y extrae la buena práctica (ej. si corregiste una importación de Vite o una validación condicional de JS, regístralo en la skill de JS/Blade).\n";
        $md .= "3. Edita los archivos `.md` de habilidades necesarios.\n";
        $md .= "4. **ELIMINA este archivo `skills/aprendizajes_temp.md`** al terminar para indicar que la memoria del agente ha sido sincronizada con el estado del proyecto.\n";

        $tempPath = base_path('skills/aprendizajes_temp.md');
        file_put_contents($tempPath, $md);
    }
}
