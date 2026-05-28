# ❌ Habilidad de No Pruebas (No Testing Rule)

Esta regla define el alcance del agente IA en relación con las pruebas de interfaz de usuario y del navegador.

---

## 🚫 Límite estricto de pruebas

* **El agente NO debe realizar pruebas en el navegador:** Queda totalmente prohibido iniciar subagentes de navegador (`browser_subagent`), interactuar con el DOM del navegador para validar flujos, o realizar capturas de pantalla para validar si el HTML/JS funciona de manera interactiva.
* **El desarrollador (USER) se encarga de las pruebas:** Toda la validación en el navegador web, clics, pruebas de usuario y aserciones de interfaz las realiza manualmente el usuario.
* **Foco del agente:** El agente debe concentrarse al 100% en escribir código limpio, controladores correctos, vistas blade bien maquetadas, estilos pulidos y lógica de base de datos robusta, validando la sintaxis mediante consola (`php -l` o `npm run build`) pero sin interactuar con la aplicación en vivo.
