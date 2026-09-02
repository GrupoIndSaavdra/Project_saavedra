@php
    /** @var \App\Models\FundicionHistory $reg */
    /** @var string $estado */
    /** @var string $deptName */

    include resource_path('views/calidad/partials/tables/table_row_logic.php');
@endphp


{{-- Fila principal --}}
<tr data-ot="{{ $reg->ot }}">
    <td>

        <div class="alm-ot-label">
            {{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}
        </div>
        @if ($reg->status === 'inactiva')
            <div class="alm-inactiva-note">
                La carpeta fue eliminada
                por el administrador.
                Los PDFs de {{ $deptName }} se
                conservan.

        @endif
    </td>
    <td class="d-text-center">
        <span class="badge-status badge-{{ $reg->status }}">
            {{ $reg->status }}
        </span>
    </td>
    <td class="d-text-center">
        <div id="status-modelo-{{ $reg->ot }}">
            @php
                $libStatus = $targetReg->calidad_revision_status ?? null;
                if ($libStatus === 'casting_aprobado') {
                    $icon = 'Proveedor.png';
                    $label = 'Enviado a Proveedor';
                    $tooltip =
                        'Pre-orden de casting enviada al proveedor, proceso finalizado';
                    $borderColor = '#9333ea';
                    $bgColor = '#f3e8ff';
                    $textColor = '#9333ea';
                } elseif ($targetReg->casting_pdf_generated) {
                    $icon = 'pdf-view.png';
                    $label = 'Casting';
                    $tooltip =
                        'Pre-orden de casting generada, esperando envío';
                    $borderColor = '#059669';
                    $bgColor = '#f0fdf4';
                    $textColor = '#15803d';
                } elseif (
                    in_array($libStatus, ['aprobado', 'calidad_aprobado'])
                ) {
                    $icon = 'Quality.png';
                    $label = 'Aprobado';
                    $tooltip = 'Modelo aprobado y liberado por Calidad';
                    $borderColor = '#10b981';
                    $bgColor = '#ecfdf5';
                    $textColor = '#047857';
                } elseif (
                    in_array($libStatus, ['rechazado', 'calidad_rechazado'])
                ) {
                    $icon = 'Quality.png';
                    $label = 'Rechazado';
                    $tooltip =
                        'Modelo rechazado por Calidad debido a desviaciones';
                    $borderColor = '#ef4444';
                    $bgColor = '#fef2f2';
                    $textColor = '#b91c1c';
                } elseif (
                    in_array($libStatus, ['mixto', 'calidad_mixto'])
                ) {
                    $icon = 'Quality.png';
                    $label = 'Mixto';
                    $tooltip =
                        'Liberación mixta por Calidad (clases aprobadas y rechazadas)';
                    $borderColor = '#eab308';
                    $bgColor = '#fef9c3';
                    $textColor = '#854d0e';
                } elseif (
                    in_array($libStatus, ['pendiente', 'calidad_parcial'])
                ) {
                    $icon = 'Revisando.png';
                    $label = 'En Revisión';
                    $tooltip =
                        'Calidad está realizando la revisión del modelo';
                    $borderColor = '#f59e0b';
                    $bgColor = '#fffbeb';
                    $textColor = '#b45309';
                } elseif ($targetReg->pre_orden_email_sent) {
                    if (
                        Auth::user()->perfil == 4 ||
                        Auth::user()->perfil == 3
                    ) {
                        $icon = 'Recibido.png';
                        $label = 'Nuevo';
                        $tooltip =
                            'Pre-orden de fabricación de modelo recibida, esperando revisión de Calidad';
                        $borderColor = '#cbd5e1';
                        $bgColor = '#f1f5f9';
                        $textColor = '#64748b';
                    } else {
                        $icon = 'enviando.png';
                        $label = 'Correo Enviado';
                        $tooltip =
                            'Pre-orden enviada por correo electrónico, esperando revisión de Calidad';
                        $borderColor = '#818cf8';
                        $bgColor = '#e0e7ff';
                        $textColor = '#4f46e5';
                    }
                } elseif ($targetReg->pre_orden_sent) {
                    $icon = 'pdf-view.png';
                    $label = 'Pre-Orden';
                    $tooltip =
                        'Pre-orden de modelo generada y guardada, pendiente de enviar';
                    $borderColor = '#60a5fa';
                    $bgColor = '#eff6ff';
                    $textColor = '#2563eb';
                } elseif ($targetReg->tiene_modelo) {
                    $icon = 'Espera.png';
                    $label = 'Tengo Modelo';
                    $tooltip =
                        'Modelo físico disponible en Almacén, en espera de revisión por Calidad';
                    $borderColor = '#0ea5e9';
                    $bgColor = '#f0f9ff';
                    $textColor = '#0369a1';
                } elseif ($reg->rechazos_procesados) {
                    $icon = 'Rechazado.png';
                    $label = 'Rechazado';
                    $tooltip =
                        'Retornado hacia un nuevo ciclo de modelo (Reproceso)';
                    $borderColor = '#dc2626';
                    $bgColor = '#fef2f2';
                    $textColor = '#b91c1c';
                } else {
                    $icon = 'Recibido.png';
                    $label = 'Nuevo';
                    $tooltip =
                        'Alerta inicial recibida, pendiente de procesar modelo por Almacén';
                    $borderColor = '#cbd5e1';
                    $bgColor = '#f1f5f9';
                    $textColor = '#64748b';
                }
            @endphp
            <div
                class="status-modelo-container cal-display-inline-flex cal-flex-direction-column cal-align-items-center cal-gap-2px cal-padding-6px cal-border-radius-8px">
                <span class="badge-modelo-icon" title="{{ $tooltip }}"
                    style="display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 50%; background: {{ $bgColor }}; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border: 2px solid {{ $borderColor }}; transition: all 0.2s ease;">
                    <img src="{{ asset('images/' . $icon) }}" alt="{{ $label }}"
                        class="cal-width-34px cal-height-34px cal-object-fit-contain" />
                </span>
                <span class="status-modelo-label"
                    style="font-size: 11px; font-weight: 700; color: {{ $textColor }}; margin-top: 4px; text-transform: uppercase; white-space: nowrap;">
                    {{ $label }}
                </span>
            </div>
        </div>
    </td>
    <td class="alm-date d-text-center">
        {{ $reg->alert_sent_at ? $reg->alert_sent_at->format('d/m/Y H:i') : '—' }}
    </td>
    <td class="d-text-center">
        <span class="badge-pdf-count">{{ $count }}</span>
    </td>
    <td class="d-text-center">
        @if ($hasPendingChanges)
            <button class="btn-toggle-files"
                style="background: linear-gradient(135deg, #f97316, #ea580c); color: white; border: 1px solid #c2410c;"
                onclick="almacenRevisarCambios('{{ $reg->ot }}')">
                Revisar Cambios
            </button>
        @elseif ($hasFilesOrControl)
            <button class="btn-toggle-files" data-target="files-{{ $estado }}-{{ $loop->index }}" data-ot="{{ $reg->ot }}"
                id="toggle-btn-{{ $estado }}-{{ $loop->index }}" aria-expanded="false">
                Ver Archivos
            </button>
        @else
            <span class="d-text-subtle cal-font-size-0-85em">Sin archivos</span>
        @endif
    </td>
</tr>

{{-- Fila desplegable de archivos --}}
@if ($hasFilesOrControl)
    <tr class="alm-files-row" id="files-{{ $estado }}-{{ $loop->index }}">
        <td colspan="6">

            {{-- CONTENEDOR UNIFICADO DE CONTROL DE CALIDAD Y DOCUMENTACIÓN --}}
            <div class="alm-process-block"
                style="margin-bottom: 25px; padding: 22px; border-radius: 14px; background-color: #f8fafc; border: 2px solid #033966; box-shadow: 0 4px 14px rgba(3, 57, 102, 0.08);">
                <div
                    style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #033966; padding-bottom: 10px; margin-bottom: 20px;">
                    <h3
                        style="margin: 0; color: #033966; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('images/Quality.png') }}" style="width: 24px; height: 24px;">
                        Etapa: Control de Calidad y Registro de Modelo de Fundición
                    </h3>
                    <span
                        style="font-size: 0.8rem; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 4px 12px; border-radius: 6px; border: 1px solid #bae6fd;">
                        CONTROL Y LIBERACIÓN
                    </span>
                </div>

                {{-- SUB-CONTENEDOR 1 (AZUL/NEUTRO SUPERIOR): DOCUMENTOS, DIBUJOS Y AYUDAS
                RECIBIDOS DE ALMACÉN --}}
                @include('calidad.partials.tables.subcontainers.almacen_docs')

                @include('calidad.partials.tables.subcontainers.ldm_docs')

                @include('calidad.partials.tables.subcontainers.rejection_docs')

                @include('calidad.partials.tables.subcontainers.quality_actions')


        </td>
    </tr>
@endif