@extends('layouts.admin')

@section('content')


<div class="max-w-6xl mx-auto p-6">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                Facturas<span class="font-light text-dorado"></span><span class="text-dorado-400 animate-pulse">_</span>
            </h1>
        </div>
    </header>
    <!-- Encabezado con título y barra de búsqueda -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <form method="post" action="{{ route('facturas.buscar') }}" class="relative mx-12 flex items-center gap-2">
            @csrf
            <label for="searchInput" class="text-white mx-2">Buscar por:</label>

            <!-- Input de búsqueda -->
            <input type="text" id="searchInput" name="search" value="{{ $search }}" placeholder="Buscar..."
                class="w-full sm:w-64 px-4 py-2 rounded-lg border border-gray-400 bg-[#eee]">

            <!-- Categoría -->
            <select name="categoria" id="categoria"
                class="bg-[#eee] p-3 rounded-lg mx-2 border border-gray-400 text-black">
                <option value="id" {{ $categoria == 'id' ? 'selected' : '' }}>Factura</option>
                <option value="inversionista" {{ $categoria == 'inversionista' ? 'selected' : '' }}>Inversionista
                </option>
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
    <div class="tabla-dorada-container">
        <div class="overflow-x-auto custom-scroll">
            <table class="tabla-dorada">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Fecha</th>
                        <th>Proyecto</th>
                        <th>Inversionista</th>
                        <th>XML</th>
                        <th>PDF</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($xmlFiles as $file)
                    <tr>
                        <td>{{ $file->batch_id }}</td>
                        <td>{{ $file->created_at }}</td>
                        <td>{{ $file->proyecto }}</td>
                        <td>{{ $file->inversor_name }}</td>

                        <td>
                            <a href="{{ route('facturas.descargar', $file->id) }}"
                                class="inline-block bg-dorado-400 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-dorado/80 transition shadow-sm">
                                Descargar XML
                            </a>
                        </td>

                        <td>
                            @if($file->pdf_path)
                            <a href="{{ route('facturas.descargarPdf', $file->id) }}"
                                class="inline-block bg-carbon-900 text-dorado-400 px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-carbon-900/90 transition shadow-sm border border-dorado/30">
                                Descargar PDF
                            </a>
                            @else
                            <span class="text-gray-400 italic text-xs">
                                Sin PDF
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-carbon-900 font-medium text-center italic">
                            No se encontraron facturas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 border-t border-carbon-200 p-4 flex justify-center">
            {{ $xmlFiles->links('pagination::tailwind') }}
        </div>
    </div>


</div>
@endsection