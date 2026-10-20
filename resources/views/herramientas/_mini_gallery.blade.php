{{--
    Partial: _mini_gallery.blade.php
    Muestra las imágenes colapsadas en un <details> nativo.
    Variables: $imgs (Collection<HerramientaImagen>)
--}}
@if($imgs->isEmpty())
    <span class="ht-no-img">—</span>
@else
    <details class="ht-gallery-details">
        <summary class="ht-gallery-summary">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 3px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            {{ $imgs->count() }} foto{{ $imgs->count() > 1 ? 's' : '' }}
        </summary>
        <div class="ht-mini-gallery">
            @foreach($imgs as $img)
                <div class="ht-mini-thumb-wrap">
                    <img class="ht-mini-thumb"
                         src="{{ asset($img->ruta) }}"
                         alt="{{ $img->nombre ?? 'Foto' }}"
                         title="{{ $img->nombre ?? 'Sin nombre' }}"
                         loading="lazy"
                         onclick="htVerImagen('{{ asset($img->ruta) }}', '{{ addslashes($img->nombre ?? '') }}')">
                    @if($img->nombre)
                        <span class="ht-mini-caption">{{ $img->nombre }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </details>
@endif
