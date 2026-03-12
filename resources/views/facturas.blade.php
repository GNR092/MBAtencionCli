@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-full mx-auto">
        <header class="mb-10 px-2">
            <div class="flex items-baseline gap-4">
                <span class="text-dorado-400 text-sm font-serif italic">|</span>
                <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none uppercase">
                    Facturas
                </h1>
            </div>
        </header>

    <!-- Barra de búsqueda -->
    <form method="post" action="{{ route('facturas.buscar') }}" class="tabla-dorada-search">
        @csrf
        <label for="searchInput">BUSCAR POR:</label>
        <input type="text" id="searchInput" name="search" value="{{ $search }}" placeholder="Buscar..." class="flex-1 min-w-40">
        <select name="categoria" id="categoria">
            <option value="id" {{ $categoria == 'id' ? 'selected' : '' }}>Factura</option>
            <option value="inversionista" {{ $categoria == 'inversionista' ? 'selected' : '' }}>Inversionista</option>
            <option value="fecha" {{ $categoria == 'fecha' ? 'selected' : '' }}>Fecha</option>
        </select>
        <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-[#0d1f30] px-6 py-2 rounded-xl font-bold text-sm uppercase tracking-wider transition">
            BUSCAR
        </button>
        <a href="{{ route('facturas.limpiar') }}" class="bg-white/10 hover:bg-white/20 text-white px-6 py-2 rounded-xl text-sm font-bold transition">
            LIMPIAR
        </a>
    </form>

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
                        <td class="font-bold text-[#d8c495]">{{ $file->batch_id }}</td>
                        <td class="text-white/70 text-xs">{{ $file->created_at }}</td>
                        <td class="text-white/80">-</td>
                        <td class="font-medium">{{ $file->user->name ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('facturas.descargar', $file->id) }}"
                                class="inline-block bg-[#d8c495] text-[#0d1f30] px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-[#c9a143] transition shadow-sm">
                                Descargar XML
                            </a>
                        </td>
                        <td>
                            @if($file->pdf_exists)
                            <a href="{{ route('facturas.descargarPdf', $file->id) }}"
                                class="inline-block bg-white/10 text-[#d8c495] px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-white/20 transition shadow-sm border border-[#d8c495]/30">
                                Descargar PDF
                            </a>
                            @else
                            <span class="text-white/30 italic text-xs">Sin PDF</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-white/40 italic">No se encontraron inversionistas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="tabla-dorada-footer">
            {{ $xmlFiles->links('pagination::tailwind') }}
        </div>
    </div>
</div>
</div>
@endsection
