@extends('layouts.admin')
@section('content')
<h1 class="text-center text-black text-3xl p-2">Registro de Inversionistas</h1>

<style>
    /* Force text color to white and background to a dark color for multiselect dropdown list items */
    div.multiselect-dropdown-list > div,
    div.multiselect-dropdown-list > div label {
        color: white !important;
        background-color: #333 !important; /* Dark background */
    }

    /* Ensure hover/focus states also have white text on a distinguishable background */
    div.multiselect-dropdown-list > div:hover {
        background-color: #555 !important; /* Slightly lighter on hover */
        color: white !important;
    }

    /* Ensure selected items also have white text on a distinguishable background */
    div.multiselect-dropdown-list > div.checked {
        background-color: #444 !important; /* Different background for checked */
        color: white !important;
    }
</style>

@if(session('success'))
    <!-- Modal -->
    <div id="successModal" class="fixed inset-0 bg-white/30 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96 text-center">
            <span class="text-xl font-bold text-green-700 mb-2">¡Éxito!</span>
            <p class="text-gray-700">{{ session('success') }}</p>
            
            <button onclick="document.getElementById('successModal').remove()" 
                class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Cerrar
            </button>
        </div>
    </div>
@endif

    {{-- Errores de validación --}}
    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


<div class="flex justify-center py-2">
    <form id="registroUsuarios" class="bg-[#2f2f2f] p-6 rounded-2xl shadow-lg w-full max-w-md" action="{{ route('registroUsuarios.datos') }}" method="POST">
        @csrf
        <h2 class="text-white text-2xl font-semibold mb-4">Registro de Inversionistas</h2>
        <!--nombre-->
        <div class="mb-4">
            <label for="name" class="block text-white">Nombre:</label>
            <input type="text" id="name" name="name" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200" value="{{ old('name') }}" required>
        </div>

        <!--email-->
        <div class="mb-4">
            <label for="email" class="block text-white">Correo Electrónico:</label>
            <input type="email" id="email" name="email" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200" value="{{ old('email') }}" required>
        </div>
        <!-- contraseña -->
        <div class="mb-4">
            <label for="password" class="block text-white">Contraseña generada:</label>
            <input type="text" id="password" name="password" 
                class="p-1 w-full mt-1 border border-gray-300 rounded-md shadow-sm bg-gray-200" 
                value="{{ session('generated_password') }}" readonly>
            <small class="text-white">Se genera automáticamente al registrar.</small>
        </div>

                <!--proyectos-->
        <div class="mb-4">
            <label class="text-white">Proyectos</label>
            <select name="proyect[]" id="proyect" multiple required multiselect-hide-x="true" class="text-black">
            <option value="RESIDENT 1">RESIDENT 1</option>
            <option value="RESIDENT 2">RESIDENT 2</option>
            <option value="CAMPUS RECIDENCIA">CAMPUS RECIDENCIA</option>
            <option value="TMZN 122">TMZN 122</option>
            <option value="GRAND TEMOZON">GRAND TEMOZÓN</option>
            <option value="Aldea Borboleta I">Aldea Borboleta I</option>
            <option value="Aldea Borboleta II">Aldea Borboleta II </option>
            <option value="Aldea Borboleta III">Aldea Borboleta III</option>
            <option value="MB RESORT MERIDA">MB RESORT MÉRIDA</option>
            <option value="Princess Village">Princess Village</option>
            <option value="Royal Square Plaza">Royal Square Plaza</option>
            <option value="RUM">RUM</option>
            <option value="Avenue Temozon">Avenue Temozón</option>
            <option value="MB Resort Orlando">MB Resort Orlando</option>
            <option value="MB Wellness Resort">MB Wellness Resort</option>
            </select>
        </div>

        <!-- Regimen fiscal -->
        <div class="mb-4">
            <label class="text-white">Regimen fiscal</label>
            <select name="regimenFiscal" id="regimenFiscal" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200 text-black">
            <option value="resico">RESICO</option>
            <option value="arrendamiento">ARRENDAMIENTO</option>
            <option value="persona moral">PERSONA MORAL</option>
            <option value="rif">RIF</option>
            </select>
        </div>
        <!-- Teléfono -->
        <div class="mb-4">
            <label for="phone" class="block text-white">Número telefónico:</label>
            <div class="flex items-center">
                <span class="phone-prefix rounded-l-md px-2 py-1">+52</span>
                <input type="tel" id="phone" name="phone"
                    class="p-1 shadow-sm rounded-r-md"
                    maxlength="10"
                    pattern="[0-9]{10}"   
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                    value="{{ old('phone') }}"
                    required>
            </div>
            <small class="text-white">Formato: 10 dígitos (ejemplo: 9999999999)</small>
        </div>


        <button type="submit" class="ml-25 mt-4 bg-[#d4a017] text-white px-4 font-semibold py-2 rounded-lg transition duration-300 ease-in-out hover:scale-105 hover:bg-[#b58714]">Registrar Usuario</button>
    </form>

</div>
<script src="js/multiselect.js"></script>

@endsection