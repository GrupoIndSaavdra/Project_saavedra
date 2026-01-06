import qrcode
import os
from datetime import date
from pathlib import Path

# Datos del operador
operador_id = input("ID del operador: ") 
soldadura_id = input("ID Soldadura: ")
fecha_entrega = input(f"Fecha de entrega (YYYY-MM-DD) [default {date.today()}]: ") or str(date.today())
cantidad = input("Cantidad (kg): ")

# Solo valores puros, cada uno en una línea
texto_qr = f"{operador_id}\n{soldadura_id}\n{fecha_entrega}\n{cantidad}"

# Obtener la carpeta de descargas del usuario
descargas_path = Path.home() / "Downloads"

# Función para generar nombre único
def generar_nombre_unico(base_name, directorio):
    ruta_completa = directorio / base_name
    if not ruta_completa.exists():
        return ruta_completa
    
    name = ruta_completa.stem
    ext = ruta_completa.suffix
    counter = 1
    while (directorio / f"{name}_{counter}{ext}").exists():
        counter += 1
    return directorio / f"{name}_{counter}{ext}"

qr = qrcode.QRCode(version=1, box_size=10, border=2)
qr.add_data(texto_qr)
qr.make(fit=True)
img = qr.make_image(fill_color="black", back_color="white")

ruta_archivo = generar_nombre_unico("qr_generado.png", descargas_path)
img.save(ruta_archivo)

print(f"QR generado correctamente en '{ruta_archivo}'")
print(texto_qr)
