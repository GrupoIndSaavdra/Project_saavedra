@extends('layouts.appMenu')

@section('head')
    <title>Gestión de Dibujos / Planos PDF</title>
    @vite([
        'resources/css/wo_views/manage_dibujos.css',
        'resources/js/wo_views/manage_dibujos.js'
    ])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="dibujos-wrapper">

        {{-- Encabezado --}}
        <div class="dibujos-header">
            <h1>Gestión de Planos / Dibujos PDF</h1>
            <span>Directorio: DIBUJOS_GIS &nbsp;|&nbsp; Sistema de Archivos</span>
        </div>

        {{-- Panel superior: Selects OT/Clase + Subir PDF --}}
        <div class="dibujos-panel">

            {{-- Tarjeta izquierda: Selects dependientes OT → Clase --}}
            <div class="dibujos-card">
                <h2>Seleccionar / Crear Carpeta</h2>
                <p class="d-text-small d-text-muted d-mb-2">
                    Selecciona la OT y Clase existentes en el sistema. Si la carpeta no existe en el servidor, se creará
                    antes de subir el primer PDF.
                </p>

                {{-- Selector de OT --}}
                <div class="dibujos-form-group">
                    <label for="ot-select">Orden de Trabajo (OT)</label>
                    <select id="ot-select" onchange="changeDibujosOT(this.value)">
                        <option value="">— Seleccionar OT —</option>
                        @foreach($todasLasOTs as $otOpt)
                            <option value="{{ $otOpt->id }}" {{ $otSeleccionadaId == $otOpt->id ? 'selected' : '' }}>
                                OT {{ $otOpt->id }}{{ $otOpt->moldura ? ' — ' . $otOpt->moldura->nombre : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Selector de Clase (dependiente de OT) --}}
                <div class="dibujos-form-group">
                    <label for="clase-select">Clase</label>
                    <select id="clase-select" onchange="changeDibujosClase(this.value)" {{ !$otSeleccionadaId ? 'disabled' : '' }}>
                        <option value="">— Seleccionar Clase —</option>
                        @if($otSeleccionadaId && $otActiva)
                            @foreach($otActiva->clases as $claseOpt)
                                <option value="{{ $claseOpt->id }}" {{ $claseSeleccionadaId == $claseOpt->id ? 'selected' : '' }}>
                                    {{ $claseOpt->nombre }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Estado de la carpeta y botón crear --}}
                @if($otSeleccionadaId && $claseSeleccionadaId && $otActiva && $claseActiva)
                    @php
                        $otNombre = (string) $otActiva->id;
                        $otLabelFull = "OT " . $otActiva->id . ($otActiva->moldura ? " — " . $otActiva->moldura->nombre : "");
                        $claseNombre = $claseActiva->nombre;
                        $carpetaExiste = isset($estructura[$otNombre]) &&
                            in_array($claseNombre, $estructura[$otNombre]);
                    @endphp

                    @if($carpetaExiste)
                        <div class="d-alert d-alert-success d-mt-2">
                            La carpeta <strong>{{ $otLabelFull }} / {{ $claseNombre }}</strong> ya existe en el servidor.
                        </div>
                    @else
                        <div class="d-alert d-alert-warning d-mt-2">
                            La carpeta <strong>{{ $otLabelFull }} / {{ $claseNombre }}</strong> aun <strong>no existe</strong>. Creala
                            antes de subir PDFs.
                        </div>
                        <button class="btn-dibujos d-mt-2" id="btn-crear-carpeta" data-ot="{{ $otNombre }}" data-clase="{{ $claseNombre }}">
                            Crear Carpeta
                        </button>
                    @endif
                @else
                    <div class="d-alert d-alert-info d-mt-2">
                        Selecciona una OT y una Clase para continuar.
                    </div>
                @endif

                {{-- Scripts de navegación --}}
                <script>
                    function changeDibujosOT(otId) {
                        const url = new URL(window.location.href);
                        if (otId) {
                            url.searchParams.set('ot_id', otId);
                            url.searchParams.delete('clase_id');
                        } else {
                            url.searchParams.delete('ot_id');
                            url.searchParams.delete('clase_id');
                        }
                        window.location.href = url.toString();
                    }

                    function changeDibujosClase(claseId) {
                        const url = new URL(window.location.href);
                        if (claseId) {
                            url.searchParams.set('clase_id', claseId);
                            window.location.href = url.toString();
                        }
                    }
                </script>
            </div>

            {{-- Tarjeta derecha: Subir PDF --}}
            <div class="dibujos-card">
                <h2>Subir PDF</h2>

                @if($otSeleccionadaId && $claseSeleccionadaId && $otActiva && $claseActiva)
                    @php
                        $otNombreUpload = (string) $otActiva->id;
                        $claseNombreUpload = $claseActiva->nombre;
                        $carpetaExisteUpload = isset($estructura[$otNombreUpload]) &&
                            in_array($claseNombreUpload, $estructura[$otNombreUpload]);
                    @endphp
                    <p class="d-text-xs d-text-muted d-mb-2">
                        Carpeta destino: <strong class="d-text-bold" style="color:#033966;">{{ $otLabelFull }} /
                            {{ $claseNombreUpload }}</strong>
                    </p>

                    @if(!$carpetaExisteUpload)
                        <p class="d-text-xs d-text-danger d-mb-2">
                            La carpeta no existe. Primero creala en el panel de la izquierda.
                        </p>
                    @endif

                    <div class="dibujos-form-group">
                        <label class="dibujos-file-label" for="d-upload-file">
                            <span id="d-upload-file-label-text">Seleccionar archivo PDF</span>
                            <input type="file" id="d-upload-file" accept=".pdf">
                        </label>
                        <span class="dibujos-file-name" id="d-upload-file-name"></span>
                    </div>

                    <button class="btn-dibujos" id="btn-subir-pdf" data-ot="{{ $otNombreUpload }}"
                        data-clase="{{ $claseNombreUpload }}" {{ !$carpetaExisteUpload ? 'disabled' : '' }}>
                        Subir PDF
                    </button>
                @else
                    <div class="d-card-placeholder">
                        <p class="d-text-subtle">Selecciona una OT y Clase en el panel izquierdo para habilitar la subida.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Panel de archivos de la carpeta seleccionada --}}
        @if($otSeleccionadaId && $claseSeleccionadaId && $otActiva && $claseActiva)
            @php
                $otNombrePanel = (string) $otActiva->id;
                $claseNombrePanel = $claseActiva->nombre;
                $carpetaExistePanel = isset($estructura[$otNombrePanel]) &&
                    in_array($claseNombrePanel, $estructura[$otNombrePanel]);
            @endphp
            <div class="dibujos-files-panel active" id="panel-archivos">
                <h2>Archivos en: <span>{{ $otLabelFull }} / {{ $claseNombrePanel }}</span></h2>

                <div class="dibujos-files-breadcrumb">
                    Carpeta activa: <strong>{{ $otLabelFull }}</strong> /
                    <strong>{{ $claseNombrePanel }}</strong>
                </div>

                <div class="dibujos-files-grid" id="archivos-grid">
                    <p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>
                </div>
            </div>
        @endif

        {{-- Tabla global de estructura OT/Clase --}}
        <div class="dibujos-table-section">
            <h2>Estructura Actual de Carpetas en el Servidor</h2>

            @if(count($estructura) === 0)
                <div class="dibujos-empty-state">
                    <p>No hay carpetas creadas aun. Selecciona una OT y Clase arriba para comenzar.</p>
                </div>
            @else
                <div class="dibujos-table-container">
                    <table class="dibujos-table" id="tabla-estructura">
                        <thead>
                            <tr>
                                <th>Orden de Trabajo</th>
                                <th>Clase</th>
                                <th>Archivos PDF</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estructura as $otName => $clases)
                                @php
                                    // Mapear el nombre de la carpeta (ID de OT) a la OT real en la BD
                                    $otReal = $todasLasOTs->firstWhere('id', (int)$otName);
                                    $otLabel = $otReal ? ("OT " . $otReal->id . ($otReal->moldura ? " — " . $otReal->moldura->nombre : "")) : $otName;
                                    $otIdBD = $otReal ? $otReal->id : null;
                                @endphp
                                @if(count($clases) === 0)
                                    <tr>
                                        <td><strong>{{ $otLabel }}</strong></td>
                                        <td><em class="d-text-subtle">Sin clases</em></td>
                                        <td><span class="badge-count badge-count-empty">0</span></td>
                                        <td>—</td>
                                    </tr>
                                @else
                                    @foreach($clases as $claseName)
                                        @php
                                            $claseReal = $otReal ? $otReal->clases->firstWhere('nombre', $claseName) : null;
                                            $claseIdBD = $claseReal ? $claseReal->id : null;
                                        @endphp
                                        <tr data-ot="{{ $otName }}" data-clase="{{ $claseName }}">
                                            <td><strong>{{ $otLabel }}</strong></td>
                                            <td>{{ $claseName }}</td>
                                            <td>
                                                <span class="badge-count" id="badge-{{ Str::slug($otName) }}-{{ Str::slug($claseName) }}">
                                                    ...
                                                </span>
                                            </td>
                                            <td>
                                                <div class="td-actions">
                                                    @if($otIdBD && $claseIdBD)
                                                        <button class="btn-dibujos btn-dibujos-sm"
                                                            onclick="irACarpeta('{{ $otIdBD }}','{{ $claseIdBD }}')">
                                                            Ver archivos
                                                        </button>
                                                    @else
                                                        <button class="btn-dibujos btn-dibujos-sm"
                                                            onclick="irACarpetaNombre('{{ $otName }}','{{ $claseName }}')">
                                                            Ver archivos
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Log de auditoria --}}
        <div class="dibujos-table-section">
            <h2>Registro de Auditoria (ultimas acciones)</h2>
            <div class="dibujos-table-container d-log-scroll">
                <table class="dibujos-log-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Accion</th>
                            <th>Ruta</th>
                            <th>Archivo</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-log">
                        <tr>
                            <td colspan="5" class="d-text-center d-text-subtle" style="padding:1em;">Cargando registro...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        window.baseUrl = "{{ url('/') }}";
        window.cerrarImgUrl = "{{ asset('images/cerrar.png') }}";
        window.estructuraInicial = @json($estructura);
        window.otSeleccionadaId = @json($otSeleccionadaId);
        window.claseSeleccionadaId = @json($claseSeleccionadaId);
        window.otNombreActivo = @json($otActiva?->id ?? null);
        window.claseNombreActivo = @json($claseActiva?->nombre ?? null);
        window.routes = {
            ...(window.routes || {}),
            'dibujos.estructura': @json(route('dibujos.estructura')),
            'dibujos.archivos': @json(route('dibujos.archivos')),
            'dibujos.serve': @json(route('dibujos.serve')),
            'dibujos.createFolder': @json(route('dibujos.createFolder')),
            'dibujos.upload': @json(route('dibujos.upload')),
            'dibujos.delete': @json(route('dibujos.delete')),
            'dibujos.replace': @json(route('dibujos.replace'))
        };
        window.csrfToken = "{{ csrf_token() }}";
    </script>
@endsection
