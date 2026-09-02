@if ($hasAprobadosGroup)
    <div class="cal-subcontainer-aprobados"
        style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #f0fdf4; border: 2px solid #22c55e; box-shadow: 0 3px 10px rgba(34, 197, 94, 0.08);">
        <div
            style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #bbf7d0; padding-bottom: 8px; margin-bottom: 15px;">
            <h4
                style="margin: 0; color: #15803d; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <img src="{{ asset('images/Aprobado.png') }}"
                    style="width: 22px; height: 22px; object-fit: contain; vertical-align: middle;"> Formato de Liberación
                de
                Modelo (F-CCL-LDM) &mdash; Aprobados
            </h4>
            <span
                style="font-size: 0.75rem; font-weight: 700; background: #dcfce7; color: #15803d; padding: 3px 10px; border-radius: 6px; border: 1px solid #86efac;">
                EN ORDEN / APROBADOS
            </span>
        </div>

        <div
            class="alm-pdf-grid cal-background-color-f0fdf4 cal-padding-15px cal-border-radius-8px cal-border-1px-solid-bbf7d0">
            @foreach ($calidadAprobadosLdm as $otroArchivo)
                @php
                    $canDelete = false;
                    $userPerfil = Auth::user()->perfil;
                    if (in_array($userPerfil, [1, 2, 3, 4, '1', '2', '3', '4'])) {
                        $canDelete = true;
                    }
                 @endphp
                @php $isDwg = strtolower(pathinfo($otroArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                <div class="dibujos-file-card card-otro"
                    style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #155724;">
                    <div class="file-icon-wrapper cal-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                        <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                            class="file-icon icon-default" />
                        <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                            class="file-icon icon-hover" />
                    </div>
                    <div class="file-name cal-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                        onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                        {{ basename($otroArchivo['nombre']) }}
                    </div>
                    <div class="file-actions cal-display-flex cal-gap-5px">
                        <button class="btn-dibujos btn-dibujos-sm btn-ver cal-background-color-155724 cal-color-white"
                            onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                        @if ($canDelete)
                            <button class="btn-dibujos btn-dibujos-sm btn-eliminar cal-background-color-dc3545 cal-color-white"
                                onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif