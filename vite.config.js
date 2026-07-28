import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Base global — Paleta GIS, Poppins, utilidades compartidas
                "resources/css/global.css",

                //Layout messages
                "resources/css/layouts/partials/messages.css",
                "resources/js/layouts/partials/messages.js",

                //Layout appMenu
                "resources/css/layouts/appMenu.css",
                "resources/js/layouts/appMenu.js",
                "resources/js/layouts/productivity.js",

                //View home
                "resources/css/home.css",
                "resources/js/home.js",

                //View login
                "resources/css/auth/login.css",

                //View moldings
                "resources/css/moldings_views/create_molding.css",
                "resources/css/moldings_views/edit_molding.css",
                "resources/js/moldings_views/edit_molding.js",

                //Views OT
                "resources/css/wo_views/manage_wo.css",
                "resources/css/wo_views/show_wo.css",
                "resources/css/wo_views/show_wo_almacen.css",
                'resources/js/wo_views/manage_wo.js',
                'resources/js/wo_views/show_wo.js',
                'resources/js/wo_views/show_wo_almacen.js',

                //Views pieces
                "resources/css/pieces_views/piecesInProgress_view.css",
                "resources/js/pieces_views/piecesInProgress_view.js",
                "resources/css/pieces_views/priorityManager_view.css",
                "resources/js/pieces_views/priorityManager_view.js",

                "resources/js/pieces_views/piecesReport/piecesReport_view.js",
                "resources/css/pieces_views/piecesReport/chosenPiece.css",
                'resources/css/pieces_views/releasePieces/releasePieces_view.css',
                'resources/js/pieces_views/releasePieces/releasePieces_view.js',
                "resources/js/pieces_views/releasePieces/releasePieces.js",
                "resources/css/pieces_views/piecesReport/adminPieces.css",
                'resources/js/pieces_views/piecesReport/adminPieces.js',
                'resources/css/wo_views/progressPanel_wo.css',
                'resources/js/wo_views/progressPanel_wo.js',

                //Views users
                "resources/css/users_views/createUser.css",
                "resources/css/users_views/recoverPassword.css",
                'resources/css/users_views/productionData.css',
                'resources/js/users_views/productionData.js',

                //Views processes
                "resources/css/processes_views/cNominals_view.css",
                "resources/js/processes_views/cNominals_view.js",
                "resources/js/processes_views/Process.js",
                "resources/css/processes_views/productionTimes.css",
                "resources/js/processes_views/productionTimes.js",
                "resources/css/processes_views/processProduction.css",
                "resources/js/processes_views/processProduction.js",

                //Views machines
                'resources/css/machines_views/machinesOccupied.css',
                'resources/js/machines_views/machinesOccupied.js',

                'resources/css/machines2.css',
                'resources/css/view_users.css',
                'resources/js/view_users.js',

                //Generar QR individual
                'resources/js/welding_tracking/generate_qr_welding.js',
                'resources/js/welding_tracking/register_welding.js',
                'resources/js/welding_tracking/welding.js',
                'resources/css/welding_tracking_views/generate_individual_qr.css',
                'resources/css/welding_tracking_views/generate_batch_qr.css',
                'resources/css/welding_tracking_views/plant_reception.css',
                'resources/css/welding_tracking_views/regenerate_qr.css',
                'resources/css/welding_tracking/trackingSoldadura.css',

                //Liberar soldadura
                'resources/js/libs/html5-qrcode.min.js',
                'resources/css/welding_tracking_views/release_qr_plant.css',
                'resources/js/welding_tracking/release_welding.js',

                //Views PTA
                'resources/css/processes_views/soldaduraPTA_table_partial.css',
                'resources/css/pta_views/analysis.css',
                'resources/css/pta_views/analysis_pdf.css',
                'resources/css/pta_views/results.css',
                'resources/css/pta_views/segunda_pasada.css',
                'resources/css/pieces_views/piecesReport/soldaduraExtraInfoPdf.css',
                'resources/css/pieces_views/piecesReport/soldaduraPTAExtraInfoPdf.css',

                //Views Reporte Diario
                'resources/css/reports/email.css',
                'resources/css/reports/resend.css',
                'resources/css/reports/pta_send.css',

                //Módulo Documentacion Técnica
                'resources/css/wo_views/manage_dibujos.css',
                'resources/css/wo_views/manage_fundicion.css',
                'resources/css/wo_views/manage_manuales.css',
                'resources/css/wo_views/manage_ayudas.css',
                'resources/css/wo_views/manage_ayudas_fundicion.css',
                'resources/js/wo_views/manage_dibujos.js',
                'resources/js/wo_views/manage_fundicion.js',
                'resources/js/wo_views/manage_manuales.js',
                'resources/js/wo_views/manage_ayudas.js',
                'resources/js/wo_views/manage_ayudas_fundicion.js',

                //Vista Almacén/Calidad — Dibujos de Fundición
                'resources/css/warehouse_views/warehouse_casting.css',
                'resources/js/warehouse_views/warehouse_casting.js',
                'resources/css/warehouse_views/quality_casting.css',
                'resources/js/warehouse_views/quality_casting.js',

                //Vista Calidad — Dibujos y Ayudas de Maquinados
                'resources/css/quality_views/quality_machining.css',
                'resources/js/quality_views/quality_machining.js',

                // Views systemLogs
                "resources/css/reports/systemLogs.css",
                "resources/js/reports/systemLogs.js",

                // Vista Herramientas Tecamac
                'resources/css/tools_views/tools_tecamac.css',
                'resources/js/tools_views/tools_tecamac.js',
            ],
            refresh: true,
        }),
    ],
    // build: {
    //     b1ase: 'http://192.168.1.106:80/',
    // },
});
