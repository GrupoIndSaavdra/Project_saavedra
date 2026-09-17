// vite.config.js
import { defineConfig } from "file:///C:/xampp/htdocs/Project_saavedra/node_modules/vite/dist/node/index.js";
import laravel from "file:///C:/xampp/htdocs/Project_saavedra/node_modules/laravel-vite-plugin/dist/index.mjs";
var vite_config_default = defineConfig({
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
        "resources/css/wo_views/create_master_wo.css",
        "resources/css/wo_views/priorities.css",
        "resources/js/wo_views/priorities_pdf.js",
        "resources/js/wo_views/manage_wo.js",
        "resources/js/wo_views/show_wo_master.js",
        "resources/js/wo_views/show_wo_programacion.js",
        "resources/js/wo_views/show_wo_almacen.js",
        "resources/js/wo_views/create_master_wo.js",
        //Views pieces
        "resources/css/pieces_views/piecesInProgress_view.css",
        "resources/js/pieces_views/piecesInProgress_view.js",
        "resources/css/pieces_views/priorityManager_view.css",
        "resources/js/pieces_views/priorityManager_view.js",
        "resources/js/pieces_views/piecesReport/piecesReport_view.js",
        "resources/css/pieces_views/piecesReport/chosenPiece.css",
        "resources/css/pieces_views/releasePieces/releasePieces_view.css",
        "resources/js/pieces_views/releasePieces/releasePieces_view.js",
        "resources/js/pieces_views/releasePieces/releasePieces.js",
        "resources/css/pieces_views/piecesReport/adminPieces.css",
        "resources/js/pieces_views/piecesReport/adminPieces.js",
        "resources/css/wo_views/progressPanel_wo.css",
        "resources/js/wo_views/progressPanel_wo.js",
        //Views users
        "resources/css/users_views/createUser.css",
        "resources/css/users_views/recoverPassword.css",
        "resources/css/users_views/productionData.css",
        "resources/js/users_views/productionData.js",
        "resources/css/users_views/organigrama.css",
        "resources/js/users_views/organigrama.js",
        //Views processes
        "resources/css/processes_views/cNominals_view.css",
        "resources/js/processes_views/cNominals_view.js",
        "resources/js/processes_views/Process.js",
        "resources/css/processes_views/productionTimes.css",
        "resources/js/processes_views/productionTimes.js",
        "resources/css/processes_views/processProduction.css",
        "resources/js/processes_views/processProduction.js",
        //Views machines
        "resources/css/machines_views/machinesOccupied.css",
        "resources/js/machines_views/machinesOccupied.js",
        "resources/css/maquinas2.css",
        "resources/css/viewUsers.css",
        "resources/js/viewUsers.js",
        //Generar QR individual
        "resources/js/TrackingSoldadura/generarQRSoldadura.js",
        "resources/js/TrackingSoldadura/registerSoldadura.js",
        "resources/js/TrackingSoldadura/soldadura.js",
        "resources/css/trackingSoldadura_views/generarQRIndividual.css",
        "resources/css/trackingSoldadura_views/generarQRLote.css",
        "resources/css/trackingSoldadura_views/recepcionPlanta.css",
        "resources/css/trackingSoldadura_views/regenerarQR.css",
        "resources/css/trackingSoldadura/trackingSoldadura.css",
        //Liberar soldadura
        "resources/js/libs/html5-qrcode.min.js",
        "resources/css/trackingSoldadura_views/liberarQRPlanta.css",
        "resources/js/trackingSoldadura/liberarSoldadura.js",
        //Views PTA
        "resources/css/processes_views/soldaduraPTA_table_partial.css",
        "resources/css/pta_views/analysis.css",
        "resources/css/pta_views/analysis_pdf.css",
        "resources/css/pta_views/results.css",
        "resources/css/pta_views/segunda_pasada.css",
        "resources/css/pieces_views/piecesReport/soldaduraExtraInfoPdf.css",
        "resources/css/pieces_views/piecesReport/soldaduraPTAExtraInfoPdf.css",
        //Views Reporte Diario
        "resources/css/reportes/email.css",
        "resources/css/reportes/reenvio.css",
        "resources/css/reportes/envio_pta.css",
        //Módulo Documentacion Técnica
        "resources/css/wo_views/manage_dibujos.css",
        "resources/css/wo_views/manage_fundicion.css",
        "resources/css/wo_views/manage_manuales.css",
        "resources/css/wo_views/manage_ayudas.css",
        "resources/css/wo_views/manage_ayudas_fundicion.css",
        "resources/js/wo_views/manage_dibujos.js",
        "resources/js/wo_views/manage_fundicion.js",
        "resources/js/wo_views/manage_manuales.js",
        "resources/js/wo_views/manage_ayudas.js",
        "resources/js/wo_views/manage_ayudas_fundicion.js",
        //Vista Almacén/Calidad — Dibujos de Fundición
        "resources/css/almacen_views/almacen_fundicion.css",
        "resources/js/almacen_views/almacen_fundicion.js",
        "resources/css/almacen_views/calidad_fundicion.css",
        "resources/js/almacen_views/calidad_fundicion.js",
        //Vista Calidad — Dibujos y Ayudas de Maquinados
        "resources/css/calidad_views/calidad_maquinados.css",
        "resources/js/calidad_views/calidad_maquinados.js",
        // Views systemLogs
        "resources/css/reports/systemLogs.css",
        "resources/js/reports/systemLogs.js",
        // Vista Herramientas Tecamac
        "resources/css/herramientas_views/herramientas_tecamac.css",
        "resources/js/herramientas_views/herramientas_tecamac.js"
      ],
      refresh: true
    })
  ]
  // build: {
  //     b1ase: 'http://192.168.1.106:80/',
  // },
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJDOlxcXFx4YW1wcFxcXFxodGRvY3NcXFxcUHJvamVjdF9zYWF2ZWRyYVwiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9maWxlbmFtZSA9IFwiQzpcXFxceGFtcHBcXFxcaHRkb2NzXFxcXFByb2plY3Rfc2FhdmVkcmFcXFxcdml0ZS5jb25maWcuanNcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfaW1wb3J0X21ldGFfdXJsID0gXCJmaWxlOi8vL0M6L3hhbXBwL2h0ZG9jcy9Qcm9qZWN0X3NhYXZlZHJhL3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSBcInZpdGVcIjtcbmltcG9ydCBsYXJhdmVsIGZyb20gXCJsYXJhdmVsLXZpdGUtcGx1Z2luXCI7XG5cbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZyh7XG4gICAgcGx1Z2luczogW1xuICAgICAgICBsYXJhdmVsKHtcbiAgICAgICAgICAgIGlucHV0OiBbXG4gICAgICAgICAgICAgICAgLy8gQmFzZSBnbG9iYWwgXHUyMDE0IFBhbGV0YSBHSVMsIFBvcHBpbnMsIHV0aWxpZGFkZXMgY29tcGFydGlkYXNcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9jc3MvZ2xvYmFsLmNzc1wiLFxuXG4gICAgICAgICAgICAgICAgLy9MYXlvdXQgbWVzc2FnZXNcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9jc3MvbGF5b3V0cy9wYXJ0aWFscy9tZXNzYWdlcy5jc3NcIixcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9qcy9sYXlvdXRzL3BhcnRpYWxzL21lc3NhZ2VzLmpzXCIsXG5cbiAgICAgICAgICAgICAgICAvL0xheW91dCBhcHBNZW51XG4gICAgICAgICAgICAgICAgXCJyZXNvdXJjZXMvY3NzL2xheW91dHMvYXBwTWVudS5jc3NcIixcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9qcy9sYXlvdXRzL2FwcE1lbnUuanNcIixcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9qcy9sYXlvdXRzL3Byb2R1Y3Rpdml0eS5qc1wiLFxuXG4gICAgICAgICAgICAgICAgLy9WaWV3IGhvbWVcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9jc3MvaG9tZS5jc3NcIixcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9qcy9ob21lLmpzXCIsXG5cbiAgICAgICAgICAgICAgICAvL1ZpZXcgbG9naW5cbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9jc3MvYXV0aC9sb2dpbi5jc3NcIixcblxuICAgICAgICAgICAgICAgIC8vVmlldyBtb2xkaW5nc1xuICAgICAgICAgICAgICAgIFwicmVzb3VyY2VzL2Nzcy9tb2xkaW5nc192aWV3cy9jcmVhdGVfbW9sZGluZy5jc3NcIixcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9jc3MvbW9sZGluZ3Nfdmlld3MvZWRpdF9tb2xkaW5nLmNzc1wiLFxuICAgICAgICAgICAgICAgIFwicmVzb3VyY2VzL2pzL21vbGRpbmdzX3ZpZXdzL2VkaXRfbW9sZGluZy5qc1wiLFxuXG4gICAgICAgICAgICAgICAgLy9WaWV3cyBPVFxuICAgICAgICAgICAgICAgIFwicmVzb3VyY2VzL2Nzcy93b192aWV3cy9tYW5hZ2Vfd28uY3NzXCIsXG4gICAgICAgICAgICAgICAgXCJyZXNvdXJjZXMvY3NzL3dvX3ZpZXdzL3Nob3dfd28uY3NzXCIsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3Mvd29fdmlld3Mvc2hvd193b19hbG1hY2VuLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3Mvd29fdmlld3MvY3JlYXRlX21hc3Rlcl93by5jc3MnLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvY3NzL3dvX3ZpZXdzL3ByaW9yaXRpZXMuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL3dvX3ZpZXdzL3ByaW9yaXRpZXNfcGRmLmpzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL3dvX3ZpZXdzL21hbmFnZV93by5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy93b192aWV3cy9zaG93X3dvX21hc3Rlci5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy93b192aWV3cy9zaG93X3dvX3Byb2dyYW1hY2lvbi5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy93b192aWV3cy9zaG93X3dvX2FsbWFjZW4uanMnLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvanMvd29fdmlld3MvY3JlYXRlX21hc3Rlcl93by5qcycsXG5cbiAgICAgICAgICAgICAgICAvL1ZpZXdzIHBpZWNlc1xuICAgICAgICAgICAgICAgIFwicmVzb3VyY2VzL2Nzcy9waWVjZXNfdmlld3MvcGllY2VzSW5Qcm9ncmVzc192aWV3LmNzc1wiLFxuICAgICAgICAgICAgICAgIFwicmVzb3VyY2VzL2pzL3BpZWNlc192aWV3cy9waWVjZXNJblByb2dyZXNzX3ZpZXcuanNcIixcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9jc3MvcGllY2VzX3ZpZXdzL3ByaW9yaXR5TWFuYWdlcl92aWV3LmNzc1wiLFxuICAgICAgICAgICAgICAgIFwicmVzb3VyY2VzL2pzL3BpZWNlc192aWV3cy9wcmlvcml0eU1hbmFnZXJfdmlldy5qc1wiLFxuXG4gICAgICAgICAgICAgICAgXCJyZXNvdXJjZXMvanMvcGllY2VzX3ZpZXdzL3BpZWNlc1JlcG9ydC9waWVjZXNSZXBvcnRfdmlldy5qc1wiLFxuICAgICAgICAgICAgICAgIFwicmVzb3VyY2VzL2Nzcy9waWVjZXNfdmlld3MvcGllY2VzUmVwb3J0L2Nob3NlblBpZWNlLmNzc1wiLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvY3NzL3BpZWNlc192aWV3cy9yZWxlYXNlUGllY2VzL3JlbGVhc2VQaWVjZXNfdmlldy5jc3MnLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvanMvcGllY2VzX3ZpZXdzL3JlbGVhc2VQaWVjZXMvcmVsZWFzZVBpZWNlc192aWV3LmpzJyxcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9qcy9waWVjZXNfdmlld3MvcmVsZWFzZVBpZWNlcy9yZWxlYXNlUGllY2VzLmpzXCIsXG4gICAgICAgICAgICAgICAgXCJyZXNvdXJjZXMvY3NzL3BpZWNlc192aWV3cy9waWVjZXNSZXBvcnQvYWRtaW5QaWVjZXMuY3NzXCIsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9waWVjZXNfdmlld3MvcGllY2VzUmVwb3J0L2FkbWluUGllY2VzLmpzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy93b192aWV3cy9wcm9ncmVzc1BhbmVsX3dvLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy93b192aWV3cy9wcm9ncmVzc1BhbmVsX3dvLmpzJyxcblxuICAgICAgICAgICAgICAgIC8vVmlld3MgdXNlcnNcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9jc3MvdXNlcnNfdmlld3MvY3JlYXRlVXNlci5jc3NcIixcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9jc3MvdXNlcnNfdmlld3MvcmVjb3ZlclBhc3N3b3JkLmNzc1wiLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvY3NzL3VzZXJzX3ZpZXdzL3Byb2R1Y3Rpb25EYXRhLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy91c2Vyc192aWV3cy9wcm9kdWN0aW9uRGF0YS5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3MvdXNlcnNfdmlld3Mvb3JnYW5pZ3JhbWEuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL3VzZXJzX3ZpZXdzL29yZ2FuaWdyYW1hLmpzJyxcblxuICAgICAgICAgICAgICAgIC8vVmlld3MgcHJvY2Vzc2VzXG4gICAgICAgICAgICAgICAgXCJyZXNvdXJjZXMvY3NzL3Byb2Nlc3Nlc192aWV3cy9jTm9taW5hbHNfdmlldy5jc3NcIixcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9qcy9wcm9jZXNzZXNfdmlld3MvY05vbWluYWxzX3ZpZXcuanNcIixcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9qcy9wcm9jZXNzZXNfdmlld3MvUHJvY2Vzcy5qc1wiLFxuICAgICAgICAgICAgICAgIFwicmVzb3VyY2VzL2Nzcy9wcm9jZXNzZXNfdmlld3MvcHJvZHVjdGlvblRpbWVzLmNzc1wiLFxuICAgICAgICAgICAgICAgIFwicmVzb3VyY2VzL2pzL3Byb2Nlc3Nlc192aWV3cy9wcm9kdWN0aW9uVGltZXMuanNcIixcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9jc3MvcHJvY2Vzc2VzX3ZpZXdzL3Byb2Nlc3NQcm9kdWN0aW9uLmNzc1wiLFxuICAgICAgICAgICAgICAgIFwicmVzb3VyY2VzL2pzL3Byb2Nlc3Nlc192aWV3cy9wcm9jZXNzUHJvZHVjdGlvbi5qc1wiLFxuXG4gICAgICAgICAgICAgICAgLy9WaWV3cyBtYWNoaW5lc1xuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvY3NzL21hY2hpbmVzX3ZpZXdzL21hY2hpbmVzT2NjdXBpZWQuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL21hY2hpbmVzX3ZpZXdzL21hY2hpbmVzT2NjdXBpZWQuanMnLFxuXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3MvbWFxdWluYXMyLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3Mvdmlld1VzZXJzLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy92aWV3VXNlcnMuanMnLFxuXG4gICAgICAgICAgICAgICAgLy9HZW5lcmFyIFFSIGluZGl2aWR1YWxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL1RyYWNraW5nU29sZGFkdXJhL2dlbmVyYXJRUlNvbGRhZHVyYS5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9UcmFja2luZ1NvbGRhZHVyYS9yZWdpc3RlclNvbGRhZHVyYS5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9UcmFja2luZ1NvbGRhZHVyYS9zb2xkYWR1cmEuanMnLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvY3NzL3RyYWNraW5nU29sZGFkdXJhX3ZpZXdzL2dlbmVyYXJRUkluZGl2aWR1YWwuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy90cmFja2luZ1NvbGRhZHVyYV92aWV3cy9nZW5lcmFyUVJMb3RlLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3MvdHJhY2tpbmdTb2xkYWR1cmFfdmlld3MvcmVjZXBjaW9uUGxhbnRhLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3MvdHJhY2tpbmdTb2xkYWR1cmFfdmlld3MvcmVnZW5lcmFyUVIuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy90cmFja2luZ1NvbGRhZHVyYS90cmFja2luZ1NvbGRhZHVyYS5jc3MnLFxuXG4gICAgICAgICAgICAgICAgLy9MaWJlcmFyIHNvbGRhZHVyYVxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvanMvbGlicy9odG1sNS1xcmNvZGUubWluLmpzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy90cmFja2luZ1NvbGRhZHVyYV92aWV3cy9saWJlcmFyUVJQbGFudGEuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL3RyYWNraW5nU29sZGFkdXJhL2xpYmVyYXJTb2xkYWR1cmEuanMnLFxuXG4gICAgICAgICAgICAgICAgLy9WaWV3cyBQVEFcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy9wcm9jZXNzZXNfdmlld3Mvc29sZGFkdXJhUFRBX3RhYmxlX3BhcnRpYWwuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy9wdGFfdmlld3MvYW5hbHlzaXMuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy9wdGFfdmlld3MvYW5hbHlzaXNfcGRmLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3MvcHRhX3ZpZXdzL3Jlc3VsdHMuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy9wdGFfdmlld3Mvc2VndW5kYV9wYXNhZGEuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy9waWVjZXNfdmlld3MvcGllY2VzUmVwb3J0L3NvbGRhZHVyYUV4dHJhSW5mb1BkZi5jc3MnLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvY3NzL3BpZWNlc192aWV3cy9waWVjZXNSZXBvcnQvc29sZGFkdXJhUFRBRXh0cmFJbmZvUGRmLmNzcycsXG5cbiAgICAgICAgICAgICAgICAvL1ZpZXdzIFJlcG9ydGUgRGlhcmlvXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3MvcmVwb3J0ZXMvZW1haWwuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy9yZXBvcnRlcy9yZWVudmlvLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3MvcmVwb3J0ZXMvZW52aW9fcHRhLmNzcycsXG5cbiAgICAgICAgICAgICAgICAvL01cdTAwRjNkdWxvIERvY3VtZW50YWNpb24gVFx1MDBFOWNuaWNhXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3Mvd29fdmlld3MvbWFuYWdlX2RpYnVqb3MuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy93b192aWV3cy9tYW5hZ2VfZnVuZGljaW9uLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3Mvd29fdmlld3MvbWFuYWdlX21hbnVhbGVzLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3Mvd29fdmlld3MvbWFuYWdlX2F5dWRhcy5jc3MnLFxuICAgICAgICAgICAgICAgICdyZXNvdXJjZXMvY3NzL3dvX3ZpZXdzL21hbmFnZV9heXVkYXNfZnVuZGljaW9uLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy93b192aWV3cy9tYW5hZ2VfZGlidWpvcy5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy93b192aWV3cy9tYW5hZ2VfZnVuZGljaW9uLmpzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL3dvX3ZpZXdzL21hbmFnZV9tYW51YWxlcy5qcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy93b192aWV3cy9tYW5hZ2VfYXl1ZGFzLmpzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL3dvX3ZpZXdzL21hbmFnZV9heXVkYXNfZnVuZGljaW9uLmpzJyxcblxuICAgICAgICAgICAgICAgIC8vVmlzdGEgQWxtYWNcdTAwRTluL0NhbGlkYWQgXHUyMDE0IERpYnVqb3MgZGUgRnVuZGljaVx1MDBGM25cbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy9hbG1hY2VuX3ZpZXdzL2FsbWFjZW5fZnVuZGljaW9uLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9hbG1hY2VuX3ZpZXdzL2FsbWFjZW5fZnVuZGljaW9uLmpzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy9hbG1hY2VuX3ZpZXdzL2NhbGlkYWRfZnVuZGljaW9uLmNzcycsXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9hbG1hY2VuX3ZpZXdzL2NhbGlkYWRfZnVuZGljaW9uLmpzJyxcblxuICAgICAgICAgICAgICAgIC8vVmlzdGEgQ2FsaWRhZCBcdTIwMTQgRGlidWpvcyB5IEF5dWRhcyBkZSBNYXF1aW5hZG9zXG4gICAgICAgICAgICAgICAgJ3Jlc291cmNlcy9jc3MvY2FsaWRhZF92aWV3cy9jYWxpZGFkX21hcXVpbmFkb3MuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL2NhbGlkYWRfdmlld3MvY2FsaWRhZF9tYXF1aW5hZG9zLmpzJyxcblxuICAgICAgICAgICAgICAgIC8vIFZpZXdzIHN5c3RlbUxvZ3NcbiAgICAgICAgICAgICAgICBcInJlc291cmNlcy9jc3MvcmVwb3J0cy9zeXN0ZW1Mb2dzLmNzc1wiLFxuICAgICAgICAgICAgICAgIFwicmVzb3VyY2VzL2pzL3JlcG9ydHMvc3lzdGVtTG9ncy5qc1wiLFxuXG4gICAgICAgICAgICAgICAgLy8gVmlzdGEgSGVycmFtaWVudGFzIFRlY2FtYWNcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2Nzcy9oZXJyYW1pZW50YXNfdmlld3MvaGVycmFtaWVudGFzX3RlY2FtYWMuY3NzJyxcbiAgICAgICAgICAgICAgICAncmVzb3VyY2VzL2pzL2hlcnJhbWllbnRhc192aWV3cy9oZXJyYW1pZW50YXNfdGVjYW1hYy5qcycsXG4gICAgICAgICAgICBdLFxuICAgICAgICAgICAgcmVmcmVzaDogdHJ1ZSxcbiAgICAgICAgfSksXG4gICAgXSxcbiAgICAvLyBidWlsZDoge1xuICAgIC8vICAgICBiMWFzZTogJ2h0dHA6Ly8xOTIuMTY4LjEuMTA2OjgwLycsXG4gICAgLy8gfSxcbn0pO1xuIl0sCiAgIm1hcHBpbmdzIjogIjtBQUEwUixTQUFTLG9CQUFvQjtBQUN2VCxPQUFPLGFBQWE7QUFFcEIsSUFBTyxzQkFBUSxhQUFhO0FBQUEsRUFDeEIsU0FBUztBQUFBLElBQ0wsUUFBUTtBQUFBLE1BQ0osT0FBTztBQUFBO0FBQUEsUUFFSDtBQUFBO0FBQUEsUUFHQTtBQUFBLFFBQ0E7QUFBQTtBQUFBLFFBR0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBO0FBQUEsUUFHQTtBQUFBLFFBQ0E7QUFBQTtBQUFBLFFBR0E7QUFBQTtBQUFBLFFBR0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBO0FBQUEsUUFHQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQTtBQUFBLFFBR0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUVBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQTtBQUFBLFFBR0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBO0FBQUEsUUFHQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBO0FBQUEsUUFHQTtBQUFBLFFBQ0E7QUFBQSxRQUVBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQTtBQUFBLFFBR0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUE7QUFBQSxRQUdBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQTtBQUFBLFFBR0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQTtBQUFBLFFBR0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBO0FBQUEsUUFHQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBO0FBQUEsUUFHQTtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQTtBQUFBO0FBQUEsUUFHQTtBQUFBLFFBQ0E7QUFBQTtBQUFBLFFBR0E7QUFBQSxRQUNBO0FBQUE7QUFBQSxRQUdBO0FBQUEsUUFDQTtBQUFBLE1BQ0o7QUFBQSxNQUNBLFNBQVM7QUFBQSxJQUNiLENBQUM7QUFBQSxFQUNMO0FBQUE7QUFBQTtBQUFBO0FBSUosQ0FBQzsiLAogICJuYW1lcyI6IFtdCn0K
