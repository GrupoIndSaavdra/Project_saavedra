# ==============================================================================
# Script de Renombramiento - Fase 1 y 2 (resources/css, resources/js, resources/views)
# ==============================================================================

# 1. MOVER EL CONTENIDO DE 'reportes' a 'reports'
Write-Host "Moviendo archivos de reportes a reports..."
# Vistas
if (Test-Path "resources/views/reportes/envio_pta.blade.php") { git mv resources/views/reportes/envio_pta.blade.php resources/views/reports/pta_send.blade.php }
if (Test-Path "resources/views/reportes/reenvio.blade.php") { git mv resources/views/reportes/reenvio.blade.php resources/views/reports/resend.blade.php }
# CSS
if (Test-Path "resources/css/reportes/envio_pta.css") { git mv resources/css/reportes/envio_pta.css resources/css/reports/pta_send.css }
if (Test-Path "resources/css/reportes/reenvio.css") { git mv resources/css/reportes/reenvio.css resources/css/reports/resend.css }
if (Test-Path "resources/css/reportes/email.css") { git mv resources/css/reportes/email.css resources/css/reports/email.css }
if (Test-Path "resources/css/reportes/pdf.css") { git mv resources/css/reportes/pdf.css resources/css/reports/pdf.css }
# (A este punto la carpeta reportes debe quedar vacía y git la ignorará o podemos borrarla)
if (Test-Path "resources/views/reportes") { Remove-Item -Recurse -Force resources/views/reportes }
if (Test-Path "resources/css/reportes") { Remove-Item -Recurse -Force resources/css/reportes }


# 2. RENOMBRAR CARPETAS PRINCIPALES (Fase 1)
Write-Host "Renombrando carpetas..."
$folders = @(
    @{ Old = "almacen"; New = "warehouse" },
    @{ Old = "almacen_views"; New = "warehouse_views" },
    @{ Old = "calidad"; New = "quality" },
    @{ Old = "calidad_views"; New = "quality_views" },
    @{ Old = "herramientas"; New = "tools" },
    @{ Old = "herramientas_views"; New = "tools_views" },
    @{ Old = "trackingSoldadura"; New = "welding_tracking" },
    @{ Old = "trackingSoldadura_views"; New = "welding_tracking_views" }
)

foreach ($dir in "resources/views", "resources/css", "resources/js") {
    foreach ($f in $folders) {
        $oldPath = "$dir/$($f.Old)"
        $newPath = "$dir/$($f.New)"
        if (Test-Path $oldPath) {
            git mv $oldPath $newPath
        }
    }
}

# 3. RENOMBRAR ARCHIVOS (Fase 2)
Write-Host "Renombrando archivos..."

# --- VIEWS ---
$viewsRenames = @(
    @{ Old = "resources/views/warehouse/almacen_fundicion.blade.php"; New = "resources/views/warehouse/warehouse_casting.blade.php" },
    @{ Old = "resources/views/warehouse/pdf_liberacion.blade.php"; New = "resources/views/warehouse/pdf_release.blade.php" },
    @{ Old = "resources/views/warehouse/pdf_rechazo.blade.php"; New = "resources/views/warehouse/pdf_rejection.blade.php" },
    @{ Old = "resources/views/warehouse/pdf_pre_orden_casting.blade.php"; New = "resources/views/warehouse/pdf_pre_order_casting.blade.php" },
    @{ Old = "resources/views/quality/calidad_fundicion.blade.php"; New = "resources/views/quality/quality_casting.blade.php" },
    @{ Old = "resources/views/quality/maquinados_index.blade.php"; New = "resources/views/quality/machining_index.blade.php" },
    @{ Old = "resources/views/welding_tracking_views/generarQRIndividual.blade.php"; New = "resources/views/welding_tracking_views/generate_individual_qr.blade.php" },
    @{ Old = "resources/views/welding_tracking_views/generarQRLote.blade.php"; New = "resources/views/welding_tracking_views/generate_batch_qr.blade.php" },
    @{ Old = "resources/views/welding_tracking_views/liberarQRPlanta.blade.php"; New = "resources/views/welding_tracking_views/release_qr_plant.blade.php" },
    @{ Old = "resources/views/welding_tracking_views/qr_individuales_pdf.blade.php"; New = "resources/views/welding_tracking_views/individual_qr_pdf.blade.php" },
    @{ Old = "resources/views/welding_tracking_views/qr_lote_pdf.blade.php"; New = "resources/views/welding_tracking_views/batch_qr_pdf.blade.php" },
    @{ Old = "resources/views/welding_tracking_views/recepcionPlanta.blade.php"; New = "resources/views/welding_tracking_views/plant_reception.blade.php" },
    @{ Old = "resources/views/welding_tracking_views/regenerarQR_lista.blade.php"; New = "resources/views/welding_tracking_views/regenerate_qr_list.blade.php" },
    @{ Old = "resources/views/welding_tracking_views/regenerarQR_verificacion.blade.php"; New = "resources/views/welding_tracking_views/regenerate_qr_verification.blade.php" },
    @{ Old = "resources/views/welding_tracking_views/trackingSoldadura.blade.php"; New = "resources/views/welding_tracking_views/welding_tracking.blade.php" }
)

foreach ($r in $viewsRenames) {
    if (Test-Path $r.Old) { git mv $r.Old $r.New }
}

# --- CSS ---
$cssRenames = @(
    @{ Old = "resources/css/maquinas2.css"; New = "resources/css/machines2.css" },
    @{ Old = "resources/css/viewUsers.css"; New = "resources/css/view_users.css" },
    @{ Old = "resources/css/warehouse_views/almacen_fundicion.css"; New = "resources/css/warehouse_views/warehouse_casting.css" },
    @{ Old = "resources/css/warehouse_views/calidad_fundicion.css"; New = "resources/css/warehouse_views/quality_casting.css" },
    @{ Old = "resources/css/warehouse_views/lib_liberacion.css"; New = "resources/css/warehouse_views/lib_release.css" },
    @{ Old = "resources/css/quality_views/calidad_maquinados.css"; New = "resources/css/quality_views/quality_machining.css" },
    @{ Old = "resources/css/tools_views/herramientas_tecamac.css"; New = "resources/css/tools_views/tools_tecamac.css" },
    @{ Old = "resources/css/welding_tracking_views/generarQRIndividual.css"; New = "resources/css/welding_tracking_views/generate_individual_qr.css" },
    @{ Old = "resources/css/welding_tracking_views/generarQRLote.css"; New = "resources/css/welding_tracking_views/generate_batch_qr.css" },
    @{ Old = "resources/css/welding_tracking_views/liberarQRPlanta.css"; New = "resources/css/welding_tracking_views/release_qr_plant.css" },
    @{ Old = "resources/css/welding_tracking_views/recepcionPlanta.css"; New = "resources/css/welding_tracking_views/plant_reception.css" },
    @{ Old = "resources/css/welding_tracking_views/regenerarQR.css"; New = "resources/css/welding_tracking_views/regenerate_qr.css" }
)

foreach ($r in $cssRenames) {
    if (Test-Path $r.Old) { git mv $r.Old $r.New }
}

# --- JS ---
$jsRenames = @(
    @{ Old = "resources/js/viewUsers.js"; New = "resources/js/view_users.js" },
    @{ Old = "resources/js/warehouse_views/almacen_fundicion.js"; New = "resources/js/warehouse_views/warehouse_casting.js" },
    @{ Old = "resources/js/warehouse_views/calidad_fundicion.js"; New = "resources/js/warehouse_views/quality_casting.js" },
    @{ Old = "resources/js/quality_views/calidad_maquinados.js"; New = "resources/js/quality_views/quality_machining.js" },
    @{ Old = "resources/js/tools_views/herramientas_tecamac.js"; New = "resources/js/tools_views/tools_tecamac.js" },
    @{ Old = "resources/js/welding_tracking/generarQRSoldadura.js"; New = "resources/js/welding_tracking/generate_qr_welding.js" },
    @{ Old = "resources/js/welding_tracking/liberarSoldadura.js"; New = "resources/js/welding_tracking/release_welding.js" },
    @{ Old = "resources/js/welding_tracking/registerSoldadura.js"; New = "resources/js/welding_tracking/register_welding.js" },
    @{ Old = "resources/js/welding_tracking/soldadura.js"; New = "resources/js/welding_tracking/welding.js" }
)

foreach ($r in $jsRenames) {
    if (Test-Path $r.Old) { git mv $r.Old $r.New }
}

Write-Host "Renombramiento completado exitosamente."
