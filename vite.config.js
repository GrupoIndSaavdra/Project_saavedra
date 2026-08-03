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
                "resources/css/wo_views/show_wo_warehouse.css",
                'resources/js/wo_views/manage_wo.js',
                'resources/js/wo_views/show_wo.js',
                'resources/js/wo_views/show_wo_warehouse.js',

                //Views pieces
                "resources/css/pieces_views/pieces_in_progress_view.css",
                "resources/js/pieces_views/pieces_in_progress_view.js",
                "resources/css/pieces_views/priority_manager_view.css",
                "resources/js/pieces_views/priority_manager_view.js",

                "resources/js/pieces_views/pieces_report/pieces_report_view.js",
                "resources/css/pieces_views/pieces_report/chosen_piece.css",
                'resources/css/pieces_views/release_pieces/release_pieces_view.css',
                'resources/js/pieces_views/release_pieces/release_pieces_view.js',
                "resources/js/pieces_views/release_pieces/release_pieces.js",
                "resources/css/pieces_views/pieces_report/admin_pieces.css",
                'resources/js/pieces_views/pieces_report/admin_pieces.js',
                'resources/css/wo_views/progress_panel_wo.css',
                'resources/js/wo_views/progress_panel_wo.js',

                //Views users
                "resources/css/users_views/create_user.css",
                "resources/css/users_views/recover_password.css",
                'resources/css/users_views/production_data.css',
                'resources/js/users_views/production_data.js',

                //Views processes
                "resources/css/processes_views/c_nominals_view.css",
                "resources/js/processes_views/c_nominals_view.js",
                "resources/js/processes_views/Process.js",
                "resources/css/processes_views/production_times.css",
                "resources/js/processes_views/production_times.js",
                "resources/css/processes_views/process_production.css",
                "resources/js/processes_views/process_production.js",

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
                'resources/css/welding_tracking/welding_tracking.css',

                //Liberar soldadura
                'resources/js/libs/html5-qrcode.min.js',
                'resources/css/welding_tracking_views/release_qr_plant.css',
                'resources/js/welding_tracking/release_welding.js',

                //Views PTA
                'resources/css/processes_views/welding_pta_table_partial.css',
                'resources/css/pta_views/analysis.css',
                'resources/css/pta_views/analysis_pdf.css',
                'resources/css/pta_views/results.css',
                'resources/css/pta_views/second_pass.css',
                'resources/css/pieces_views/pieces_report/welding_extra_info_pdf.css',
                'resources/css/pieces_views/pieces_report/welding_pta_extra_info_pdf.css',

                //Views Reporte Diario
                'resources/css/reports/email.css',
                'resources/css/reports/resend.css',
                'resources/css/reports/pta_send.css',

                //Módulo Documentacion Técnica
                'resources/css/wo_views/manage_drawings.css',
                'resources/css/wo_views/manage_casting.css',
                'resources/css/wo_views/manage_manuals.css',
                'resources/css/wo_views/manage_visual_aids.css',
                'resources/css/wo_views/manage_casting_visual_aids.css',
                'resources/js/wo_views/manage_drawings.js',
                'resources/js/wo_views/manage_casting.js',
                'resources/js/wo_views/manage_manuals.js',
                'resources/js/wo_views/manage_visual_aids.js',
                'resources/js/wo_views/manage_casting_visual_aids.js',

                //Vista Almacén/Calidad — Dibujos de Fundición
                'resources/css/warehouse_views/warehouse_casting.css',
                'resources/js/warehouse_views/warehouse_casting.js',
                'resources/css/warehouse_views/quality_casting.css',
                'resources/js/warehouse_views/quality_casting.js',

                //Vista Calidad — Dibujos y Ayudas de Maquinados
                'resources/css/quality_views/quality_machining.css',
                'resources/js/quality_views/quality_machining.js',

                // Views system_logs
                "resources/css/reports/system_logs.css",
                "resources/js/reports/system_logs.js",

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
