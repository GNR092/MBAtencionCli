@extends('layouts.admin')

@section('content')
        <h1 class="text-center text-black text-3xl p-2">IMPUESTOS</h1>
        <div class="p-6">
    <!-- Encabezado con título y barra de búsqueda -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <form method="GET" action="{{ route('inpuestos') }}" class="relative mx-12 flex items-center gap-2">
            @csrf
            <label for="searchInput" class="text-black mx-2">Buscar por:</label>

            <!-- Input de búsqueda -->
            <input 
                type="text" 
                id="searchInput"
                name="search" 
               value="{{ request('search') }}"
                placeholder="Buscar..." 
                class="w-full sm:w-64 px-4 py-2 rounded-lg border border-gray-400 bg-[#eee]"
            >

            <!-- Categoría -->
            <select name="categoria" id="categoria" class="bg-[#eee] p-3 rounded-lg mx-2 border border-gray-400 text-black">
            <option value="proyecto" {{ request('categoria') == 'proyecto' ? 'selected' : '' }}>Proyecto</option>
            <option value="departamento" {{ request('categoria') == 'departamento' ? 'selected' : '' }}>Departamento</option>
            <option value="inversionista" {{ request('categoria') == 'inversionista' ? 'selected' : '' }}>inversionista</option>
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
        </form>
             <!-- Botón descargar -->
            <button type="submit" onClick="openModalDescarga()"
            class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 mx-8 rounded">
            Descargar
            </button>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="w-full text-sm text-center border-collapse">
            <thead class="bg-[#2f2f2f] text-[#fff] uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-3 border-b">Factura</th>
                    <th class="px-6 py-3 border-b">UIID</th>
                    <th class="px-6 py-3 border-b">Fecha</th>
                    <th class="px-6 py-3 border-b">Proyecto</th>
                    <th class="px-6 py-3 border-b">Departamento</th>
                    <th class="px-6 py-3 border-b">Inversionista</th>
                    <th class="px-6 py-3 border-b">Tipo factor</th>
                    <th class="px-6 py-3 border-b">Regimen fiscal</th>
                    <th class="px-6 py-3 border-b">Importe base</th>
                    <th class="px-6 py-3 border-b">Importe ISR</th>
                    
                </tr>
            </thead>
            <tbody id="tableBody" class="divide-y divide-[#eee]">
                <tr class="hover:bg-gray-100 transition">
                    @forelse($xmlFiles as $file)
                    <td class="px-6 py-4">{{ $file->id }}</td>
                    <td class="px-6 py-4">{{ $file->uuid }}</td>
                    <td class="px-6 py-4">{{ $file->created_at }}</td>
                    <td class="px-6 py-4">{{ $file->proyectos }}</td>
                    <td class="px-6 py-4">{{ $file->departamento }}</td>
                    <td class="px-6 py-4">{{ $file->emisor_name }}</td>
                    <td class="px-6 py-4">{{ $file->tipoFactor }}</td>
                    <td class="px-6 py-4">
                            @if ($file->tasaCuota == '0.0125000000')
                                RESICO
                            @elseif ($file->tasaCuota == '0.0100000000')
                                ARRENDAMIENTO
                            @else
                                PERSONA MORAL
                            @endif
                    </td>
                    <td class="px-6 py-4">${{number_format($file->importeBase,2) }}</td>
                   <td class="px-6 py-4">${{number_format($file->isr,2) }}</td>

                    
                </tr>
                                     @empty
                <tr>
                    <td colspan="5">No se encontraron facturas</td>
                </tr>
                @endforelse
                
                <tr class="bg-gray-300 font-bold">
                    <td colspan="9" class="text-right px-6 py-3">TOTAL BASE:</td>
                    <td class="px-6 py-3 text-[#20157e]">
                        ${{ number_format($totalBase, 2) }}
                    </td>
                </tr>
                <tr class="bg-gray-300 font-bold">
                    <td colspan="9" class="text-right px-6 py-3">TOTAL ISR RETENIDO:</td>
                    <td class="px-6 py-3 text-[#20157e]">
                        ${{ number_format($totalISR, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-lg shadow p-2 ">
                {{ $xmlFiles->links('pagination::tailwind') }}
            </div>
        </div>
    </div>

    <!--modal para la descarga-->
    <div id="descargaModal" class="bg-white/30 backdrop-blur-sm fixed inset-0 bg-opacity-50 flex items-center justify-center hidden">
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
                        <input 
                            type="date" 
                            name="desde"
                            id="desde"
                            placeholder="dd/mm/aaaa"
                            class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200" 
                            >
                    </div>
                    <!--fecha de terminacion-->
                    <div class="mb-6 mt-2">
                        <label class="text-black">
                            Hasta
                        </label>
                        <input 
                            type="date" 
                            name="hasta"
                            id="hasta"
                            placeholder="dd/mm/aaaa"
                            class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200" 
                            >
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeModalDescarga()" class="bg-gray-300 px-4 py-2 rounded">Cerrar</button>

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
    function openModalDescarga(){
        document.getElementById("descargaModal").classList.remove("hidden");
    }
    function closeModalDescarga(){
        document.getElementById("descargaModal").classList.add("hidden");
    }
</script>
@endsection