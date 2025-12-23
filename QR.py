import qrcode

operador = input("Operador: ")
nombre = input("Nombre: ")
tipo = input("Tipo: ")
kilos = input("Kilos: ")

# Texto plano que se guardará en el QR
texto_qr = (
    f"Operador: {operador}\n"
    f"Nombre: {nombre}\n"
    f"Tipo: {tipo}\n"
    f"Kilos: {kilos}"
)

qr = qrcode.QRCode(
    version=1,
    box_size=25,
    border=2
)

qr.add_data(texto_qr)
qr.make(fit=True)

img = qr.make_image(fill_color="black", back_color="white")
img.save("qr_generado.png")

print("\nQR generado correctamente como 'qr_generado.png'")
print("Contenido del QR:")
print(texto_qr)

