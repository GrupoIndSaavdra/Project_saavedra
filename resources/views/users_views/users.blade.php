@extends($layout) <!-- Cambia 'app' según tu layout principal -->

@section('content')

    <head>
        <meta charset="UTF-8">
        <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de usuarios</title>

        @vite(['resources/css/app.css', 'resources/css/viewUsers.css', 'resources/js/viewUsers.js'])
        <title>@yield('title')</title>
    </head>

    <body background="{{ asset('images/fondoLogin.jpg') }}">
        <div class="container1">
            <!-- Buscador -->
            <div class="search-container">
                <h1>Gestión de usuarios</h1>
                <div>
                    <input type="text" class="search-input" placeholder="Buscar por matrícula o nombre">
                    <button class="btn">Buscar</button>
                </div>
            </div>

            <!-- Tabla -->
            <table>
                <thead>
                    <tr>
                        <th>
                            <select class="role-select">
                                <option value="todos">Rol</option>
                                <option value="admin">Administrador</option>
                                <option value="operador">Operador</option>
                                <option value="almacen">Almacen</option>
                                <option value="master">Master</option>
                                <option value="calidad">Calidad</option>
                            </select>
                        </th>
                        <th>Matrícula</th>
                        <th>Usuario</th>
                        <th>Fecha de registro</th>
                        <th>Fecha de alta/baja</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Administrador</td>
                        <td>11111</td>
                        <td>Abraham Saavedra Ricalde</td>
                        <td>2025-01-01</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Administrador</td>
                        <td>4171</td>
                        <td>Marco Joel Angel Velasco</td>
                        <td>2025-01-02</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Operador</td>
                        <td>4070</td>
                        <td>Natali Joselin Alemán Perez</td>
                        <td>2025-01-03</td>
                        <td>2025-01-01</td>
                    </tr>
                </tbody>
            </table>
            <!-- Menú Contextual -->
            <ul id="context-menu" class="context-menu">
                <a href="{{ route('alta_usuario') }}" class="menu-item">Dar de alta</a>
                <a href="{{ route('baja_usuario') }}" class="menu-item">Dar de baja</a>
                <a href="{{ route('eliminar_usuario') }}" class="eliminar-option">Eliminar</a>
            </ul>
        </div>
        <script src="viewUsers.js"></script>
    </body>
@endsection
