@extends('layouts.appMenu')

@section('head')
    @vite(['resources/css/reportes/reenvio.css'])
    <title>Enviar Reporte por Correo</title>
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')
    <div class="reenvio-container">


        <div class="reenvio-header">
            <h1>Enviar Reporte de Producción</h1>
            <p class="reenvio-subheader">
                Genera y envía el reporte de un día específico a los destinatarios que indiques.
            </p>
        </div>

        {{-- Alertas --}}
        @if(session('success'))
            <div class="reenvio-alert reenvio-alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="reenvio-alert reenvio-alert-error">{{ session('error') }}</div>
        @endif

        {{-- Info box --}}
        <div class="reenvio-info">
            <strong>¿Cómo funciona?:</strong>
            Selecciona la fecha del turno que deseas reportar, escribe los correos de los destinatarios
            (separados por coma) y presiona <strong><em>Enviar Reporte</em></strong>. El sistema consultará los registros de
            producción de ese día y generará el correo automáticamente.
        </div>

        {{-- Formulario principal --}}
        <div class="reenvio-card">
            <h2>Parametros del Reporte</h2>

            <form action="{{ route('reportes.produccion.reenviar') }}" method="POST">
                @csrf

                {{-- Fecha --}}
                <div class="reenvio-field">
                    <label>Fecha del turno a reportar</label>
                    <input type="date" name="fecha" class="reenvio-input" value="{{ old('fecha', now()->toDateString()) }}"
                        max="{{ now()->toDateString() }}" required>
                    <p class="reenvio-hint">Solo puedes seleccionar fechas pasadas o el día de hoy.</p>
                </div>

                {{-- Destinatarios --}}
                <div class="reenvio-field">
                    <label>Destinatarios</label>
                    <input type="text" name="correos" class="reenvio-input"
                        placeholder="gerencia@empresa.com, supervisor@empresa.com" value="{{ old('correos') }}">
                    <p class="reenvio-hint">
                        Escribe uno o varios correos separados por coma.
                        Si lo dejas vacío, se enviará a los destinatarios configurados por defecto.
                    </p>
                </div>

                {{-- Botones --}}
                <div class="reenvio-actions">
                    <button type="submit" class="reenvio-btn reenvio-btn-primary">
                        Enviar Reporte
                    </button>
                    <a href="{{ route('home') }}" class="reenvio-btn reenvio-btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        {{-- Envío automático --}}
        <div class="reenvio-card reenvio-card-secondary">
            <h2>Envio Automatico (23:59 hrs)</h2>
            <p class="reenvio-scheduler-text">
                El sistema está configurado para enviar el reporte automáticamente todos los días
                a las <strong>23:59 hrs</strong>. Los destinatarios son los configurados en el sistema.
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.reenvio-alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('fade-out');
                    setTimeout(() => {
                        alert.remove();
                    }, 1000); // Esperar a que termine la transición de opacidad
                }, 5000); // 5 segundos antes de empezar a desvanecer
            });
        });
    </script>
@endsection
