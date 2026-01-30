@extends('layouts.admin')


@section('content')

        <h1 class="text-center text-black text-3xl p-4"> CONTRATOS</h1>
        <div class="flex justify-center py-4">

            @php
                $user = session('user');
            @endphp

            <form action="/subir-archivo" method="POST" enctype="multipart/form-data" class="bg-[#2f2f2f] p-6 rounded-2xl shadow-lg w-full max-w-md">
                @csrf

                <!-- Título -->
                <h2 class="text-white text-2xl font-semibold mb-4">📄Subir contrato</h2>

                <!-- Input archivo -->
                <div class="mb-6">
                    <input type="file" name="archivo" multiple required  accept=".pdf"
                        class="w-full text-sm text-gray-900 bg-gray-200 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
               
                <div class="mb-6">
                <!-- Select solo para admin -->
                @if($user && $user->rol === 'administrador')
                    <label for="myInput" class="text-white">Asignar a usuario:</label>

                    <!-- Input de búsqueda -->
                    <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Buscar usuario..." 
                        class="bg-white rounded-lg px-4 py-2 w-full">

                    <!-- Lista de usuarios -->
                    <ul id="myUL"
                        class="absolute w-64 bg-white rounded-lg mt-1 shadow-md max-h-60 overflow-y-auto hidden z-50">
                        @foreach ($users as $u)
                            <li>
                                <a href="#" 
                                onclick="selectUser('{{ $u->id }}', '{{ $u->name }}')" 
                                class="block px-4 py-2 hover:bg-gray-200 ">
                                {{ $u->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Input oculto para guardar el id del usuario -->
                    <input type="hidden" name="user_id" id="selectedUserId">
                @endif

                <div>
                    <label for="proyect" class="text-white">Selecciona proyecto:</label>
                    <select name="proyect" id="proyect" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200 text-black" required>
                        <option value="" disabled selected>Selecciona proyecto</option>
                        <option value="RESIDENT 1">RESIDENT 1</option>
                        <option value="RESIDENT 2">RESIDENT 2</option>
                        <option value="CAMPUS RECIDENCIA">CAMPUS RECIDENCIA</option>
                        <option value="TMZN 122">TMZN 122</option>
                        <option value="GRAND TEMOZON">GRAND TEMOZÓN</option>
                        <option value="MB RESORT MERIDA">MB RESORT MÉRIDA</option>
                        <option value="Princess Village">Princess Village</option>
                        <option value="Royal Square Plaza">Royal Square Plaza</option>
                        <option value="RUM">RUM</option>
                        <option value="Avenue Temozon">Avenue Temozón</option>
                        <option value="MB Resort Orlando">MB Resort Orlando</option>
                        <option value="MB Wellness Resort">MB Wellness Resort</option>
                        <option value="Aldea Borboleta I">Aldea Borboleta I</option>
                        <option value="Aldea Borboleta II">Aldea Borboleta II </option>
                        <option value="Aldea Borboleta III">Aldea Borboleta III</option>
                    </select>
                </div>

                
                <!--importe-->
                <div class="mb-6 mt-2">
                    <label class="text-white">
                        Importe Bruto *
                    </label>
                    <input 
                        type="text" 
                        name="importe_bruto_renta" 
                        id="importe_bruto_renta"
                        placeholder="$0.00"
                        class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200" 
                        value="{{ old('importe_bruto_renta') }}"
                        required
                        onblur="formatearImporte(this)"
                    >
                    <small class="text-white">Ejemplo: $1,500,000.50</small>
                </div>
                 <!-- fecha de inicio -->
                 <div class="mb-6 mt-2">
                    <label class="text-white">
                        Fecha de inicio*
                    </label>
                    <input 
                        type="date" 
                        name="fecha_inicio" 
                        id="fecha_inicio"
                        placeholder="dd/mm/aaaa"
                        class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200" 
                        value="{{ old('fecha_inicio') }}"
                        required
                        >
                 </div>
                  <!--fecha de terminacion -->
                <div class="mb-6 mt-2">
                    <label class="text-white">
                        Fecha de terminacion*
                    </label>
                    <input 
                        type="date" 
                        name="fecha_terminacion"
                        id="fecha_terminacion"
                        placeholder="dd/mm/aaaa"
                        class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200" 
                        value="{{ old('fecha_terminacion') }}"
                        required
                        >
                 </div>
                <!-- Estado del contrato -->
                <h3 class="text-white text-xl font-semibold mb-2 mt-6">Estado del contrato</h3>
                <div class="space-y-2 mb-4">
                    <label class="flex items-center space-x-2 text-white">
                        <input  type="checkbox" id="activo" name="activo" value="1" class="form-checkbox text-green-500 rounded">
                        <span>Activo</span>
                    </label>

                    <label class="flex items-center space-x-2 text-white">
                        <input  type="checkbox" id="inactivo" name="inactivo" value="1" class="form-checkbox text-red-500 rounded">
                        <span>Inactivo</span>
                    </label>
                </div>
                   
                <div id="estado-error" class="hidden bg-red-500 text-white px-4 py-2 rounded-md mt-2"></div>                     
                
                <!-- Botón con ping -->
                <div class="relative inline-flex items-center mb-6">
                    <!-- Ping animado -->
                    <span class="absolute -top-1 -right-1 inline-flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#fec127] opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-[#ffd773]"></span>
                    </span>

                    <!-- Botón -->
                    <button type="submit" 
                        class="bg-[#d4a017] text-white px-6 py-2 rounded-lg font-semibold shadow-md transition duration-300 ease-in-out hover:scale-105 hover:bg-[#b58714]">
                        SUBIR
                    </button>
                </div>
            
            </form>
        </div>

        <!-- Alerta de éxito -->
        @if(session('success'))
            <div id="alert" 
                class="fixed top-5 right-5 flex items-center justify-between px-4 py-3 bg-green-500 text-white rounded-lg shadow-lg animate-fade-in-down">
                <span>{{ session('success') }}</span>
                <button onclick="document.getElementById('alert').remove()" class="ml-3 font-bold">✖</button>
            </div>
        @endif
<script src="/js/validateEstado.js"></script>
<script src="/js/filter.js"></script>
<script>
function formatearImporte(input) {
    let valor = input.value;

    // Eliminar todo lo que no sea número o punto
    valor = valor.replace(/[^0-9.]/g, '');

    // Convertir a número
    let numero = parseFloat(valor);

    // Validar que sea número
    if (!isNaN(numero)) {
        // Formatear con separador de miles y dos decimales
        input.value = '$' + numero.toLocaleString('en-US', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });
    } else {
        input.value = '';
    }
}
</script>
@endsection