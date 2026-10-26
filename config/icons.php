<?php

/*
|--------------------------------------------------------------------------
| Catálogo Centralizado de Iconos y Recursos Visuales
|--------------------------------------------------------------------------
|
| Este archivo es el único punto de control para la gestión de iconos
| e imágenes del sistema. Modificar la ruta aquí actualiza todas las vistas
| Blade y scripts JavaScript automáticamente.
|
| Uso en Blade:
|   {{ icon('aprobado') }}           -> Retorna la URL del asset
|   <x-icon name="aprobado" />       -> Renderiza la etiqueta <img>
|
| Uso en JavaScript:
|   window.getIcon('aprobado')       -> Retorna la URL del asset en el cliente
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Estados y Sindicaciones
    |--------------------------------------------------------------------------
    */
    'aprobado'              => 'images/iconos/Aprobado.png',
    'rechazado'             => 'images/iconos/Rechazado.png',
    'espera'                => 'images/iconos/Espera.png',
    'recibido'              => 'images/iconos/Recibido.png',
    'revisando'             => 'images/iconos/Revisando.png',
    'editando'              => 'images/iconos/Editando.png',
    'guardado'              => 'images/iconos/Guardado.png',
    'ready'                 => 'images/iconos/ready.png',
    'alerta'                => 'images/iconos/Alerta-Icon.png',
    'aviso'                 => 'images/iconos/Aviso.png',
    'critica'               => 'images/iconos/Critica.png',
    'sospechosa'            => 'images/iconos/Sospechosa.png',
    'error'                 => 'images/iconos/error.png',
    'bloqueado'             => 'images/iconos/bloqueado.png',
    'desbloqueado'          => 'images/iconos/desbloqueado.png',
    'bloqueado_maquina'     => 'images/iconos/Bloq-Maqu-Icon.png',
    'desbloqueado_maquina'  => 'images/iconos/Desbloc-Maq-Icon.png',
    'por_liberar'           => 'images/iconos/por_liberar_icon.png',

    /*
    |--------------------------------------------------------------------------
    | Botones y Acciones de Usuario
    |--------------------------------------------------------------------------
    */
    'liberar'               => 'images/iconos/Liberar.png',
    'rechazar'              => 'images/iconos/Rechazar.png',
    'eliminar'              => 'images/iconos/delete.png',
    'cancelar'              => 'images/iconos/Cancelar-Icon.png',
    'quitar'                => 'images/iconos/quitar.png',
    'eliminar_archivos'     => 'images/iconos/Eliminar-Archivos.png',
    'eliminar_carpeta'      => 'images/iconos/Eliminar-Carpeta.png',
    'editar'                => 'images/iconos/edit.png',
    'editar_info'           => 'images/iconos/editar-informacion.png',
    'editar_user'           => 'images/usuarios/editaruser.png',
    'anadir'                => 'images/iconos/anadir.png',
    'actualizar'            => 'images/iconos/actualizar.png',
    'descarga'              => 'images/iconos/Descarga.png',
    'descarga_excel'        => 'images/iconos/Descargar_Excel-Icon.png',
    'descarga_excel_shadow' => 'images/iconos/Descargar_Excel-Icon-WShadow.png',
    'descarga_pdf'          => 'images/iconos/Descargar_PDF-Icon.png',
    'descarga_pdf_shadow'   => 'images/iconos/Descargar_PDF-Icon-WShadow.png',
    'imprimir'              => 'images/iconos/Imprimir-Icon.png',
    'imprimir_shadow'       => 'images/iconos/Imprimir-Icon-WShadow.png',
    'upload'                => 'images/iconos/upload_icon.png',
    'reemplazar'            => 'images/iconos/reemplazar.png',
    'reiniciar'             => 'images/iconos/reiniciar.png',
    'priorizar'             => 'images/iconos/priorizar.png',
    'escanear'              => 'images/iconos/Escanear-icon.png',
    'enviando'              => 'images/iconos/enviando.png',
    'redireccionar'         => 'images/iconos/redireccionar.png',
    'lupa'                  => 'images/iconos/lupa.png',
    'ojito'                 => 'images/iconos/ojito.png',
    'zoom_in'               => 'images/iconos/mas_zoom.png',
    'zoom_out'              => 'images/iconos/menos_zoom.png',
    'cerrar'                => 'images/iconos/cerrar.png',
    'loading'               => 'images/iconos/loading.gif',

    /*
    |--------------------------------------------------------------------------
    | Tipos de Archivos, Documentación y Viewers
    |--------------------------------------------------------------------------
    */
    'dwg'                   => 'images/iconos/dwg.png',
    'dwg_shadow'            => 'images/iconos/dwg-shadow.png',
    'pdf'                   => 'images/iconos/pdf.png',
    'pdf_view'              => 'images/iconos/pdf-view.png',
    'pdf_view_shadow'       => 'images/iconos/pdf-view-shadow.png',
    'pdf_transparent'       => 'images/iconos/pdf-transparent.png',
    'galeria'               => 'images/iconos/galeria.png',
    'galeria_shadow'        => 'images/iconos/galeria-shadow.png',
    'camara'                => 'images/iconos/Camara-View.png',
    'camara_shadow'         => 'images/iconos/Camara-Shadow.png',
    'documento'             => 'images/iconos/documento.png',
    'sin_archivos'          => 'images/iconos/sin_archivos.png',
    'sin_carpetas'          => 'images/iconos/Sin_Carpetas.png',
    'sin_av'                => 'images/iconos/sin_AV.png',
    'copia_datos'           => 'images/iconos/copia-de-datos.png',
    'cnc'                   => 'images/iconos/ProgramsCNC.png',
    'cnc_shadow'            => 'images/iconos/ProgramsCNC-Shadow.png',
    'nc'                    => 'images/iconos/ProgramsNC.png',
    'nc_shadow'             => 'images/iconos/ProgramsNC-Shadow.png',

    /*
    |--------------------------------------------------------------------------
    | Metadatos, Tiempos, Turnos y Tablas
    |--------------------------------------------------------------------------
    */
    'fecha'                 => 'images/iconos/Fecha.png',
    'fecha_icon'            => 'images/iconos/Fecha-Icon.png',
    'hora'                  => 'images/iconos/Hora.png',
    'calendario'            => 'images/iconos/calendario.png',
    'turno'                 => 'images/iconos/Turno-Icon.png',
    'turno_manana'          => 'images/iconos/Turno_Manana.png',
    'turno_mixto'           => 'images/iconos/Turno_Mixto.png',
    'turno_noche'           => 'images/iconos/Turno_Noche.png',
    'orden_asc'             => 'images/iconos/Orden_Asc.png',
    'orden_desc'            => 'images/iconos/Orden_Desc.png',
    'sin_orden'             => 'images/iconos/Sin_Orden.png',
    'triangulo_abajo'       => 'images/iconos/triangulo_abajo.png',
    'ot'                    => 'images/iconos/OT-Icon.png',
    'id'                    => 'images/iconos/ID-Icon.png',
    'codigo'                => 'images/iconos/Codigo-Icon.png',
    'folio'                 => 'images/iconos/folio-icon.png',
    'version'               => 'images/iconos/Version-Icon.png',
    'revision'              => 'images/iconos/Revision-Icon.png',
    'meta'                  => 'images/iconos/Meta-Icon.png',
    'stock'                 => 'images/iconos/Stock-Icon.png',
    'piezas'                => 'images/iconos/Piezas-Icon.png',
    'herramientas'          => 'images/iconos/Herramientas-Icon.png',
    'maquina'               => 'images/iconos/Maquina-Icon.png',
    'proceso'               => 'images/iconos/Proceso-Icon.png',

    /*
    |--------------------------------------------------------------------------
    | Cabeceras de Vistas (Módulos Principales)
    |--------------------------------------------------------------------------
    */
    'vista_liberacion'      => 'images/vistas/Liberacion_Pzas.png',
    'vista_dibujos_maq'     => 'images/vistas/Dibujos_Maquinados.png',
    'vista_dibujos_fund'    => 'images/vistas/Dibujos_Fundicion.png',
    'vista_ayudas_fund'     => 'images/vistas/Ayudas_Visuales_Fundicion.png',
    'vista_ayudas_maq'      => 'images/vistas/Ayudas_Visuales_Maquinados.png',
    'vista_prioridades'     => 'images/vistas/Prioridades.png',
    'vista_organigrama'     => 'images/vistas/Organigrama.png',
    'vista_maquinas'        => 'images/vistas/Maquinas_Ocupadas.png',
    'vista_piezas_ocupadas' => 'images/vistas/Piezas_Ocupadas.png',
    'vista_reporte_piezas'  => 'images/vistas/Reporte_Pzas.png',
    'vista_programas_cnc'   => 'images/vistas/Programas_CNC.png',
    'vista_manuales'        => 'images/vistas/Manuales_Procesos.png',
    'vista_herramientas'    => 'images/vistas/Herramientas_Tecamac.png',
    'vista_cotas'           => 'images/vistas/Cotas_Nominales_Tolerancias.png',
    'vista_crear_ot'        => 'images/vistas/Crear_Modificar_OT.png',
    'vista_edicion_molduras'=> 'images/vistas/Registro_Edicion_Molduras.png',
    'vista_recepcion_sold'  => 'images/vistas/Recepcion_Soldadura.png',
    'vista_entrega_sold'    => 'images/vistas/Entrega_Soldadura.png',
    'vista_qr_individual'   => 'images/vistas/Generar_QR_Individual.png',
    'vista_qr_lote'         => 'images/vistas/Generar_QR_Lote.png',
    'vista_regenerar_qr'    => 'images/vistas/Regenerar_QR.png',
    'vista_logs'            => 'images/vistas/Logs_Produccion_Administradores.png',
    'vista_recuperar_pass'  => 'images/vistas/Restablecer_Contrasena.png',

    /*
    |--------------------------------------------------------------------------
    | Usuarios y Roles de Sistema
    |--------------------------------------------------------------------------
    */
    'user_admin'            => 'images/usuarios/Administracion-Icon.png',
    'user_gerente'          => 'images/usuarios/gerente.png',
    'user_supervisor'       => 'images/usuarios/supervisor.png',
    'user_supervisora'      => 'images/usuarios/supervisora.png',
    'user_soldador'         => 'images/usuarios/soldador.png',
    'user_cnc'              => 'images/usuarios/operador_cnc.png',
    'user_centro_maquinado' => 'images/usuarios/operador_centro_maquinado.png',
    'user_becario'          => 'images/usuarios/becario.png',
    'user_ayudante'         => 'images/usuarios/ayudante general.png',
    'user_default'          => 'images/usuarios/usuario.png',
    'user_error'            => 'images/usuarios/personError.png',

    // Roles específicos de navegación y badges
    'rol_administrador'       => 'images/usuarios/Administracion-Icon.png',
    'rol_operador'            => 'images/usuarios/Operador-Icon.png',
    'rol_master'              => 'images/iconos/Master-Icon.png',
    'rol_calidad'             => 'images/usuarios/Calidad-Icon.png',
    'rol_almacen'             => 'images/iconos/Almacen-Icon.png',
    'rol_supervisor'          => 'images/usuarios/Supervision-Icon.png',

    'Administracion-Icon.png' => 'images/usuarios/Administracion-Icon.png',
    'Operador-Icon.png'       => 'images/usuarios/Operador-Icon.png',
    'Master-Icon.png'         => 'images/iconos/Master-Icon.png',
    'Calidad-Icon.png'        => 'images/usuarios/Calidad-Icon.png',
    'Almacen-Icon.png'        => 'images/iconos/Almacen-Icon.png',
    'Supervision-Icon.png'    => 'images/usuarios/Supervision-Icon.png',
    'image-icon.png'          => 'images/iconos/IMG-Icon.png',
    'img_icon'                => 'images/iconos/IMG-Icon.png',

    /*
    |--------------------------------------------------------------------------
    | Logotipos y Branding
    |--------------------------------------------------------------------------
    */
    'logo_gis'              => 'images/logos/Logo_GIS.png',
    'icono_gis'             => 'images/logos/Icono_GIS.png',
    'icono_menu'            => 'images/iconos/icono.png',
    'logo_saavedra'         => 'images/logos/Logo_GIS.png',
    'logo_saavedra_alt'     => 'images/logos/lg_saavedra2.png',
    'favicon'               => 'images/logos/favicon.ico',

    /*
    |--------------------------------------------------------------------------
    | Fondos de Pantalla
    |--------------------------------------------------------------------------
    */
    'fondo_login'           => 'images/fondos/fondoLogin.jpg',
    'fondo_admin'           => 'images/fondos/fondoadmin.png',
    'fondo_almacen'         => 'images/fondos/fondoalmacen.png',
    'fondo_calidad'         => 'images/fondos/fondocalidad.png',
    'fondo_procesos'        => 'images/fondos/fondoprocesos.png',
    'fondo_master'          => 'images/fondos/fondo master.png',
];
