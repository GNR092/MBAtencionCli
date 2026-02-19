@extends('layouts.admin')

@section('content')
<div class="p-6">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                Impuestos<span class="font-light text-dorado"></span><span class="text-dorado-400 animate-pulse">_</span>
            </h1>
        </div>
    </header>
    <!-- Encabezado con título y barra de búsqueda -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <form method="GET" action="{{ route('impuestos') }}" class="relative mx-12 flex items-center gap-2">
            @csrf
            <label for="searchInput" class="text-white/70 mx-2 text-sm">Buscar por:</label>

            <!-- Input de búsqueda -->
            <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Buscar..."
                class="w-full sm:w-64 px-4 py-2 rounded-lg border border-[#d8c495]/30 bg-white/10 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495]">

            <!-- Categoría -->
            <select name="categoria" id="categoria"
                class="bg-[#0d1f30] p-3 rounded-lg mx-2 border border-[#d8c495]/30 text-white">
                <option value="proyecto" {{ request('categoria') == 'proyecto' ? 'selected' : '' }}>Proyecto</option>
                <option value="departamento" {{ request('categoria') == 'departamento' ? 'selected' : '' }}>Departamento</option>
                <option value="inversionista" {{ request('categoria') == 'inversionista' ? 'selected' : '' }}>Inversionista</option>
            </select>

            <!-- Botón -->
            <button type="submit" class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] px-4 py-2 rounded mx-2 font-bold text-sm uppercase tracking-wider">
                BUSCAR
            </button>

            <!-- Botón limpiar filtros -->
            <a href="{{ route('impuestos.limpiar') }}"
                class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded mx-2 text-sm">
                LIMPIAR
            </a>

            <!-- Botón descargar -->
            <button type="button" onClick="openModalDescarga()"
                class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] px-4 py-2 mx-2 rounded font-bold text-sm uppercase tracking-wider">
                Descargar
            </button>
        </form>

    </div>

    <!-- Tabla -->
    <div class="tabla-dorada-container">
        <div class="overflow-x-auto custom-scroll">
            <table class="tabla-dorada">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>UUID</th>
                        <th>Fecha</th>
                        <th>Proyecto</th>
                        <th>Departamento</th>
                        <th>Inversionista</th>
                        <th>Tipo Factor</th>
                        <th>Régimen Fiscal</th>
                        <th>Importe Base</th>
                        <th>Importe ISR</th>
                    </tr>
                </thead>

                <tbody id="tableBody">
                    @forelse($xmlFiles as $file)
                    <tr>
                        <td>{{ $file->id }}</td>
                        <td class="text-xs font-mono">{{ Str::limit($file->uuid, 8) }}</td>
                        <td>{{ $file->created_at }}</td>
                        <td>{{ $file->proyecto ?? '—' }}</td>
                        <td>{{ $file->departamento }}</td>
                        <td>{{ $file->emisor_name }}</td>
                        <td>{{ $file->tipoFactor }}</td>
                        <td>{{ $file->regimenFiscal ?? '—' }}</td>
                        <td class="font-medium">${{ number_format($file->importeBase, 2) }}</td>
                        <td class="font-medium text-red-400">${{ number_format($file->isr, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-10 text-white/30 font-medium italic">
                            No se encontraron facturas
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="9" class="text-right px-6 py-3 uppercase text-xs tracking-wider">TOTAL BASE:</td>
                        <td class="px-4 py-3 font-black">
                            ${{ number_format($totalBase, 2) }}
                        </td>
                    </tr>
                    <tr class="border-t border-[#d8c495]/10">
                        <td colspan="9" class="text-right px-6 py-3 uppercase text-xs tracking-wider">TOTAL ISR RETENIDO:</td>
                        <td class="px-4 py-3 font-black text-red-400">
                            ${{ number_format($totalISR, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="border-t border-[#d8c495]/10 p-4 flex justify-center">
            {{ $xmlFiles->links('pagination::tailwind') }}
        </div>
    </div>

    <!--modal para la descarga-->
    <div id="descargaModal"
        class="bg-black/60 backdrop-blur-sm fixed inset-0 flex items-center justify-center hidden">
        <div class="bg-[#112134] border border-[#d8c495]/20 rounded-xl shadow-lg p-8 relative mx-auto flex max-h-[90vh] w-[800px] flex-col">
            <h2 class="text-[#d8c495] font-bold uppercase tracking-widest text-sm mb-6">
                Defina las opciones para la descarga
            </h2>
            <div>
                <form action="{{ route('impuestos.export') }}" method="POST">
                    @csrf
                    <!--fecha de inicio-->
                    <div class="mb-6 mt-2">
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                            Desde
                        </label>
                        <input type="date" name="desde" id="desde"
                            class="p-3 mt-1 block w-full border border-[#d8c495]/30 rounded-lg bg-white/5 text-white focus:outline-none focus:border-[#d8c495]">
                    </div>
                    <!--fecha de terminacion-->
                    <div class="mb-6 mt-2">
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                            Hasta
                        </label>
                        <input type="date" name="hasta" id="hasta"
                            class="p-3 mt-1 block w-full border border-[#d8c495]/30 rounded-lg bg-white/5 text-white focus:outline-none focus:border-[#d8c495]">
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="closeModalDescarga()"
                            class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded">Cerrar</button>

                        <button type="submit" class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] font-bold px-4 py-2 rounded">
                            Descargar Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


</div>
<!--scrip para el filtro-->
<script>
function openModalDescarga() {
    document.getElementById("descargaModal").classList.remove("hidden");
}

function closeModalDescarga() {
    document.getElementById("descargaModal").classList.add("hidden");
}
</script>
@endsection
