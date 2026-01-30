@extends('layouts.user-simple')

@section('content')
        <a href="/vista-usuario" class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded mx-2">Regresar</a>
        <h1 class="text-center text-black text-3xl p-2"> ESTADOS DE CUENTA</h1>
<div class="max-w-6xl mx-auto p-6">
    <!-- Encabezado con título y barra de búsqueda -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <form method="GET" action="{{ route('estadosDeCuenta') }}" class="relative mx-12 flex items-center gap-2">
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
            <option value="mes" {{ request('categoria') == 'mes' ? 'selected' : '' }}>Mes</option>
            <option value="estado" {{ request('categoria') == 'estado' ? 'selected' : '' }}>Estado</option>
            <option value="id" {{ request('categoria') == 'id' ? 'selected' : '' }}>ID</option>
            </select>

            <!-- Botón -->
            <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 rounded mx-2">
                BUSCAR
            </button>

                        <!-- Botón limpiar filtros -->
            <a href="{{ route('estadosDeCuenta.limpiar') }}"
            class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded mx-2">
                LIMPIAR
            </a>


        </form>
       <button type="submit" onClick="openModalDescarga()"
        class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 rounded mx-2">
            imprimir
       </button>

    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="w-full text-sm text-center border-collapse">
            <thead class="bg-[#2f2f2f] text-[#fff] uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-3 border-b">ID</th>
                    <th class="px-6 py-3 border-b">Proyecto</th>
                    <th class="px-6 py-3 border-b">Estado</th>
                    <th class="px-6 py-3 border-b">Fecha</th>
                    <th class="px-6 py-3 border-b">Importe Base</th>
                    <th class="px-6 py-3 border-b">Importe ISR</th>
                    <th class="px-6 py-3 border-b">Saldo neto</th>
                </tr>
            </thead>
            <tbody id="tableBody" class="divide-y divide-[#eee]">
                @forelse($cuentas as $cuenta)
                <tr class="hover:bg-gray-100 transition">
                
                 <td class="px-6 py-4">{{$cuenta->id_cuentas_por_pagar }}</td>
                    <td class="px-6 py-4">{{$cuenta->proyectos}}</td>
                    <td class="px-6 py-4">
                        @if($cuenta->estado === 'parcial')
                            <select 
                                class="estado-select bg-yellow-100 border border-yellow-400 rounded px-2 py-1 text-sm text-yellow-900"
                                data-id="{{ $cuenta->id_cuentas_por_pagar }}"
                            >
                                <option value="parcial" {{ $cuenta->estado === 'parcial' ? 'selected' : '' }}>Parcial</option>
                                <option value="pagado" {{ $cuenta->estado === 'pagado' ? 'selected' : '' }}>Pagado</option>
                            </select>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded 
                                {{ $cuenta->estado === 'pendiente' ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800' }}">
                                {{ ucfirst($cuenta->estado) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ json_decode($cuenta->mesesdepago)->mes ?? 'Sin mes' }}</td>
                    <td class="px-6 py-4">${{number_format($cuenta->importe_base_final,2)}}</td>
                    <td class="px-6 py-4">${{number_format($cuenta->isr,2)}}</td>
                    <td class="px-6 py-4">${{number_format($cuenta->saldo_neto,2)}}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">No se encontraron facturas</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

                <!-- boton modal charts -->
    <div >
        <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 rounded mx-100 mt-5" onClick="openModal()">
            Mostrar gráficas
        </button>
    </div>

    <!--modal graficas-->
    <div id="chartsmModal" class="bg-white/30 backdrop-blur-sm fixed inset-0 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 relative mx-auto flex max-h-[90vh] w-[800px] flex-col overflow-y-scroll">
            <h2 class="text-lg font-bold mb-4">Gráficas</h2>

        <!-- FILTROS -->
            <div >
                <div>
                    <label>Año:</label>
                    <select id="filtroYear" class="border px-2 py-1 rounded text-black" onchange="cargarGraficaAnual()">
                        @for($y = 2023; $y <= now()->year; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>

                    <!-- GRÁFICA ANUAL -->
                    <h3 class="font-bold mt-4">Anual</h3>
                    <canvas id="graficaAnual" height="100%" width="100%"></canvas>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="closeModal()" class="bg-gray-300 px-4 py-2 rounded">Cerrar</button>
            </div>
        </div >
    </div>

    <!--modal para la impresion-->
    <div id="descargaModal" class="bg-white/30 backdrop-blur-sm fixed inset-0 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 relative mx-auto flex max-h-[90vh] w-[800px] flex-col">
            <h2>
                Defina las opciones para la descarga
            </h2>
            <div>
            <form action="{{ route('estadoCuenta.descargarPdf') }}" method="POST">
                @csrf

                <input type="hidden" name="id_usuario" value="{{ $usuario->id }}">

                <div class="mb-6 mt-2">
                    <label class="text-black">Desde</label>
                    <input type="date" name="desde" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200">
                </div>

                <div class="mb-6 mt-2">
                    <label class="text-black">Hasta</label>
                    <input type="date" name="hasta" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200">
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeModalDescarga()" class="bg-gray-300 px-4 py-2 rounded">Cerrar</button>
                    <button type="submit" class="bg-[#c9a143] text-white px-4 py-2 rounded">Descargar PDF</button>
                </div>

            </form>

            </div>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!--script para las graficas-->
<script >
    let graficaAnual = null;

    function openModal() {
        document.getElementById("chartsmModal").classList.remove("hidden");
        cargarGraficaAnual();
    }
    function closeModal() {
        document.getElementById("chartsmModal").classList.add("hidden");
    }

    async function cargarGraficaAnual() {
        const year = document.getElementById("filtroYear").value;

        const resp = await fetch(`/estados-de-cuenta/grafica-anual-pagados/${year}`);
        const datos = await resp.json();
        const labels = datos.map(x => x.mes);
        const Pagados = datos.map(x => x.pagados);

        const ctx = document.getElementById("graficaAnual");

        if (graficaAnual) graficaAnual.destroy();

            graficaAnual = new Chart(ctx, {
            type: "bar",
            data: {
                labels,
                datasets: [
                    { label: "Pagados", data: Pagados, backgroundColor: "#c9a143" }
                ]
            },
            options: {
                plugins:{
                    tooltip:{
                        callbacks:{
                            label(context){
                                return context.dataset.label + ": $" + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
</script>
<!--script modal-->
<script>
    function openModalDescarga() {
        document.getElementById("descargaModal").classList.remove("hidden");
    }
    function closeModalDescarga() {
        document.getElementById("descargaModal").classList.add("hidden");
    }
</script>
@endsection