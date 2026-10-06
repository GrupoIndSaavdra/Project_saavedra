
f='resources/views/calidad/partials/sidebar_legend.blade.php'
with open(f, encoding='utf-8') as file: text = file.read()
replacements = {
    'Gu\xc3\xada': 'Guía',
    'GuÃ\xada': 'Guía',
    'GuÃ­a': 'Guía',
    'notificaciÃ³n': 'notificación',
    'revisiÃ³n': 'revisión',
    'Ã¡rea': 'área',
    'AlmacÃ©n': 'Almacén',
    'estÃ¡': 'está',
    'RevisiÃ³n': 'Revisión',
    'OperaciÃ³n': 'Operación',
    'LogÃ\xadstica': 'Logística',
    'LogÃ­stica': 'Logística',
    'demÃ¡s': 'demás',
    'LiberaciÃ³n': 'Liberación'
}
for k, v in replacements.items(): text = text.replace(k, v)
with open(f, 'w', encoding='utf-8') as file: file.write(text)
print('Done!')

