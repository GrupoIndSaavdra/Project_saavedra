import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                //Layout messages
                "resources/css/layouts/partials/messages.css",
                "resources/js/layouts/partials/messages.js",

                //Layout appMenu
                "resources/css/layouts/appMenu.css",
                "resources/js/layouts/appMenu.js",
                
                //View home
                "resources/css/home.css",

                //View login
                "resources/css/auth/login.css",
                
                //View moldings
                "resources/css/moldings_views/create_molding.css",
                "resources/css/moldings_views/edit_molding.css",
                "resources/js/moldings_views/edit_molding.js",
                
                //Views OT
                "resources/css/wo_views/manage_wo.css", 
                "resources/css/wo_views/show_wo.css",
                'resources/js/wo_views/manage_wo.js',
                'resources/js/wo_views/show_wo.js',

                //Views pieces
                "resources/css/pieces_views/piecesInProgress_view.css",
                "resources/js/pieces_views/piecesInProgress_view.js",
                "resources/css/pieces_views/piecesReport/piecesReport_view.css",
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


                "resources/css/cepillado.css",
                "resources/css/barreno.css",
                "resources/js/editarInterfaz.js",
                "resources/js/editarTabla.js",
                'resources/css/maquinas2.css',
                'resources/css/rectificado.css',
                'resources/css/copiado.css',
                'resources/css/viewUsers.css',
                'resources/js/viewUsers.js',
            ],
            refresh: true,
        }),
    ],
    // build: {
    //     b1ase: 'http://192.168.1.106:80/',
    // },
});
