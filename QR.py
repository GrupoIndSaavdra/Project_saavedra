import qrcode
import os
from datetime import date

# Datos del operador
operador_id = input("ID del operador: ") 
soldadura_id = input("ID Soldadura: ")
fecha_entrega = input(f"Fecha de entrega (YYYY-MM-DD) [default {date.today()}]: ") or str(date.today())
cantidad = input("Cantidad (kg): ")

# Solo valores puros, cada uno en una línea
texto_qr = f"{operador_id}\n{soldadura_id}\n{fecha_entrega}\n{cantidad}"

# Función para generar nombre único
def generar_nombre_unico(base_name):
    if not os.path.exists(base_name):
        return base_name
    
    name, ext = os.path.splitext(base_name)
    counter = 1
    while os.path.exists(f"{name}_{counter}{ext}"):
        counter += 1
    return f"{name}_{counter}{ext}"

qr = qrcode.QRCode(version=1, box_size=10, border=2)
qr.add_data(texto_qr)
qr.make(fit=True)
img = qr.make_image(fill_color="black", back_color="white")

nombre_archivo = generar_nombre_unico("qr_generado.png")
img.save(nombre_archivo)

print(f"QR generado correctamente como '{nombre_archivo}'")
print(texto_qr)
