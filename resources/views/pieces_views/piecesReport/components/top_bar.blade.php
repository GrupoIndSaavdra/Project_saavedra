    @php
        $from = request('from') ?? request('origin');
        $backUrl = route('searchPieces');
        if ($from === 'home' || $from === 'inicio') {
            $backUrl = route('home');
        } elseif (isset($profile)) {
            if ($profile === 'calidad' || $profile === 'quality') {
                $backUrl = route('showReleasePieces_view');
            } elseif ($profile === 'admin') {
                $backUrl = route('searchPieces');
            }
        }
    @endphp

    <div class="cp-top-bar">
        <div class="cp-top-left">
            <a href="{{ $backUrl }}" id="btnRegresar" class="action-btn-wrapper btn-regresar"
                title="Regresar a la vista anterior">
                <img src="{{ asset('images/iconos/img-back.png') }}" class="img-action-btn btn-regresar-img"
                    alt="Regresar" />
                <span class="action-label">Regresar</span>
            </a>
        </div>

        <div class="cp-top-center">
            <div class="report-header">
                <div class="title-row">
                    <img src="{{ asset('images/vistas/Reporte_Pzas.png') }}" class="header-view-icon"
                        alt="Reporte de piezas" />
                    <h1>OT #{{ $ot }}</h1>
                    <img src="{{ asset('images/vistas/Reporte_Pzas.png') }}" class="header-view-icon"
                        alt="Reporte de piezas" />
                </div>
                <p class="subtitle">{{ $clase }} — {{ $process }}</p>
            </div>
        </div>

        <div class="cp-top-right">
            <button type="button" class="action-btn-wrapper btn-print" onclick="window.print()"
                title="Imprimir / Exportar Reporte">
                <img src="{{ asset('images/iconos/Imprimir-Icon.png') }}" class="img-action-btn btn-print-img"
                    alt="Imprimir" />
                <span class="action-label">Imprimir</span>
            </button>
            <div id="operadores-container" class="operadores-container"></div>
        </div>
    </div>

    <script>
        (function() {
            const opContainer = document.getElementById('operadores-container') || document.getElementById('container');
            if (opContainer && window.process) {
                opContainer.appendChild(window.process.crearTablaOperadores(@json($operadores)));
            }
        })();
    </script>
