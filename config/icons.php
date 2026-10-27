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
    'aprobado'              => 'images/Color_Icons/Generales/Aprobado.png',
    'rechazado'             => 'images/Color_Icons/Generales/Rechazado.png',
    'espera'                => 'images/Color_Icons/Generales/Espera.png',
    'recibido'              => 'images/Color_Icons/Generales/Recibido.png',
    'revisando'             => 'images/Color_Icons/Generales/Revisando.png',
    'editando'              => 'images/Color_Icons/Generales/Editando.png',
    'guardado'              => 'images/Color_Icons/Generales/Guardado.png',
    'ready'                 => 'images/Color_Icons/Generales/ready.png',
    'alerta'                => 'images/Color_Icons/Generales/Alerta-Icon.png',
    'aviso'                 => 'images/Color_Icons/Generales/Aviso.png',
    'critica'               => 'images/Color_Icons/Generales/Critica.png',
    'sospechosa'            => 'images/Color_Icons/Generales/Sospechosa.png',
    'error'                 => 'images/Color_Icons/Generales/error.png',
    'bloqueado'             => 'images/Light_Icons/Bloqueado-Light.png',
    'desbloqueado'          => 'images/Color_Icons/Generales/desbloqueado.png',
    'bloqueado_maquina'     => 'images/Color_Icons/Generales/Bloq-Maqu-Icon.png',
    'desbloqueado_maquina'  => 'images/Color_Icons/Generales/Desbloc-Maq-Icon.png',
    'por_liberar'           => 'images/Color_Icons/Generales/por_liberar_icon.png',

    /*
    |--------------------------------------------------------------------------
    | Botones y Acciones de Usuario
    |--------------------------------------------------------------------------
    */
    'liberar'               => 'images/Color_Icons/Generales/Liberar.png',
    'rechazar'              => 'images/Color_Icons/Generales/Rechazar.png',
    'eliminar'              => 'images/Color_Icons/Generales/delete.png',
    'cancelar'              => 'images/Light_Icons/Cancelar-Light.png',
    'quitar'                => 'images/Color_Icons/Generales/quitar.png',
    'eliminar_archivos'     => 'images/Color_Icons/Generales/Eliminar-Archivos.png',
    'eliminar_carpeta'      => 'images/Color_Icons/Generales/Eliminar-Carpeta.png',
    'editar'                => 'images/Color_Icons/Generales/edit.png',
    'editar_info'           => 'images/Color_Icons/Generales/editar-informacion.png',
    'editar_user'           => 'images/Color_Icons/Usuarios/editaruser.png',
    'anadir'                => 'images/Color_Icons/Generales/anadir.png',
    'actualizar'            => 'images/Color_Icons/Generales/actualizar.png',
    'descarga'              => 'images/Color_Icons/Generales/Descarga.png',
    'descarga_excel'        => 'images/Color_Icons/Generales/Descargar_Excel-Icon.png',
    'descarga_excel_shadow' => 'images/Light_Icons/Descargar_Excel-Light.png',
    'descarga_pdf'          => 'images/Color_Icons/Generales/Descargar_PDF-Icon.png',
    'descarga_pdf_shadow'   => 'images/Light_Icons/Descargar_PDF-Light.png',
    'imprimir'              => 'images/Color_Icons/Generales/Imprimir-Icon.png',
    'imprimir_shadow'       => 'images/Light_Icons/Imprimir-Light.png',
    'upload'                => 'images/Color_Icons/Generales/upload_icon.png',
    'reemplazar'            => 'images/Color_Icons/Generales/reemplazar.png',
    'reiniciar'             => 'images/Color_Icons/Generales/reiniciar.png',
    'priorizar'             => 'images/Color_Icons/Generales/priorizar.png',
    'escanear'              => 'images/Color_Icons/Generales/Escanear-icon.png',
    'enviando'              => 'images/Color_Icons/Generales/enviando.png',
    'redireccionar'         => 'images/Color_Icons/Generales/redireccionar.png',
    'lupa'                  => 'images/Color_Icons/Generales/lupa.png',
    'ojito'                 => 'images/Color_Icons/Generales/ojito.png',
    'zoom_in'               => 'images/Light_Icons/Mas_Zoom-Light.png',
    'zoom_out'              => 'images/Light_Icons/Menos_Zoom-Light.png',
    'cerrar'                => 'images/Color_Icons/Generales/cerrar.png',
    'loading'               => 'images/Color_Icons/Generales/loading.gif',

    /*
    |--------------------------------------------------------------------------
    | Tipos de Archivos, Documentación y Viewers
    |--------------------------------------------------------------------------
    */
    'dwg'                   => 'images/Color_Icons/Generales/dwg.png',
    'dwg_shadow'            => 'images/Shadow_Icons/DWG-Shadow.png',
    'pdf'                   => 'images/Color_Icons/Generales/pdf.png',
    'pdf_view'              => 'images/Color_Icons/Generales/pdf-view.png',
    'pdf_view_shadow'       => 'images/Shadow_Icons/PDF_View-Shadow.png',
    'pdf_transparent'       => 'images/Color_Icons/Generales/pdf-transparent.png',
    'galeria'               => 'images/Color_Icons/Generales/galeria.png',
    'galeria_shadow'        => 'images/Shadow_Icons/Galeria-Shadow.png',
    'camara'                => 'images/Color_Icons/Generales/Camara-View.png',
    'camara_shadow'         => 'images/Shadow_Icons/Camara-Shadow.png',
    'documento'             => 'images/Color_Icons/Generales/documento.png',
    'sin_archivos'          => 'images/Color_Icons/Generales/sin_archivos.png',
    'sin_carpetas'          => 'images/Color_Icons/Generales/Sin_Carpetas.png',
    'sin_av'                => 'images/Color_Icons/Generales/sin_AV.png',
    'copia_datos'           => 'images/Color_Icons/Generales/copia-de-datos.png',
    'cnc'                   => 'images/Color_Icons/Generales/ProgramsCNC.png',
    'cnc_shadow'            => 'images/Shadow_Icons/ProgramsCNC-Shadow.png',
    'nc'                    => 'images/Color_Icons/Generales/ProgramsNC.png',
    'nc_shadow'             => 'images/Shadow_Icons/ProgramsNC-Shadow.png',

    /*
    |--------------------------------------------------------------------------
    | Metadatos, Tiempos, Turnos y Tablas
    |--------------------------------------------------------------------------
    */
    'fecha'                 => 'images/Color_Icons/Generales/Fecha.png',
    'fecha_icon'            => 'images/Light_Icons/Fecha-Light.png',
    'hora'                  => 'images/Color_Icons/Generales/Hora.png',
    'calendario'            => 'images/Color_Icons/Generales/calendario.png',
    'turno'                 => 'images/Light_Icons/Turno-Light.png',
    'turno_manana'          => 'images/Color_Icons/Generales/Turno_Manana.png',
    'turno_mixto'           => 'images/Color_Icons/Generales/Turno_Mixto.png',
    'turno_noche'           => 'images/Color_Icons/Generales/Turno_Noche.png',
    'orden_asc'             => 'images/Color_Icons/Generales/Orden_Asc.png',
    'orden_desc'            => 'images/Color_Icons/Generales/Orden_Desc.png',
    'sin_orden'             => 'images/Color_Icons/Generales/Sin_Orden.png',
    'triangulo_abajo'       => 'images/Color_Icons/Generales/triangulo_abajo.png',
    'ot'                    => 'images/Light_Icons/OT-Light.png',
    'id'                    => 'images/Light_Icons/ID-Light.png',
    'codigo'                => 'images/Color_Icons/Generales/Codigo-Icon.png',
    'folio'                 => 'images/Color_Icons/Generales/folio-icon.png',
    'version'               => 'images/Light_Icons/Version-Light.png',
    'revision'              => 'images/Light_Icons/Revision-Light.png',
    'meta'                  => 'images/Light_Icons/Meta-Light.png',
    'stock'                 => 'images/Light_Icons/Stock-Light.png',
    'piezas'                => 'images/Light_Icons/Piezas-Light.png',
    'herramientas'          => 'images/Light_Icons/Herramientas-Light.png',
    'maquina'               => 'images/Light_Icons/Maquina-Light.png',
    'proceso'               => 'images/Light_Icons/Proceso-Light.png',

    /*
    |--------------------------------------------------------------------------
    | Cabeceras de Vistas (Módulos Principales)
    |--------------------------------------------------------------------------
    */
    'vista_liberacion'      => 'images/Color_Icons/Vistas/Liberacion_Pzas.png',
    'vista_dibujos_maq'     => 'images/Color_Icons/Vistas/Dibujos_Maquinados.png',
    'vista_dibujos_fund'    => 'images/Color_Icons/Vistas/Dibujos_Fundicion.png',
    'vista_ayudas_fund'     => 'images/Color_Icons/Vistas/Ayudas_Visuales_Fundicion.png',
    'vista_ayudas_maq'      => 'images/Color_Icons/Vistas/Ayudas_Visuales_Maquinados.png',
    'vista_prioridades'     => 'images/Color_Icons/Vistas/Prioridades.png',
    'vista_organigrama'     => 'images/Color_Icons/Vistas/Organigrama.png',
    'vista_maquinas'        => 'images/Color_Icons/Vistas/Maquinas_Ocupadas.png',
    'vista_piezas_ocupadas' => 'images/Color_Icons/Vistas/Piezas_Ocupadas.png',
    'vista_reporte_piezas'  => 'images/Color_Icons/Vistas/Reporte_Pzas.png',
    'vista_programas_cnc'   => 'images/Color_Icons/Vistas/Programas_CNC.png',
    'vista_manuales'        => 'images/Color_Icons/Vistas/Manuales_Procesos.png',
    'vista_herramientas'    => 'images/Color_Icons/Vistas/Herramientas_Tecamac.png',
    'vista_cotas'           => 'images/Color_Icons/Vistas/Cotas_Nominales_Tolerancias.png',
    'vista_crear_ot'        => 'images/Color_Icons/Vistas/Crear_Modificar_OT.png',
    'vista_edicion_molduras'=> 'images/Color_Icons/Vistas/Registro_Edicion_Molduras.png',
    'vista_recepcion_sold'  => 'images/Color_Icons/Vistas/Recepcion_Soldadura.png',
    'vista_entrega_sold'    => 'images/Color_Icons/Vistas/Entrega_Soldadura.png',
    'vista_qr_individual'   => 'images/Color_Icons/Vistas/Generar_QR_Individual.png',
    'vista_qr_lote'         => 'images/Color_Icons/Vistas/Generar_QR_Lote.png',
    'vista_regenerar_qr'    => 'images/Color_Icons/Vistas/Regenerar_QR.png',
    'vista_logs'            => 'images/Color_Icons/Vistas/Logs_Produccion_Administradores.png',
    'vista_recuperar_pass'  => 'images/Color_Icons/Vistas/Restablecer_Contrasena.png',

    /*
    |--------------------------------------------------------------------------
    | Usuarios y Roles de Sistema
    |--------------------------------------------------------------------------
    */
    'user_admin'            => 'images/Light_Icons/Administracion-Light.png',
    'user_gerente'          => 'images/Color_Icons/Usuarios/gerente.png',
    'user_supervisor'       => 'images/Color_Icons/Usuarios/supervisor.png',
    'user_supervisora'      => 'images/Color_Icons/Usuarios/supervisora.png',
    'user_soldador'         => 'images/Color_Icons/Usuarios/soldador.png',
    'user_cnc'              => 'images/Color_Icons/Usuarios/operador_cnc.png',
    'user_centro_maquinado' => 'images/Color_Icons/Usuarios/operador_centro_maquinado.png',
    'user_becario'          => 'images/Color_Icons/Usuarios/becario.png',
    'user_ayudante'         => 'images/Color_Icons/Usuarios/ayudante_general.png',
    'user_default'          => 'images/Color_Icons/Usuarios/usuario.png',
    'user_error'            => 'images/Color_Icons/Usuarios/personError.png',

    // Roles específicos de navegación y badges
    'rol_administrador'       => 'images/Light_Icons/Administracion-Light.png',
    'rol_operador'            => 'images/Light_Icons/Operador-Light.png',
    'rol_master'              => 'images/Light_Icons/Master-Light.png',
    'rol_calidad'             => 'images/Light_Icons/Calidad-Light.png',
    'rol_almacen'             => 'images/Light_Icons/Almacen-Light.png',
    'rol_supervisor'          => 'images/Light_Icons/Supervision-Light.png',

    'Administracion-Icon.png' => 'images/Light_Icons/Administracion-Light.png',
    'Operador-Icon.png'       => 'images/Light_Icons/Operador-Light.png',
    'Master-Icon.png'         => 'images/Light_Icons/Master-Light.png',
    'Calidad-Icon.png'        => 'images/Light_Icons/Calidad-Light.png',
    'Almacen-Icon.png'        => 'images/Light_Icons/Almacen-Light.png',
    'Supervision-Icon.png'    => 'images/Light_Icons/Supervision-Light.png',
    'image-icon.png'          => 'images/Light_Icons/IMG-Light.png',
    'img_icon'                => 'images/Light_Icons/IMG-Light.png',

    /*
    |--------------------------------------------------------------------------
    | Logotipos y Branding
    |--------------------------------------------------------------------------
    */
    'logo_gis'              => 'images/logos/Logo_GIS.png',
    'icono_gis'             => 'images/logos/Icono_GIS.png',
    'icono_menu'            => 'images/Color_Icons/Generales/icono.png',
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
