@props(['paginator', 'label' => 'registros', 'mode' => 'classic', 'target' => null])

@if ($paginator && ($paginator->hasMorePages() || $paginator->previousPageUrl() || $paginator->count() > 0))
    @if ($mode === 'load-more')
        @if ($paginator->hasMorePages())
            <div class="gis-cursor-loadmore-wrap" data-cursor-pagination="load-more">
                <button type="button" 
                        class="gis-pagination-btn gis-pagination-loadmore" 
                        data-next-url="{{ $paginator->nextPageUrl() }}" 
                        data-target="{{ $target }}"
                        title="Cargar más {{ $label }}">
                    <span class="gis-loadmore-text">Cargar más {{ $label }}</span>
                    <svg class="gis-loadmore-spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0 1 10 10"></path>
                    </svg>
                </button>
            </div>
        @endif
    @else
        <nav class="gis-cursor-pagination" aria-label="Navegación de {{ $label }}">
            {{-- Botón Anterior --}}
            @if ($paginator->previousPageUrl())
                <a href="{{ $paginator->previousPageUrl() }}" class="gis-pagination-btn gis-pagination-prev" rel="prev" title="Página anterior">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Anterior</span>
                </a>
            @else
                <span class="gis-pagination-btn gis-pagination-disabled" aria-disabled="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Anterior</span>
                </span>
            @endif

            {{-- Información Central de la Página Actual --}}
            <div class="gis-pagination-info">
                <span class="gis-pagination-badge">
                    Mostrando {{ $paginator->count() }} {{ $label }}
                </span>
            </div>

            {{-- Botón Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="gis-pagination-btn gis-pagination-next" rel="next" title="Página siguiente">
                    <span>Siguiente</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            @else
                <span class="gis-pagination-btn gis-pagination-disabled" aria-disabled="true">
                    <span>Siguiente</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </span>
            @endif
        </nav>
    @endif
@endif
