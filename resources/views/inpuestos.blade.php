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
        <form method="GET" action="{{ route('inpuestos') }}" class="relative mx-12 flex items-center gap-2">
            @csrf
            <label for="searchInput" class="text-white mx-2">Buscar por:</label>

            <!-- Input de búsqueda -->
            <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Buscar..."
                class="w-full sm:w-64 px-4 py-2 rounded-lg border border-gray-400 bg-[#eee]">

            <!-- Categoría -->
            <select name="categoria" id="categoria"
                class="bg-[#eee] p-3 rounded-lg mx-2 border border-gray-400 text-black">
                <option value="proyecto" {{ request('categoria') == 'proyecto' ? 'selected' : '' }}>Proyecto</option>
                <option value="departamento" {{ request('categoria') == 'departamento' ? 'selected' : '' }}>Departamento
                </option>
                <option value="inversionista" {{ request('categoria') == 'inversionista' ? 'selected' : '' }}>
                    inversionista</option>
            </select>

            <!-- Botón -->
            <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 rounded mx-2">
                BUSCAR
            </button>

            <!-- Botón limpiar filtros -->
            <a href="{{ route('inpuestos.limpiar') }}"
                class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded mx-2">
                LIMPIAR
            </a>

            <!-- Botón descargar -->
            <button type="submit" onClick="openModalDescarga()"
                class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 mx-2 rounded">
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
                        <td>Proyecto_ficticio</td>
                        <td>{{ $file->departamento }}</td>
                        <td>{{ $file->emisor_name }}</td>
                        <td>{{ $file->tipoFactor }}</td>
                        <td>
                            @if ($file->tasaCuota == '0.0125000000')
                            <span
                                class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-bold">RESICO</span>
                            @elseif ($file->tasaCuota == '0.0100000000')
                            <span
                                class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full font-bold">ARRENDAMIENTO</span>
                            @else
                            <span
                                class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full font-bold">MORAL</span>
                            @endif
                        </td>
                        <td class="font-medium">${{ number_format($file->importeBase, 2) }}</td>
                        <td class="font-medium text-red-600">${{ number_format($file->isr, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-10 text-carbon-900 font-medium italic">
                            No se encontraron facturas
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="9" class="text-right px-6 py-3 uppercase text-xs tracking-wider">TOTAL BASE:</td>
                        <td class="px-4 py-3 font-black text-carbon-900">
                            ${{ number_format($totalBase, 2) }}
                        </td>
                    </tr>
                    <tr class="border-t border-carbon-900/20">
                        <td colspan="9" class="text-right px-6 py-3 uppercase text-xs tracking-wider">TOTAL ISR
                            RETENIDO:</td>
                        <td class="px-4 py-3 font-black text-red-700">
                            ${{ number_format($totalISR, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="bg-gray-50 border-t border-carbon-200 p-4 flex justify-center">
            {{ $xmlFiles->links('pagination::tailwind') }}
        </div>
    </div>

    <!--modal para la descarga-->
    <div id="descargaModal"
        class="bg-white/30 backdrop-blur-sm fixed inset-0 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 relative mx-auto flex max-h-[90vh] w-[800px] flex-col">
            <h2>
                Defina las opciones para la descarga
            </h2>
            <div>
                <form action="{{ route('inpuestos.export') }}" method="POST">
                    @csrf
                    <!--fecha de inicio-->
                    <div class="mb-6 mt-2">
                        <label class="text-black">
                            Desde
                        </label>
                        <input type="date" name="desde" id="desde" placeholder="dd/mm/aaaa"
                            class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200">
                    </div>
                    <!--fecha de terminacion-->
                    <div class="mb-6 mt-2">
                        <label class="text-black">
                            Hasta
                        </label>
                        <input type="date" name="hasta" id="hasta" placeholder="dd/mm/aaaa"
                            class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200">
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="closeModalDescarga()"
                            class="bg-gray-300 px-4 py-2 rounded">Cerrar</button>

                        <button type="submit" class="bg-[#c9a143] text-white px-4 py-2 rounded">
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