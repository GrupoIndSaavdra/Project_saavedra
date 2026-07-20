import re

files = [
    'app/Http/Controllers/DibujosPdfController.php',
    'app/Http/Controllers/DibujosFundicionPdfController.php'
]

def patch_getFiles(content):
    pattern = re.compile(r'\$ot\s*=\s*\$this->sanitizePath\(\$request->query\(\'ot\',\s*\'\'\)\);\s*\$clase\s*=\s*\$this->sanitizePath\(\$request->query\(\'clase\',\s*\'\'\)\);.*?(?:\/\/\s*Resolver nombre de OT si es numérico|if\s*\(is_numeric\(\$ot\)\)\s*\{).*?else\s*\{.*?\n\s*\}', re.DOTALL)
    
    replacement = '''$rawOt = $request->query('ot', '');
            $clase = $this->sanitizePath($request->query('clase', ''));

            if (empty($rawOt)) {
                return response()->json(['error' => 'Parámetro OT es requerido.'], 422);
            }

            if ($clase === 'null' || $clase === '--') $clase = '';

            $otModel = \\\\App\\\\Models\\\\Orden_trabajo::query()->with('moldura')->find($rawOt);
            if ($otModel) {
                $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
                $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
            } else {
                $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
            }'''
            
    pattern_serve = re.compile(r'\$ot\s*=\s*\$this->sanitizePath\(\$request->query\(\'ot\',\s*\'\'\)\);\s*\$clase\s*=\s*\$this->sanitizePath\(\$request->query\(\'clase\',\s*\'\'\)\);\s*\$archivo\s*=\s*\$this->sanitizeFileName\(\$request->query\(\'archivo\',\s*\'\'\)\);.*?(?:if\s*\(empty\(\$ot\).*?\{.*?\}).*?if\s*\(is_numeric\(\$ot\)\)\s*\{.*?else\s*\{.*?\n\s*\}', re.DOTALL)
    
    replacement_serve = '''$rawOt = $request->query('ot', '');
        $clase = $this->sanitizePath($request->query('clase', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($rawOt) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $otModel = \\\\App\\\\Models\\\\Orden_trabajo::query()->with('moldura')->find($rawOt);
        if ($otModel) {
            $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
        } else {
            $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
        }'''
        
    c1 = pattern.sub(replacement, content)
    c2 = pattern_serve.sub(replacement_serve, c1)
    return c2

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    new_content = patch_getFiles(content)
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print('Patched', filepath)
