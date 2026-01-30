@extends('layouts.user-simple')

@section('content')
<a href="/vista-usuario" class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded mx-2">Regresar</a>
<h1 class="text-center text-black text-3xl p-2"> CONTRATOS</h1>

<div x-data="{ show: false, docId: null, password: '', error: '' }">
    <div class="max-w-6xl mx-auto p-6">
    <!-- Encabezado con título y barra de búsqueda -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <form method="post" action="{{ route('contratos.buscar') }}" class="relative mx-12 flex items-center gap-2">
            @csrf
            <label for="searchInput" class="text-black mx-2">Buscar por:</label>

            <!-- Input de búsqueda -->
            <input 
                type="text" 
                id="searchInput"
                name="search" 
                value="{{ $search }}"
                placeholder="Buscar..." 
                class="w-full sm:w-64 px-4 py-2 rounded-lg border border-gray-400 bg-[#eee]"
            >

            <!-- Categoría -->
            <select name="categoria" id="categoria" class="bg-[#eee] p-3 rounded-lg mx-2 border border-gray-400 text-black">
            <option value="id" {{ $categoria == 'id' ? 'selected' : '' }}>ID</option>
            <option value="folio" {{ $categoria == 'folio' ? 'selected' : '' }}>Folio</option>
            <option value="fecha" {{ $categoria == 'fecha' ? 'selected' : '' }}>Fecha</option>
            </select>

            <!-- Botón -->
            <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 rounded mx-2">
                BUSCAR
            </button>

                        <!-- Botón limpiar filtros -->
            <a href="{{ route('contratos.limpiar') }}" 
            class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded mx-2">
                LIMPIAR
            </a>

        </form>
    </div>

        <!-- Tabla -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="w-full text-sm text-center border-collapse">
                <thead class="bg-[#2f2f2f] text-[#fff] uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-3 border-b">ID</th>
                        <th class="px-6 py-3 border-b">Folio</th>
                        <th class="px-6 py-3 border-b">Proyecto</th>
                        <th class="px-6 py-3 border-b">Fecha</th>
                        <th class="px-6 py-3 border-b">Estado</th>
                        <th class="px-6 py-3 border-b">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eee]">
                    @forelse($contratos as $contrato)
                        <tr class="hover:bg-gray-100 transition">
                            <td class="px-6 py-4">{{ $contrato->id }}</td>
                            <td class="px-6 py-4">{{ $contrato->folio }}</td>
                            <td class="px-6 py-4">{{ $contrato->proyecto }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($contrato->fecha)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded 
                                    {{ $contrato->estado === 'activo' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                    {{ ucfirst($contrato->estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button 
                                    class="bg-[#bb8d1a] text-white px-3 py-1 rounded"
                                     @click=" show=true; docId={{ $contrato->id }}; password=''; error=''">
                                    Descargar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-gray-500">
                                No tienes contratos asignados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
                            <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-lg shadow p-2 ">
                {{ $contratos->links('pagination::tailwind') }}
            </div>
        </div>

        </div>

                <!-- Paginación -->
        <div class="mt-4">
            {{ $contratos->links() }}
        </div>

        <!-- Modal -->
        <div 
            x-show="show"
            x-transition.opacity
            class="fixed inset-0 flex items-center justify-center bg-white/30 backdrop-blur-sm"
            style="display: none;" 
        >
            <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                <h2 class="text-lg font-bold mb-4">Confirmar contraseña</h2>

                <input type="password" x-model="password" placeholder="Ingresa tu contraseña"
                    class="w-full border p-2 rounded mb-2" />

                <p x-text="error" class="text-red-500 text-sm mb-2"></p>

                <div class="flex justify-end space-x-2">
                    <button class="px-4 py-2 bg-gray-300 rounded" @click="show=false">Cancelar</button>
                    <button class="px-4 py-2 bg-[#bb8d1a] text-white rounded" @click="checkPassword">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/checkContratos.js"></script>

@endsection

