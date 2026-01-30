@extends('layouts.admin')

@section('content')
        <h1 class="text-center text-black text-3xl p-2">Enviar aviso a usuario(s)</h1>

        @if(session('success'))
        <div class="bg-green-800 text-white p-4 mb-6 rounded">
            {{ session('success') }}
        </div>
    @endif
        <!-- Formulario -->

<div class="w-full py-10 px-6 z-10 relative bg-[#2f2f2f] rounded-lg shadow-lg">

    <form action="{{ route('avisos.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Buscar usuario -->
        <div class="flex items-center justify-between gap-4 mb-6">
            <label class="text-white w-40 text-right">Buscar usuario:</label>
            <input type="text" 
                name="usuario" 
                class="flex-1 bg-[#eee] text-black placeholder-gray-700 border border-gray-600 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-gray-500" 
                placeholder="nombre, correo, ID">
        </div>
            <!-- Enviar a todos -->
        <div class="flex items-center  gap-4 mb-6">
            <label class="text-white w-40 text-right">Enviar a todos:</label>
            <input type="checkbox" name="todos" value="1">
             <!-- Enviar por proyecto -->
            <select name="proyect[]" id="proyect" class="bg-[#eee] p-3 rounded-lg mx-2 text-black">
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

        <!-- Asunto -->
        <div class="flex items-center justify-between gap-4 mb-6 ">
            <label class="text-white w-40 text-right">Asunto:</label>
            <input type="text" 
                name="asunto" 
                class="flex-1 bg-white text-black placeholder-gray-400 border border-gray-600 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
        </div>

        <!-- Mensaje -->
        <div class="flex items-start justify-between gap-4 mb-6">
            <label class="text-white w-40 text-right mt-2">Mensaje:</label>
            <textarea name="mensaje" rows="4" 
                class="flex-1 bg-white text-black placeholder-gray-700 border border-gray-600 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-gray-500"  
                placeholder="escribe aquí"></textarea>
        </div>

        <!-- Prioridad -->
        <div class="flex items-center justify-between gap-4 mb-6">
            <label class="text-white w-40 text-right">Prioridad:</label>
            <select name="prioridad" 
                class="flex-1 bg-white text-black border border-gray-600 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
                <option value="alta">Alta</option>
                <option value="media" selected>Media</option>
                <option value="baja">Baja</option>
            </select>
        </div>

        <!-- Canal de envío -->
        <div class="flex items-start justify-between gap-4 mb-6">
            <label class="text-white w-40 text-right mt-2">Canal de envío:</label>
            <div class="flex flex-col gap-2 flex-1">
                <label class="flex items-center space-x-2 text-white">
                    <input type="checkbox" name="canales[]" value="interno" class="rounded">
                    <span>Interno</span>
                </label>
                <label class="flex items-center space-x-2 text-white">
                    <input type="checkbox" name="canales[]" value="correo" class="rounded">
                    <span>Correo electrónico</span>
                </label>
                <label class="flex items-center space-x-2 text-white">
                <input type="checkbox" name="canales[]" value="whatsapp" class="rounded">
                <span>WhatsApp</span>
            </label>
            </div>
        </div>

        <!-- Botón -->
        <div class="text-center">
            <button type="submit" 
                class="bg-[#d4a017] text-white px-8 py-2 rounded-lg font-bold hover:bg-[#b58714] transition">
                ENVIAR AVISO
            </button>
        </div>
    </form>
@endsection