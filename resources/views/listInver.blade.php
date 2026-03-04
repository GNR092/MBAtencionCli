@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-full mx-auto p-4 md:p-6">
        <header class="mb-10 px-2">
            <div class="flex items-baseline gap-4">
                <span class="text-dorado-400 text-sm font-serif italic">|</span>
                <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none uppercase">
                    Inversionistas
                </h1>
            </div>
        </header>

    <!-- Encabezado con título y barra de búsqueda -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <form method="GET" action="{{ route('inversionistas.index') }}" class="relative mx-12 flex items-center gap-2">
            <label for="searchInput" class="text-white/70 mx-2 text-sm">Buscar por:</label>

            <!-- Input de búsqueda -->
            <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Buscar..."
                class="w-full sm:w-64 px-4 py-2 rounded-lg border border-[#d8c495]/30 bg-white/10 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495]">

            <!-- Categoría -->
            <select name="categoria" id="categoria" class="bg-[#0d1f30] p-3 rounded-lg mx-2 border border-[#d8c495]/30 text-white">
                <option value="proyectos" {{ request('categoria') == 'proyectos' ? 'selected' : '' }}>Proyectos</option>
                <option value="nombre" {{ request('categoria') == 'nombre' ? 'selected' : '' }}>Nombre inversor</option>
                <option value="factura" {{ request('categoria') == 'factura' ? 'selected' : '' }}>ID Factura</option>
            </select>

            <!-- Botón -->
            <button type="submit" class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] px-4 py-2 rounded mx-2 font-bold text-sm uppercase tracking-wider">
                BUSCAR
            </button>

            <a href="{{ route('inversionistas.limpiar') }}"
                class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded mx-2 text-sm">
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
                        <th>Proyecto</th>
                        <th>Fecha</th>
                        <th>Inversionista</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($xmlFiles as $file)
                    <tr>
                        <td class="font-bold">{{ $file->id }}</td>
                        <td>{{ $file->nombre_proyecto ?? '—' }}</td>
                        <td>{{ $file->created_at }}</td>
                        <td>{{ $file->emisor_name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-10 text-white/30 font-medium italic">
                            No se encontraron facturas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-[#d8c495]/10 p-4 flex justify-center">
            {{ $xmlFiles->links('pagination::tailwind') }}
        </div>
    </div>
</div>
</div>
@endsection
