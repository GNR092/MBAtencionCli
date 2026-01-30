@extends('layouts.admin')

@section('content')
        <h1 class="text-center text-black text-3xl p-2">FACTURAS</h1>
<div class="max-w-6xl mx-auto p-6">
    <!-- Encabezado con título y barra de búsqueda -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <form method="post" action="{{ route('facturas.buscar') }}" class="relative mx-12 flex items-center gap-2">
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
            <option value="id" {{ $categoria == 'id' ? 'selected' : '' }}>Factura</option>
            <option value="inversionista" {{ $categoria == 'inversionista' ? 'selected' : '' }}>Inversionista</option>
            <option value="fecha" {{ $categoria == 'fecha' ? 'selected' : '' }}>Fecha</option>
            </select>

            <!-- Botón -->
            <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 rounded mx-2">
                BUSCAR
            </button>

                        <!-- Botón limpiar filtros -->
            <a href="{{ route('facturas.limpiar') }}" 
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
                    <th class="px-6 py-3 border-b">Factura</th>
                    <th class="px-6 py-3 border-b">Fecha</th>
                    <th class="px-6 py-3 border-b">Proyecto</th>
                    <th class="px-6 py-3 border-b">Inversionista</th>
                    <th class="px-6 py-3 border-b">XML</th>
                    <th class="px-6 py-3 border-b">PDF</th>
                </tr>
            </thead>
            <tbody id="tableBody" class="divide-y divide-[#eee]">
                 @forelse($xmlFiles as $file)
                <tr class="hover:bg-gray-100 transition">
                    <td class="px-6 py-4">{{ $file->batch_id }}</td>
                    <td class="px-6 py-4">{{ $file->created_at}}</td>
                    <td class="px-6 py-4">{{ $file->proyectos }}</td>
                    <td class="px-6 py-4">{{ $file->emisor_name }}</td>
                    <td class="px-6 py-4">
                    <a href="{{ route('facturas.descargar', $file->id) }}"
                    class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 rounded-lg transition inline-block">
                    Descargar XML
                    </a>
                    </td>
                    <td class="px-6 py-4">
                        @if($file->pdf_path)
                            <a href="{{ route('facturas.descargarPdf', $file->id) }}"
                            class="bg-[#ffb300] hover:bg-[#e0a800] text-black px-4 py-2 rounded-lg transition inline-block">
                                Descargar PDF
                            </a>
                        @else
                            <span class="text-gray-400 italic">
                                Sin PDF
                            </span>
                        @endif
                    </td>

                 </tr>
                        @empty
                <tr>
                    <td colspan="5">No se encontraron facturas</td>
                </tr>
                @endforelse
                
            </tbody>
        </table>
                <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-lg shadow p-2 ">
                {{ $xmlFiles->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection