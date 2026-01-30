@extends('layouts.admin')

@section('content')
<h1 class="text-center text-black text-3xl p-4">Editar Contrato</h1>

<div class="flex justify-center py-4">
    <form action="{{ route('contratos.actualizar', $contractToEdit->id) }}" method="POST" enctype="multipart/form-data" class="bg-[#2f2f2f] p-6 rounded-2xl shadow-lg w-full max-w-md">
        @csrf
        @method('PUT')

        <h2 class="text-white text-2xl font-semibold mb-4">📄 Editar contrato</h2>

        <!-- Nombre del archivo actual -->
        <div class="mb-4">
            <p class="text-white mb-2">Archivo actual:</p>
            <p class="bg-gray-100 rounded px-3 py-2">{{ $contractToEdit->nombre }}</p>
        </div>

        <!-- Campo para subir nuevo archivo -->
        <div class="mb-6">
            <label class="text-white">Actualizar archivo (opcional):</label>
            <input type="file" name="archivo" accept=".pdf"
                class="w-full text-sm text-gray-900 bg-gray-200 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <!-- Seleccionar usuario (solo admin) -->
        @if($admin && $admin->rol === 'administrador')
        <div class="mb-6">
            <label for="user_id" class="text-white">Asignar a usuario:</label>
            <select name="user_id" id="user_id" class="w-full p-2 rounded bg-gray-100 text-black">
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ $contractToEdit->user_id == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                @endforeach
            </select>
        </div>
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


        <!-- Importe -->
        <div class="mb-6">
            <label class="text-white">Importe Bruto *</label>
            <input 
                type="text" 
                name="importe_bruto_renta" 
                id="importe_bruto_renta"
                value="{{ old('importe_bruto_renta', $contractToEdit->importe_bruto_renta) }}"
                class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200" 
                onblur="formatearImporte(this)"
                required
            >
        </div>
        <!-- Estado -->
        <h3 class="text-white text-xl font-semibold mb-2 mt-6">Estado del contrato</h3>
        <div class="space-y-2 mb-4">
            <label class="flex items-center space-x-2 text-white">
                <input type="checkbox" name="activo" value="1" {{ $contractToEdit->estado === 'activo' ? 'checked' : '' }}>
                <span>Activo</span>
            </label>

            <label class="flex items-center space-x-2 text-white">
                <input type="checkbox" name="inactivo" value="1" {{ $contractToEdit->estado === 'inactivo' ? 'checked' : '' }}>
                <span>Inactivo</span>
            </label>
        </div>

        <button type="submit"
            class="bg-[#d4a017] text-white px-6 py-2 rounded-lg font-semibold shadow-md transition duration-300 ease-in-out hover:scale-105 hover:bg-[#b58714]">
            Guardar cambios
        </button>
    </form>
</div>
@endsection
