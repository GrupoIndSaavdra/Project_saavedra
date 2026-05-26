# Guía de Lógica y Modelos (Logic Skill) - Project_saavedra

Esta guía establece cómo manejar la capa de lógica de negocio, los modelos de base de datos y la seguridad en `Project_saavedra`.

## Ubicación y Modelos
- **Ubicación:** `app/Models/`
- **Nomenclatura de Modelos:** Los modelos siguen nombres en PascalCase pero a veces mezclan underscores para coincidir o clarificar conceptos de base de datos legacy (ej. `Orden_trabajo`, `Fecha_proceso`, `Moldura`, `User`).

## Uso de Eloquent ORM
- Se prioriza fuertemente el uso de Eloquent `Model::query()->where(...)->first();` y colecciones.
- **Evitar consultas N+1:** Ver la guía de Controladores sobre cómo usar el método `with()` para Eager Loading.

## Reglas de Negocio en Controladores vs Modelos
- En este proyecto, mucha de la lógica de negocio se encuentra en los controladores. Al crear nuevas funcionalidades, mantén la consistencia, pero siempre trata de aislar lógicas de validación o procesos pesados de datos (como la recolección de metadatos o cálculos) en funciones separadas auxiliares o en los mismos Modelos.

## Autenticación y Autorización basada en Perfiles
- El proyecto determina permisos y renderizado dinámico basado en el campo `perfil` del modelo `User`.
- Las comprobaciones lógicas típicas se hacen evaluando `auth()->user()->perfil`.
- **Perfiles Comunes Documentados:**
  - `1`: Admin / Sistemas
  - `4`: Perfil que revisa reportes / Piezas
  - `5`: Un tipo de usuario especial (afecta la lógica de mostrado de clases).
  - `6`: Gerencia
  - `8`: Calidad / Ingeniería
- Al programar lógica condicional, utiliza estructuras claras o arrays para agrupar roles (ej. `in_array(auth()->user()->perfil, ['1', '6', '8'])`).

## Sesiones Temporales
- El sistema utiliza fuertemente el helper `session()` para almacenar variables de estado y accesos temporales (como `pta_temp_auth`, `pta_temp_ot_id`). Asegúrate de considerar el ciclo de vida de estas sesiones al manipular accesos de usuario o lógicas cruzadas entre departamentos.
