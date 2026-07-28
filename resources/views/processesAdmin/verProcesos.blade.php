@extends('layouts.appMenu')

@section('head')
    <title>Progreso de Procesos</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <style>
        .container-procesos {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        .titulo-seccion {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
        }
        .tabla-procesos {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .tabla-procesos thead tr {
            background: #2c3e50;
            color: #fff;
        }
        .tabla-procesos th, .tabla-procesos td {
            padding: 0.65rem 1rem;
            text-align: center;
            font-size: 0.85rem;
            border-bottom: 1px solid #eaeaea;
        }
        .tabla-procesos th {
            font-weight: 600;
            letter-spacing: 0.03em;
        }
        .tabla-procesos tbody tr:hover {
            background: #f0f4f8;
        }
        .badge-num {
            display: inline-block;
            background: #27ae60;
            color: #fff;
            border-radius: 12px;
            padding: 0.15rem 0.6rem;
            font-size: 0.8rem;
            font-weight: 600;
            min-width: 30px;
        }
        .badge-zero {
            background: #bdc3c7;
        }
        .sin-datos {
            text-align: center;
            padding: 3rem;
            color: #7f8c8d;
            font-size: 1rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-procesos">
        <h1 class="titulo-seccion">Progreso de Procesos por Orden de Trabajo</h1>

        @isset($ot)
            @if(count($ot) > 0)
                <table class="tabla-procesos">
                    <thead>
                        <tr>
                            <th>OT</th>
                            <th>Moldura</th>
                            <th>Clase</th>
                            <th>Cepillado</th>
                            <th>Desbaste</th>
                            <th>Rev. Laterales</th>
                            <th>1ª Ope. Sold.</th>
                            <th>2ª Ope. Sold.</th>
                            <th>Pedido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ot as $fila)
                            <tr>
                                <td><strong>{{ $fila[0] ?? '—' }}</strong></td>
                                <td>{{ $fila[1] ?? '—' }}</td>
                                <td>{{ $fila[2] ?? '—' }}</td>
                                <td>
                                    <span class="badge-num {{ ($fila[3] ?? 0) == 0 ? 'badge-zero' : '' }}">
                                        {{ $fila[3] ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-num {{ ($fila[4] ?? 0) == 0 ? 'badge-zero' : '' }}">
                                        {{ $fila[4] ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-num {{ ($fila[5] ?? 0) == 0 ? 'badge-zero' : '' }}">
                                        {{ $fila[5] ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-num {{ ($fila[6] ?? 0) == 0 ? 'badge-zero' : '' }}">
                                        {{ $fila[6] ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-num {{ ($fila[7] ?? 0) == 0 ? 'badge-zero' : '' }}">
                                        {{ $fila[7] ?? 0 }}
                                    </span>
                                </td>
                                <td>{{ $fila[25] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="sin-datos">No hay órdenes de trabajo activas en este momento.</p>
            @endif
        @else
            <p class="sin-datos">No hay datos disponibles para mostrar.</p>
        @endisset
    </div>
@endsection
