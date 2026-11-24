<dialog id="priorityDocuments" class="glass-panel priority-documents-dialog no-print" aria-labelledby="priorityDocumentsTitle">
    <header>
        <h2 id="priorityDocumentsTitle">Archivos de la OT</h2>
        <button type="button" class="btns blue" data-close-documents aria-label="Cerrar archivos">Cerrar</button>
    </header>
    <p data-documents-status role="status" aria-live="polite"></p>
    <div data-documents-sections></div>
    <button type="button" class="btns blue" data-retry-documents hidden>Reintentar</button>
</dialog>
@php
    $priorityDocumentIcons = [
        'pdf' => [icon('pdf_view_shadow'), icon('pdf_view')],
        'dwg' => [icon('dwg_shadow'), icon('dwg')],
        'other' => [icon('documento'), icon('documento')],
    ];
@endphp
<script>
    window.routes = window.routes || {};
    window.routes.priorityDocuments = @json(route('master.priorities.documents', ['ot' => '__OT__']));
    window.routes.priorityDocumentsAvailability = @json(route('master.priorities.documents.availability'));
    window.priorityDocumentIcons = @json($priorityDocumentIcons);
</script>
