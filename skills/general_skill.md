# Guía de Habilidades Generales para Project_saavedra

Esta habilidad general (Master Skill) actúa como el orquestador principal para el desarrollo dentro del proyecto `Project_saavedra`. Este proyecto está construido utilizando **Laravel**, y requiere seguir una serie de convenciones y patrones específicos.

## Propósito
Siempre que debas modificar, analizar o agregar nuevas funcionalidades al proyecto, debes consultar las guías específicas para cada capa de la arquitectura.

## Habilidades Relacionadas
Por favor, asegúrate de utilizar y seguir las siguientes guías específicas dependiendo de lo que estés desarrollando:

1. **[Controladores (Controllers)](controllers_skill.md):** Contiene las instrucciones para crear, modificar y estructurar los controladores. Incluye mejores prácticas como Eager Loading y la estructuración de respuestas (vistas o JSON).
2. **[Vistas (Views)](views_skill.md):** Detalla cómo trabajar con plantillas Blade de Laravel, cómo extender layouts base (como `appMenu.blade.php`), y cómo pasar datos a JavaScript.
3. **[Lógica y Modelos (Logic)](logic_skill.md):** Guía sobre la estructura de los Modelos (Eloquent), las consultas a la base de datos, y el manejo de lógica de perfiles de usuario.
4. **[Estilos y Colores (Styles)](styles_skill.md):** Proporciona la paleta de colores oficial del proyecto (verdes, rojos, azules oscuros) y el uso de CSS puro, animaciones y filtros (como `backdrop-filter`).

## Flujo de Trabajo Recomendado
1. **Entender el Requerimiento:** Identifica qué capa del patrón MVC se ve afectada.
2. **Revisar la Lógica:** Consulta la guía de Lógica para estructurar las consultas y permisos.
3. **Crear/Modificar el Controlador:** Consulta la guía de Controladores para preparar y pasar los datos de forma optimizada.
4. **Desarrollar la Vista y Estilos:** Utiliza las guías de Vistas y Estilos para asegurar que la interfaz de usuario se vea coherente con el diseño general del proyecto.
