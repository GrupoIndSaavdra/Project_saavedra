import re

files = [
    'app/Http/Controllers/DibujosPdfController.php',
    'app/Http/Controllers/DibujosFundicionPdfController.php'
]

def apply_db_lookup(content):
    # This regex looks for:
    # $ot = $this->normalizeOTName($this->sanitizePath($request->input('ot')));
    # and replaces it with the robust lookup.
    
    pattern1 = re.compile(r'\$ot\s*=\s*\$this->normalizeOTName\(\$this->sanitizePath\(\$request->input\(\'ot\'\)\)\);')
    
    replacement1 = r'''$rawOt = $request->input('ot');
        $otModel = \App\Models\Orden_trabajo::query()->with('moldura')->find($rawOt);
        if ($otModel) {
            $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
        } else {
            $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
        }'''
        
    c1 = pattern1.sub(replacement1.replace('\\', '\\\\'), content)
    
    # Also for serveFile where it uses query('ot')
    pattern2 = re.compile(r'\$ot\s*=\s*\$this->normalizeOTName\(\$this->sanitizePath\(\$request->query\(\'ot\',\s*\'\'\)\)\);')
    replacement2 = r'''$rawOt = $request->query('ot', '');
        $otModel = \App\Models\Orden_trabajo::query()->with('moldura')->find($rawOt);
        if ($otModel) {
            $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
        } else {
            $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
        }'''
        
    c2 = pattern2.sub(replacement2.replace('\\', '\\\\'), c1)
    
    # Also for getFiles in DibujosFundicionPdfController
    pattern_getFiles_Fundicion = re.compile(r'\$ot\s*=\s*\$this->sanitizePath\(\$request->query\(\'ot\',\s*\'\'\)\);\s*\$clase\s*=\s*\$this->sanitizePath\(\$request->query\(\'clase\',\s*\'\'\)\);.*?(?:\/\/\s*Resolver nombre de carpeta si se pasó un ID|if\s*\(is_numeric\(\$ot\)\)\s*\{).*?else\s*\{.*?\n\s*\}', re.DOTALL)
    
    replacement_getFiles = r'''$rawOt = $request->query('ot', '');
            $clase = $this->sanitizePath($request->query('clase', ''));

            if (empty($rawOt)) {
                return response()->json(['error' => 'Parámetro OT es requerido.'], 422);
            }

            if ($clase === 'null' || $clase === '--') $clase = '';

            $otModel = \App\Models\Orden_trabajo::query()->with('moldura')->find($rawOt);
            if ($otModel) {
                $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
                $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
            } else {
                $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
            }'''
            
    c3 = pattern_getFiles_Fundicion.sub(replacement_getFiles.replace('\\', '\\\\'), c2)
    
    # serveFile in DibujosFundicionPdfController
    pattern_serve_Fundicion = re.compile(r'\$ot\s*=\s*\$this->sanitizePath\(\$request->query\(\'ot\',\s*\'\'\)\);\s*\$clase\s*=\s*\$this->sanitizePath\(\$request->query\(\'clase\',\s*\'\'\)\);\s*\$archivo\s*=\s*\$this->sanitizeFileName\(\$request->query\(\'archivo\',\s*\'\'\)\);.*?(?:if\s*\(empty\(\$ot\).*?\{.*?\}).*?if\s*\(is_numeric\(\$ot\)\)\s*\{.*?else\s*\{.*?\n\s*\}', re.DOTALL)
    
    replacement_serve_Fundicion = r'''$rawOt = $request->query('ot', '');
        $clase = $this->sanitizePath($request->query('clase', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($rawOt) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $otModel = \App\Models\Orden_trabajo::query()->with('moldura')->find($rawOt);
        if ($otModel) {
            $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
        } else {
            $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
        }'''
        
    c4 = pattern_serve_Fundicion.sub(replacement_serve_Fundicion.replace('\\', '\\\\'), c3)
    
    # SendEmailAlert in DibujosFundicionPdfController
    pattern_alert = re.compile(r'\$otFolderName\s*=\s*\$this->normalizeOTName\(\$this->sanitizePath\(\$request->input\(\'ot\'\)\)\);')
    replacement_alert = r'''$rawOt = $request->input('ot');
        $otModel = \App\Models\Orden_trabajo::query()->with('moldura')->find($rawOt);
        if ($otModel) {
            $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $otFolderName = $this->normalizeOTName($this->sanitizePath($otLabel));
        } else {
            $otFolderName = $this->normalizeOTName($this->sanitizePath($rawOt));
        }'''
    
    c5 = pattern_alert.sub(replacement_alert.replace('\\', '\\\\'), c4)

    return c5

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    new_content = apply_db_lookup(content)
    # properly escape backslashes for python regex replacement backreferences.
    # Actually I used `r'''...'''` which avoids regex backref issues, but let's just make sure there are no issues.
    new_content = new_content.replace(r'\App', r'\\App')
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print('Patched', filepath)
