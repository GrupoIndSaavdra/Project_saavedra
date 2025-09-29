@extends('layouts.appAdmin')
@section('content')

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Piezas en máquinas</title>
    @vite('resources/css/maquinas.css')
    <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
</head>

<body background="{{ asset('images/fondoLogin.jpg') }}">
    <script>
        function crearSelect(ot, arregloOT, div){
            if(ot.value != "null"){
                for(let i=0; i<arregloOT.length; i++){
                    if(arregloOT[i][0] == ot.value){
                        //Eliminar select de clases si existe
                        let label = document.getElementsByClassName('label-select')[1];
                        let select_clases = document.getElementById('clases');
                        let button = document.getElementsByClassName('btn-search')[0];
                        if(select_clases != null){
                            label.remove();
                            select_clases.remove();
                            button.remove();
                        }

                        //Creacion de la etiqueta para el select de clases
                        label = document.createElement('label');
                        label.for = 'clases';
                        label.className = 'label-select';
                        label.innerHTML = 'Selecciona la clase:';

                        //Creacion del select de clases
                        select_clases = document.createElement('select');
                        select_clases.name = 'clase';
                        select_clases.id = 'clases';

                        //Creacion de las opciones para el select de clases
                        arregloOT[i][1].forEach((clase) => {
                            let option = document.createElement('option');
                            console.log(clase[0] + " " + clase[1]);
                            option.value = clase[0];
                            option.text = clase[1];
                            select_clases.appendChild(option);
                        });

                        //Creacion del boton
                        button = document.createElement('button');
                        button.className = 'btn-search';
                        button.type = 'submit';
                        button.innerHTML = 'Buscar máquinas';

                        //Agregar elementos al div
                        div.appendChild(label);
                        div.appendChild(select_clases);
                        div.appendChild(button);
                    }
                }
            }else{
                //Eliminar select de clases
                let label = document.getElementsByClassName('label-select')[1];
                let select_clases = document.getElementById('clases');
                let button = document.getElementsByClassName('btn-search')[0];
                if(select_clases != null){
                    label.remove();
                    select_clases.remove();
                    button.remove();
                }
            }
        }
    </script>
    <div class="container">
        <form action="{{route('showMachinesProcess')}}" method="post" class="wrapper bg-white">
            @csrf
            <h1 class="title">Piezas en máquinas</h1>
            <img src="{{ asset('images/lg_saavedra.png') }}" class="logo-saa">
            <div class="selecttts">
                @isset($arregloOT)
                    <div class="select-container">
                        <label for="title" class="label-select">Selecciona la O.T:</label>
                        <select name="ot" id="ot">
                            <option value="null"></option>
                            @foreach ($arregloOT as $ot)
                                <option value="{{ $ot[0] }}">{{ $ot[0] }}</option>
                            @endforeach
                        </select>
                        <script>
                            let select_ot = document.getElementById('ot');
                            let container = document.getElementsByClassName('select-container')[0];
                            let arregloOT = @json($arregloOT);
                            select_ot.addEventListener("change", function(){
                                crearSelect(select_ot, arregloOT, container);
                            });
                        </script>
                    </div><br>
                @else
                    <div class="select-container">
                        <label for="title" class="label-select">Sin ordenes de trabajo registradas</label>
                    </div>
                @endisset
            </div>
        </form>
    </div>
</body>
@endsection