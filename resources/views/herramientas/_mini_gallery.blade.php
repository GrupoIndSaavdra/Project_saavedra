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
            📷 {{ $imgs->count() }} foto{{ $imgs->count() > 1 ? 's' : '' }}
        </summary>
        <div class="ht-mini-gallery">
            @foreach($imgs as $img)
                <div class="ht-mini-thumb-wrap">
                    <img class="ht-mini-thumb"
                         src="{{ asset($img->ruta) }}"
                         alt="{{ $img->nombre ?? 'Foto' }}"
                         title="{{ $img->nombre ?? 'Sin nombre' }}"
                         onclick="htVerImagen('{{ asset($img->ruta) }}', '{{ addslashes($img->nombre ?? '') }}')">
                    @if($img->nombre)
                        <span class="ht-mini-caption">{{ $img->nombre }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </details>
@endif
