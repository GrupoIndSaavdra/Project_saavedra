    @if ($process == 'Soldadura' || $process == 'Soldadura PTA' || $process == 'Rectificado' || $process == 'Palomas')
        <style>
            .tabla3 {
                width: 130%;
            }
        </style>
    @endif
    @if ($process == 'Copiado')
        <style>
            .scrollabe-table {
                height: 500px;
            }
        </style>
    @endif

        @csrf
        <div class="scrollabe-table" id="scrollabe-table">
            @if ($process == 'Asentado')
                <table border="1" class="tabla3 cp-width-100pct">
                    <tr>
                        <th class="t-title cp-width-150px">#PZ</th>
                        <th class="t-title">Sin juego</th>
                        <th class="t-title">Sin luz</th>
                        <th class="t-title">Error</th>
                        <th class="t-title cp-width-700px">Observaciones</th>
                    </tr>
                    @foreach ($piecesInfo as $piece)
                        <tr>
                            <td><input type="text" class="input"
                                    value="{{ $piece->n_juego ?? ($piece['n_juego'] ?? '') }}" disabled></td>
                            <td><input type="text" class="input"
                                    value="{{ $piece->sin_juego ?? ($piece['sin_juego'] ?? '') }}" disabled></td>
                            <td><input type="text" class="input"
                                    value="{{ $piece->sin_luz ?? ($piece['sin_luz'] ?? '') }}" disabled></td>
                            <td><input type="text" class="input"
                                    value="{{ $piece->error ?? ($piece['error'] ?? '') }}" disabled /></td>
                            <td><input type="text" class="input"
                                    value="{{ $piece->observaciones ?? ($piece['observaciones'] ?? '') }}" disabled />
                            </td>
                        </tr>
                    @endforeach
                </table>
            @elseif ($process == 'Cavidades')
                <table class="tabla3">
                    <tr>
                        <th class="t-title cp-width-150px">#PZ</th>
                        <th class="t-title" colspan="2">
                            Altura 1
                            @if (isset($cNominal->altura1) && !isset($cNominal->profundidad1))
                                <div class="cp-margin-top-6px">
                                    <span
                                        class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                        <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                        {{ $cNominal->altura1 }} mm
                                    </span>
                                </div>
                            @elseif (isset($cNominal->profundidad1))
                                <div class="cp-margin-top-6px">
                                    <span
                                        class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                        <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                        {{ $cNominal->profundidad1 }} mm
                                    </span>
                                </div>
                            @endif
                        </th>
                        <th class="t-title" colspan="2">
                            Altura 2
                            @if (isset($cNominal->altura2) && !isset($cNominal->profundidad1))
                                <div class="cp-margin-top-6px">
                                    <span
                                        class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                        <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                        {{ $cNominal->altura2 }} mm
                                    </span>
                                </div>
                            @elseif (isset($cNominal->profundidad2))
                                <div class="cp-margin-top-6px">
                                    <span
                                        class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                        <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                        {{ $cNominal->profundidad2 }} mm
                                    </span>
                                </div>
                            @endif
                        </th>
                        <th class="t-title" colspan="2">
                            Altura 3
                            @if (isset($cNominal->altura3) && !isset($cNominal->profundidad1))
                                <div class="cp-margin-top-6px">
                                    <span
                                        class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                        <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                        {{ $cNominal->altura3 }} mm
                                    </span>
                                </div>
                            @elseif (isset($cNominal->profundidad3))
                                <div class="cp-margin-top-6px">
                                    <span
                                        class="cp-display-inline-flex cp-align-items-center cp-padding-2px-12px cp-background-f1f3f5 cp-border-radius-50px cp-font-size-11px cp-color-495057 cp-border-1px-solid-dee2e6 cp-font-weight-500">
                                        <strong class="cp-color-007bff cp-margin-right-5px">REF</strong>
                                        {{ $cNominal->profundidad3 }} mm
                                    </span>
                                </div>
                            @endif
                        </th>
                        <th class="t-title"></th>
                        <th class="t-title" colspan="2"></th>
                    </tr>
                    <tr>
                        <th class="t-title"></th>
                        @if (isset($cNominal->altura1) && !isset($cNominal->profundidad1))
                            <th colspan="2">Altura</th>
                            <th colspan="2">Altura</th>
                            <th colspan="2">Altura</th>
                        @else
                            <th>Profundidad</th>
                            <th>Diametro</th>
                            <th>Profundidad</th>
                            <th>Diametro</th>
                            <th>Profundidad</th>
                            <th>Diametro</th>
                        @endif
                        <th>Acetato B/M</th>
                        <th>Error</th>
                        <th class="cp-width-1000px">Observaciones</th>
                    </tr>
                    <tr>
                        <td>C.Nominal</td>
                        @if (isset($cNominal->altura1) && !isset($cNominal->profundidad1))
                            <td colspan="2"><input type="number" value="{{ $cNominal->altura1 }}" class="input"
                                    step="any" inputmode="decimal" disabled></td>
                            <td colspan="2"><input type="number" value="{{ $cNominal->altura2 }}" class="input"
                                    step="any" inputmode="decimal" disabled></td>
                            <td colspan="2"><input type="number" value="{{ $cNominal->altura3 }}" class="input"
                                    step="any" inputmode="decimal" disabled></td>
                        @else
                            <td><input type="number" value="{{ $cNominal->profundidad1 }}" class="input"
                                    step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" value="{{ $cNominal->diametro1 }}" class="input" step="any"
                                    inputmode="decimal" disabled></td>
                            <td><input type="number" value="{{ $cNominal->profundidad2 }}" class="input"
                                    step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" value="{{ $cNominal->diametro2 }}" class="input" step="any"
                                    inputmode="decimal" disabled></td>
                            <td><input type="number" value="{{ $cNominal->profundidad3 }}" class="input"
                                    step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" value="{{ $cNominal->diametro3 }}" class="input" step="any"
                                    inputmode="decimal" disabled></td>
                        @endif
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                    </tr>
                    <tr>
                        <td> Tolerancias </td>
                        @if (isset($cNominal->altura1) && !isset($cNominal->profundidad1))
                            <td colspan="2"><input type="text"
                                    value="{{ isset($tolerance->altura1) && $tolerance->altura1 !== '' ? '± ' . $tolerance->altura1 : '± ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled></td>
                            <td colspan="2"><input type="text"
                                    value="{{ isset($tolerance->altura2) && $tolerance->altura2 !== '' ? '± ' . $tolerance->altura2 : '± ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled></td>
                            <td colspan="2"><input type="text"
                                    value="{{ isset($tolerance->altura3) && $tolerance->altura3 !== '' ? '± ' . $tolerance->altura3 : '± ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled></td>
                        @else
                            <td><input type="text"
                                    value="{{ isset($tolerance->profundidad1_1) && $tolerance->profundidad1_1 !== '' ? '+ ' . $tolerance->profundidad1_1 : '+ ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled><input
                                    type="text"
                                    value="{{ isset($tolerance->profundidad2_1) && $tolerance->profundidad2_1 !== '' ? '- ' . $tolerance->profundidad2_1 : '- ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text"
                                    value="{{ isset($tolerance->diametro1_1) && $tolerance->diametro1_1 !== '' ? '+ ' . $tolerance->diametro1_1 : '+ ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled><input
                                    type="text"
                                    value="{{ isset($tolerance->diametro2_1) && $tolerance->diametro2_1 !== '' ? '- ' . $tolerance->diametro2_1 : '- ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text"
                                    value="{{ isset($tolerance->profundidad1_2) && $tolerance->profundidad1_2 !== '' ? '+ ' . $tolerance->profundidad1_2 : '+ ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled><input
                                    type="text"
                                    value="{{ isset($tolerance->profundidad2_2) && $tolerance->profundidad2_2 !== '' ? '- ' . $tolerance->profundidad2_2 : '- ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text"
                                    value="{{ isset($tolerance->diametro1_2) && $tolerance->diametro1_2 !== '' ? '+ ' . $tolerance->diametro1_2 : '+ ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled><input
                                    type="text"
                                    value="{{ isset($tolerance->diametro2_2) && $tolerance->diametro2_2 !== '' ? '- ' . $tolerance->diametro2_2 : '- ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text"
                                    value="{{ isset($tolerance->profundidad1_3) && $tolerance->profundidad1_3 !== '' ? '+ ' . $tolerance->profundidad1_3 : '+ ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled><input
                                    type="text"
                                    value="{{ isset($tolerance->profundidad2_3) && $tolerance->profundidad2_3 !== '' ? '- ' . $tolerance->profundidad2_3 : '- ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text"
                                    value="{{ isset($tolerance->diametro1_3) && $tolerance->diametro1_3 !== '' ? '+ ' . $tolerance->diametro1_3 : '+ ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled><input
                                    type="text"
                                    value="{{ isset($tolerance->diametro2_3) && $tolerance->diametro2_3 !== '' ? '- ' . $tolerance->diametro2_3 : '- ' }}"
                                    class="input-medio" step="any" inputmode="decimal" disabled></td>
                        @endif
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                    </tr>
                    @foreach ($piecesInfo as $pieceInfo)
                        <tr>
                            <td><input type="text" class="input" value="{{ $pieceInfo['n_pieza'] }}" disabled>
                            </td>
                            @if (isset($cNominal->altura1) && !isset($cNominal->profundidad1))
                                @php
                                    // Altura1 validation (symmetric tolerance)
                                    $altura1_error = false;
                                    if (isset($cNominal->altura1) && isset($tolerance->altura1)) {
                                        $upper = $cNominal->altura1 + $tolerance->altura1;
                                        $lower = $cNominal->altura1 - $tolerance->altura1;
                                        $altura1_error =
                                            ($pieceInfo['altura1'] ?? 0) < $lower ||
                                            ($pieceInfo['altura1'] ?? 0) > $upper;
                                    }

                                    // Altura2 validation (symmetric tolerance)
                                    $altura2_error = false;
                                    if (isset($cNominal->altura2) && isset($tolerance->altura2)) {
                                        $upper = $cNominal->altura2 + $tolerance->altura2;
                                        $lower = $cNominal->altura2 - $tolerance->altura2;
                                        $altura2_error =
                                            ($pieceInfo['altura2'] ?? 0) < $lower ||
                                            ($pieceInfo['altura2'] ?? 0) > $upper;
                                    }

                                    // Altura3 validation (symmetric tolerance)
                                    $altura3_error = false;
                                    if (isset($cNominal->altura3) && isset($tolerance->altura3)) {
                                        $upper = $cNominal->altura3 + $tolerance->altura3;
                                        $lower = $cNominal->altura3 - $tolerance->altura3;
                                        $altura3_error =
                                            ($pieceInfo['altura3'] ?? 0) < $lower ||
                                            ($pieceInfo['altura3'] ?? 0) > $upper;
                                    }
                                @endphp
                                <td colspan="2"><input type="number" class="input"
                                        value="{{ $pieceInfo['altura1'] ?? '' }}" step="any" inputmode="decimal"
                                        style="{{ $altura1_error ? 'border: 3px solid red;' : '' }}" disabled></td>
                                <td colspan="2"><input type="number" class="input"
                                        value="{{ $pieceInfo['altura2'] ?? '' }}" step="any" inputmode="decimal"
                                        style="{{ $altura2_error ? 'border: 3px solid red;' : '' }}" disabled></td>
                                <td colspan="2"><input type="number" class="input"
                                        value="{{ $pieceInfo['altura3'] ?? '' }}" step="any" inputmode="decimal"
                                        style="{{ $altura3_error ? 'border: 3px solid red;' : '' }}" disabled></td>
                            @else
                                @php
                                    // Profundidad1 validation
                                    $prof1_error = false;
                                    if (
                                        isset($cNominal->profundidad1) &&
                                        isset($tolerance->profundidad1_1) &&
                                        isset($tolerance->profundidad2_1)
                                    ) {
                                        $upper = $cNominal->profundidad1 + $tolerance->profundidad1_1;
                                        $lower = $cNominal->profundidad1 - $tolerance->profundidad2_1;
                                        $prof1_error =
                                            $pieceInfo['profundidad1'] < $lower || $pieceInfo['profundidad1'] > $upper;
                                    }

                                    // Diametro1 validation
                                    $diam1_error = false;
                                    if (
                                        isset($cNominal->diametro1) &&
                                        isset($tolerance->diametro1_1) &&
                                        isset($tolerance->diametro2_1)
                                    ) {
                                        $upper = $cNominal->diametro1 + $tolerance->diametro1_1;
                                        $lower = $cNominal->diametro1 - $tolerance->diametro2_1;
                                        $diam1_error =
                                            $pieceInfo['diametro1'] < $lower || $pieceInfo['diametro1'] > $upper;
                                    }

                                    // Profundidad2 validation
                                    $prof2_error = false;
                                    if (
                                        isset($cNominal->profundidad2) &&
                                        isset($tolerance->profundidad1_2) &&
                                        isset($tolerance->profundidad2_2)
                                    ) {
                                        $upper = $cNominal->profundidad2 + $tolerance->profundidad1_2;
                                        $lower = $cNominal->profundidad2 - $tolerance->profundidad2_2;
                                        $prof2_error =
                                            $pieceInfo['profundidad2'] < $lower || $pieceInfo['profundidad2'] > $upper;
                                    }

                                    // Diametro2 validation
                                    $diam2_error = false;
                                    if (
                                        isset($cNominal->diametro2) &&
                                        isset($tolerance->diametro1_2) &&
                                        isset($tolerance->diametro2_2)
                                    ) {
                                        $upper = $cNominal->diametro2 + $tolerance->diametro1_2;
                                        $lower = $cNominal->diametro2 - $tolerance->diametro2_2;
                                        $diam2_error =
                                            $pieceInfo['diametro2'] < $lower || $pieceInfo['diametro2'] > $upper;
                                    }

                                    // Profundidad3 validation
                                    $prof3_error = false;
                                    if (
                                        isset($cNominal->profundidad3) &&
                                        isset($tolerance->profundidad1_3) &&
                                        isset($tolerance->profundidad2_3)
                                    ) {
                                        $upper = $cNominal->profundidad3 + $tolerance->profundidad1_3;
                                        $lower = $cNominal->profundidad3 - $tolerance->profundidad2_3;
                                        $prof3_error =
                                            $pieceInfo['profundidad3'] < $lower || $pieceInfo['profundidad3'] > $upper;
                                    }

                                    // Diametro3 validation
                                    $diam3_error = false;
                                    if (
                                        isset($cNominal->diametro3) &&
                                        isset($tolerance->diametro1_3) &&
                                        isset($tolerance->diametro2_3)
                                    ) {
                                        $upper = $cNominal->diametro3 + $tolerance->diametro1_3;
                                        $lower = $cNominal->diametro3 - $tolerance->diametro2_3;
                                        $diam3_error =
                                            $pieceInfo['diametro3'] < $lower || $pieceInfo['diametro3'] > $upper;
                                    }
                                @endphp
                                <td><input type="number" class="input"
                                        style="{{ $prof1_error ? 'border: 3px solid red;' : '' }}"
                                        value="{{ $pieceInfo['profundidad1'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                                <td><input type="number" class="input"
                                        style="{{ $diam1_error ? 'border: 3px solid red;' : '' }}"
                                        value="{{ $pieceInfo['diametro1'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                                <td><input type="number" class="input"
                                        style="{{ $prof2_error ? 'border: 3px solid red;' : '' }}"
                                        value="{{ $pieceInfo['profundidad2'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                                <td><input type="number" class="input"
                                        style="{{ $diam2_error ? 'border: 3px solid red;' : '' }}"
                                        value="{{ $pieceInfo['diametro2'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                                <td><input type="number" class="input"
                                        style="{{ $prof3_error ? 'border: 3px solid red;' : '' }}"
                                        value="{{ $pieceInfo['profundidad3'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                                <td><input type="number" class="input"
                                        style="{{ $diam3_error ? 'border: 3px solid red;' : '' }}"
                                        value="{{ $pieceInfo['diametro3'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                            @endif
                            <td><input type="text" class="input" value="{{ $pieceInfo['acetatoBM'] }}" disabled>
                            </td>
                            <td><input type="text" class="input" value="{{ $pieceInfo['error'] }}" disabled>
                            </td>
                            <td><input type="text" class="input" value="{{ $pieceInfo['observaciones'] }}" disabled>
                            </td>
                        </tr>
                    @endforeach
                </table>
            @elseif ($process == 'Copiado')
                <table border="1" class="tabla3">
                    <label class="title-subproceso"> C I L I N D R A D O</label>
                    <tr>
                        <th class="t-title cp-width-150px">#PZ</th>
                        <th class="t-title">Diametro 1</th>
                        <th class="t-title">Profundidad 1</th>
                        <th class="t-title">Diametro 2</th>
                        <th class="t-title">Profundidad 2</th>
                        <th class="t-title">Diametro de sufridera</th>
                        <th class="t-title">Diametro de ranura</th>
                        <th class="t-title">Profundidad de ranura</th>
                        <th class="t-title">Profundidad de sufridera</th>
                        <th class="t-title">Altura total</th>
                        <th class="t-title cp-width-200px">Error</th><br>
                        <th class="t-title cp-width-700px">Observaciones</th>
                    </tr>
                    <tr>
                        <td>C.Nominal.</td>
                        <td><input type="number" class="input" value="{{ $cNominal->diametro1_cilindrado }}" disabled>
                        </td>
                        <td><input type="number" class="input" value="{{ $cNominal->profundidad1_cilindrado }}"
                                disabled>
                        </td>
                        <td><input type="number" class="input" value="{{ $cNominal->diametro2_cilindrado }}" disabled>
                        </td>
                        <td><input type="number" class="input" value="{{ $cNominal->profundidad2_cilindrado }}"
                                disabled>
                        </td>
                        <td><input type="number" class="input" value="{{ $cNominal->diametro_sufridera }}" disabled>
                        </td>
                        <td><input type="number" class="input" value="{{ $cNominal->diametro_ranura }}" disabled>
                        </td>
                        <td><input type="number" class="input" value="{{ $cNominal->profundidad_ranura }}" disabled>
                        </td>
                        <td><input type="number" class="input" value="{{ $cNominal->profundidad_sufridera }}"
                                disabled></td>
                        <td><input type="number" class="input" value="{{ $cNominal->altura_total }}" disabled>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td> Tolerancias. </td>
                        <td><input type="text" class="input"
                                value="{{ isset($tolerance->diametro1_cilindrado) && $tolerance->diametro1_cilindrado !== '' ? '± ' . $tolerance->diametro1_cilindrado : '± ' }}"
                                disabled></td>
                        <td><input type="text" class="input"
                                value="{{ isset($tolerance->profundidad1_cilindrado) && $tolerance->profundidad1_cilindrado !== '' ? '± ' . $tolerance->profundidad1_cilindrado : '± ' }}"
                                disabled>
                        </td>
                        <td><input type="text" class="input"
                                value="{{ isset($tolerance->diametro2_cilindrado) && $tolerance->diametro2_cilindrado !== '' ? '± ' . $tolerance->diametro2_cilindrado : '± ' }}"
                                disabled></td>
                        <td><input type="text" class="input"
                                value="{{ isset($tolerance->profundidad2_cilindrado) && $tolerance->profundidad2_cilindrado !== '' ? '± ' . $tolerance->profundidad2_cilindrado : '± ' }}"
                                disabled>
                        </td>
                        <td><input type="text" class="input"
                                value="{{ isset($tolerance->diametro_sufridera) && $tolerance->diametro_sufridera !== '' ? '± ' . $tolerance->diametro_sufridera : '± ' }}"
                                disabled></td>
                        <td><input type="text" class="input"
                                value="{{ isset($tolerance->diametro_ranura) && $tolerance->diametro_ranura !== '' ? '± ' . $tolerance->diametro_ranura : '± ' }}"
                                disabled>
                        </td>
                        <td><input type="text" class="input"
                                value="{{ isset($tolerance->profundidad_ranura) && $tolerance->profundidad_ranura !== '' ? '± ' . $tolerance->profundidad_ranura : '± ' }}"
                                disabled></td>
                        <td><input type="text" class="input"
                                value="{{ isset($tolerance->profundidad_sufridera) && $tolerance->profundidad_sufridera !== '' ? '± ' . $tolerance->profundidad_sufridera : '± ' }}"
                                disabled>
                        </td>
                        <td><input type="text" class="input"
                                value="{{ isset($tolerance->altura_total) && $tolerance->altura_total !== '' ? '± ' . $tolerance->altura_total : '± ' }}"
                                disabled>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    @foreach ($piecesInfo as $pieceInfo)
                        <tr>
                            <td><input type="text" class="input" value="{{ $pieceInfo['n_pieza'] }}"
                                    step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" class="input" value="{{ $pieceInfo['diametro1_cilindrado'] }}"
                                    step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" class="input"
                                    value="{{ $pieceInfo['profundidad1_cilindrado'] }}" step="any"
                                    inputmode="decimal" disabled></td>
                            <td><input type="number" class="input" value="{{ $pieceInfo['diametro2_cilindrado'] }}"
                                    step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" class="input"
                                    value="{{ $pieceInfo['profundidad2_cilindrado'] }}" step="any"
                                    inputmode="decimal" disabled></td>
                            <td><input type="number" class="input" value="{{ $pieceInfo['diametro_sufridera'] }}"
                                    step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" class="input" value="{{ $pieceInfo['diametro_ranura'] }}"
                                    step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" class="input" value="{{ $pieceInfo['profundidad_ranura'] }}"
                                    step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" class="input" value="{{ $pieceInfo['profundidad_sufridera'] }}"
                                    step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" class="input" value="{{ $pieceInfo['altura_total'] }}"
                                    step="any" inputmode="decimal" disabled></td>
                            <td><input type="text" class="input" value="{{ $pieceInfo['error_cilindrado'] }}"
                                    disabled></td>
                            <td><input type="text" class="input"
                                    value="{{ $pieceInfo['observaciones_cilindrado'] }}" disabled>
                            </td>
                        </tr>
                    @endforeach
                </table>
            @elseif($process == 'Off Set')
                <table border="1" class="tabla3">
                    <>
                        <tr>
                            <th class="t-title cp-width-150px">#PZ</th>
                            <th class="t-title cp-width-200px cp-border-bottom-none">Ancho de
                                altura</th>
                            <th class="t-title" colspan="2">Profundidad de tacon</th>
                            <th class="t-title" colspan="2">Simetría</th>
                            <th class="t-title cp-width-200px cp-border-bottom-none">Ancho del
                                tacon</th>
                            <th class="t-title" colspan="2">Barreno Lateral</th>
                            <th class="t-title cp-width-200px cp-border-bottom-none">Altura tacon
                                inicial</th>
                            <th class="t-title cp-width-200px cp-border-bottom-none">Altura tacon
                                intermedia</th>
                            <th class="t-title cp-width-200px cp-border-bottom-none">Error</th>
                            <th class="t-title cp-width-700px cp-border-bottom-none">Observaciones</th>
                        </tr>
                        <tr>
                            <th class="t-title"></th>
                            <th class="cp-border-bottom-none cp-border-top-none"></th>
                            <th>Hembra</th>
                            <th>Macho</th>
                            <th>Hembra</th>
                            <th>Macho</th>
                            <th class="cp-border-bottom-none cp-border-top-none"></th>
                            <th>Hembra</th>
                            <th>Macho</th>
                            <th class="cp-border-bottom-none cp-border-top-none"></th>
                            <th class="cp-border-bottom-none cp-border-top-none"></th>
                            <th class="cp-border-bottom-none cp-border-top-none"></th>
                            <th class="cp-border-bottom-none cp-border-top-none"></th>
                        </tr>
                        <tr>
                            <td>C.Nominal</td>
                            <td><input type="number" name="cNomi_anchoRanura" value="{{ $cNominal->anchoRanura }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" name="cNomi_profuTaconHembra"
                                    value="{{ $cNominal->profuTaconHembra }}" class="input" step="any"
                                    inputmode="decimal" disabled></td>
                            <td><input type="number" name="cNomi_profuTaconMacho"
                                    value="{{ $cNominal->profuTaconMacho }}" class="input" step="any"
                                    inputmode="decimal" disabled></td>
                            <td><input type="number" name="cNomi_simetriaHembra"
                                    value="{{ $cNominal->simetriaHembra }}" class="input" step="any"
                                    inputmode="decimal" disabled></td>
                            <td><input type="number" name="cNomi_simetriaMacho" value="{{ $cNominal->simetriaMacho }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" name="cNomi_anchoTacon" value="{{ $cNominal->anchoTacon }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" name="cNomi_barrenoLateralHembra"
                                    value="{{ $cNominal->barrenoLateralHembra }}" class="input" step="any"
                                    inputmode="decimal" disabled></td>
                            <td><input type="number" name="cNomi_barrenoLateralMacho"
                                    value="{{ $cNominal->barrenoLateralMacho }}" class="input" step="any"
                                    inputmode="decimal" disabled></td>
                            <td><input type="number" name="cNomi_alturaTaconInicial"
                                    value="{{ isset($cNominal->alturaTaconInicial) && $cNominal->alturaTaconInicial !== '' ? number_format($cNominal->alturaTaconInicial, 3) : '' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" name="cNomi_alturaTaconIntermedia"
                                    value="{{ isset($cNominal->alturaTaconIntermedia) && $cNominal->alturaTaconIntermedia !== '' ? number_format($cNominal->alturaTaconIntermedia, 3) : '' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="number" class="input" disabled></td>
                            <td><input type="number" class="input" disabled></td>
                        </tr>
                        <tr>
                            <td> Tolerancias </td>
                            <td><input type="text" name="tole_anchoRanura"
                                    value="{{ isset($tolerance->anchoRanura) && $tolerance->anchoRanura !== '' ? '± ' . $tolerance->anchoRanura : '± ' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text" name="tole_profuTaconHembra"
                                    value="{{ isset($tolerance->profuTaconHembra) && $tolerance->profuTaconHembra !== '' ? '± ' . $tolerance->profuTaconHembra : '± ' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text" name="tole_profuTaconMacho"
                                    value="{{ isset($tolerance->profuTaconMacho) && $tolerance->profuTaconMacho !== '' ? '± ' . $tolerance->profuTaconMacho : '± ' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text" name="tole_simetriaHembra"
                                    value="{{ isset($tolerance->simetriaHembra) && $tolerance->simetriaHembra !== '' ? '± ' . $tolerance->simetriaHembra : '± ' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text" name="tole_simetriaMacho"
                                    value="{{ isset($tolerance->simetriaMacho) && $tolerance->simetriaMacho !== '' ? '± ' . $tolerance->simetriaMacho : '± ' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text" name="tole_anchoTacon"
                                    value="{{ isset($tolerance->anchoTacon) && $tolerance->anchoTacon !== '' ? '± ' . $tolerance->anchoTacon : '± ' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text" name="tole_barrenoLateralHembra"
                                    value="{{ isset($tolerance->barrenoLateralHembra) && $tolerance->barrenoLateralHembra !== '' ? '± ' . $tolerance->barrenoLateralHembra : '± ' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text" name="tole_barrenoLateralMacho"
                                    value="{{ isset($tolerance->barrenoLateralMacho) && $tolerance->barrenoLateralMacho !== '' ? '± ' . $tolerance->barrenoLateralMacho : '± ' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text" name="tole_alturaTaconInicial"
                                    value="{{ isset($tolerance->alturaTaconInicial) && $tolerance->alturaTaconInicial !== '' ? '± ' . number_format($tolerance->alturaTaconInicial, 3) : '± ' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text" name="tole_alturaTaconIntermedia"
                                    value="{{ isset($tolerance->alturaTaconIntermedia) && $tolerance->alturaTaconIntermedia !== '' ? '± ' . number_format($tolerance->alturaTaconIntermedia, 3) : '± ' }}"
                                    class="input" step="any" inputmode="decimal" disabled></td>
                            <td><input type="text" class="input" disabled></td>
                            <td><input type="text" class="input" disabled></td>
                        </tr>
                        @foreach ($piecesInfo as $pieceInfo)
                            @php
                                $fields = [
                                    'anchoRanura',
                                    'profuTaconHembra',
                                    'profuTaconMacho',
                                    'simetriaHembra',
                                    'simetriaMacho',
                                    'anchoTacon',
                                    'barrenoLateralHembra',
                                    'barrenoLateralMacho',
                                    'alturaTaconInicial',
                                    'alturaTaconIntermedia',
                                ];
                                $errorsInfo = [];
                                foreach ($fields as $field) {
                                    $errorsInfo[$field] = false;
                                    if (
                                        isset($cNominal->$field) &&
                                        isset($tolerance->$field) &&
                                        is_numeric($cNominal->$field) &&
                                        is_numeric($tolerance->$field) &&
                                        isset($pieceInfo[$field]) &&
                                        is_numeric($pieceInfo[$field])
                                    ) {
                                        $upper = $cNominal->$field + $tolerance->$field;
                                        $lower = $cNominal->$field - $tolerance->$field;
                                        $errorsInfo[$field] =
                                            $pieceInfo[$field] < $lower || $pieceInfo[$field] > $upper;
                                    }
                                }
                            @endphp
                            <tr>
                                <td><input type="text" class="input" value="{{ $pieceInfo['n_pieza'] }}" disabled>
                                </td>
                                <td><input type="number"
                                        class="input {{ !empty($errorsInfo['anchoRanura']) ? 'input-error cp-border-3px-solid-red' : '' }}"
                                        value="{{ $pieceInfo['anchoRanura'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                                <td><input type="number"
                                        class="input {{ !empty($errorsInfo['profuTaconHembra']) ? 'input-error cp-border-3px-solid-red' : '' }}"
                                        value="{{ $pieceInfo['profuTaconHembra'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                                <td><input type="number"
                                        class="input {{ !empty($errorsInfo['profuTaconMacho']) ? 'input-error cp-border-3px-solid-red' : '' }}"
                                        value="{{ $pieceInfo['profuTaconMacho'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                                <td><input type="number"
                                        class="input {{ !empty($errorsInfo['simetriaHembra']) ? 'input-error cp-border-3px-solid-red' : '' }}"
                                        value="{{ $pieceInfo['simetriaHembra'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                                <td><input type="number"
                                        class="input {{ !empty($errorsInfo['simetriaMacho']) ? 'input-error cp-border-3px-solid-red' : '' }}"
                                        value="{{ $pieceInfo['simetriaMacho'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                                <td><input type="number"
                                        class="input {{ !empty($errorsInfo['anchoTacon']) ? 'input-error cp-border-3px-solid-red' : '' }}"
                                        value="{{ $pieceInfo['anchoTacon'] }}" step="any" inputmode="decimal"
                                        disabled></td>
                                <td><input type="number"
                                        class="input {{ !empty($errorsInfo['barrenoLateralHembra']) ? 'input-error cp-border-3px-solid-red' : '' }}"
                                        value="{{ $pieceInfo['barrenoLateralHembra'] }}" step="any"
                                        inputmode="decimal" disabled></td>
                                <td><input type="number"
                                        class="input {{ !empty($errorsInfo['barrenoLateralMacho']) ? 'input-error cp-border-3px-solid-red' : '' }}"
                                        value="{{ $pieceInfo['barrenoLateralMacho'] }}" step="any"
                                        inputmode="decimal" disabled>
                                </td>
                                <td><input type="number"
                                        class="input {{ !empty($errorsInfo['alturaTaconInicial']) ? 'input-error cp-border-3px-solid-red' : '' }}"
                                        value="{{ isset($pieceInfo['alturaTaconInicial']) && $pieceInfo['alturaTaconInicial'] !== '' ? number_format($pieceInfo['alturaTaconInicial'], 3) : '' }}"
                                        step="any" inputmode="decimal" disabled></td>
                                <td><input type="number"
                                        class="input {{ !empty($errorsInfo['alturaTaconIntermedia']) ? 'input-error cp-border-3px-solid-red' : '' }}"
                                        value="{{ isset($pieceInfo['alturaTaconIntermedia']) && $pieceInfo['alturaTaconIntermedia'] !== '' ? number_format($pieceInfo['alturaTaconIntermedia'], 3) : '' }}"
                                        step="any" inputmode="decimal" disabled></td>
                                <td><input type="text" class="input" value="{{ $pieceInfo['error'] }}" disabled>
                                </td>
                                <td><input type="text" class="input" value="{{ $pieceInfo['observaciones'] }}"
                                        disabled></td>
                            </tr>
                        @endforeach
                </table>
            @elseif ($process == 'Soldadura PTA')
                {{--
                    Tabla especial de Soldadura PTA con estructura de 3 sub-filas por pieza.
                    El partial recibe $piezasGroup ya agrupado por n_pieza desde el controlador.
                    Se usa en modo 'reporte' (sin inputs, solo lectura).
                    --}}
                @include('processes_views.soldaduraPTA_table_partial', [
                    'piezas' => $piecesInfo,
                    'piezasGroup' => $piezasGroup ?? collect(),
                    'modo' => 'reporte',
                ])
            @else
                <script>
                    document.getElementById('scrollabe-table').appendChild(process.crearProceso()); //Agregar tabla al div.
                </script>
            @endif
        </div>
