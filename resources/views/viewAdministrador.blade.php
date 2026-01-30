@extends('layouts.admin')

@section('content')
        <h1 class="text-center text-black text-3xl p-2"> CUENTAS POR PAGAR</h1>
<div class="max-w-6xl mx-auto p-6">
  
    <!-- Encabezado con título y barra de búsqueda -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <form method="GET" action="{{ route('viewAdministrador') }}" class="relative mx-12 flex items-center gap-2">
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
            <option value="name" {{ request('categoria') == 'name' ? 'selected' : '' }}>Inversionista</option>
            </select>

            <!-- Botón -->
            <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 rounded mx-2">
                BUSCAR
            </button>

            <!-- Botón limpiar filtros -->
            <a href="{{ route('viewAdministrador.limpiar') }}"
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
                    <th class="px-6 py-3 border-b">ID</th>
                    <th class="px-6 py-3 border-b">Inversionista</th>
                    <th class="px-6 py-3 border-b">Proyecto</th>
                    <th class="px-6 py-3 border-b">Estado</th> 
                    <th class="px-6 py-3 border-b">Mes</th> 
                    <th class="px-6 py-3 border-b">Importe Base</th>
                    <th class="px-6 py-3 border-b">Importe ISR</th>
                    <th class="px-6 py-3 border-b">Saldo neto</th>
                    <th class="px-6 py-3 border-b">Monto pagado</th>
                    <th class="px-6 py-3 border-b">Saldo Pendiente</th>
                </tr>
            </thead>
            <tbody id="tableBody" class="divide-y divide-[#eee]">
                <tr class="hover:bg-gray-100 transition">
                      @forelse($cuentas as $cuenta)
                    <td class="px-6 py-4">{{$cuenta->id_cuentas_por_pagar }}</td>
                    <td class="px-6 py-4">{{$cuenta->name}}</td>
                    <td class="px-6 py-4">{{$cuenta->proyecto}}</td>
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
                    <td class="px-6 py-4">${{number_format($cuenta->monto_pagado,2)}}</td>
                    <td class="px-6 py-4">${{number_format($cuenta->saldo_pendiente,2)}}</td>
                 </tr>
                                     @empty
                <tr>
                    <td colspan="5">No se encontraron facturas</td>
                </tr>
                @endforelse
                <tr class="bg-gray-300 font-bold">
                    <td colspan="9" class="text-right px-6 py-3">TOTAL CUENTAS POR PAGAR:</td>
                    <td class="px-6 py-3 text-[#2f2f2f]">
                        ${{ number_format($totalPendiente, 2) }}
                    </td>
                </tr>
                                <tr class="bg-gray-300 font-bold">
                    <td colspan="9" class="text-right px-6 py-3">TOTAL CUENTAS PAGADAS:</td>
                    <td class="px-6 py-3 text-[#2f2f2f]">
                        ${{ number_format($totalPagado, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-lg shadow p-2 ">
                {{ $cuentas->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
    <!-- boton modal charts -->
    <div >
        <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-4 py-2 rounded mx-100 mt-5" onClick="openModal()">
            Mostrar gráficas
        </button>
    </div>

    <!---modal-->
<div id="chartsmModal" class="bg-white/30 backdrop-blur-sm fixed inset-0 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 relative mx-auto flex max-h-[90vh] w-[800px] flex-col overflow-y-scroll">
        <h2 class="text-lg font-bold mb-4">Gráficas</h2>

        <!-- FILTROS -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label>Año:</label>
                <select id="filtroYear" class="border px-2 py-1 rounded text-black" onchange="cargarGraficaAnual()">
                    @for($y = 2023; $y <= now()->year; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="font-bold">Seleccionar proyecto:</label>
                <select id="selectProyecto" class="border px-2 py-1 rounded text-black">
                    <option value="">-- Seleccione --</option>
                    @foreach ($proyectos as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <!-- GRÁFICA ANUAL -->
        <h3 class="font-bold mt-4">Anual</h3>
        <canvas id="graficaAnual" height="200"></canvas>

        <!-- GRÁFICA ANUAL POR PROYECTO -->
        <h3 class="text-xl font-bold mt-6">Gráfica anual por proyecto</h3>
        <canvas id="graficaProyecto" height="200"></canvas>

        <div class="flex justify-end gap-2 mt-4">
            <button type="button" onclick="closeModal()" class="bg-gray-300 px-4 py-2 rounded">Cerrar</button>
        </div>
    </div>
</div>

<!--modal para la impresion-->
<div id="descargaModal" class="bg-white/30 backdrop-blur-sm fixed inset-0 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 relative mx-auto flex max-h-[90vh] w-[800px] flex-col">
        <h2>
            Defina las opciones para la descarga
        </h2>
        <div>
            <form action="{{ route('viewAdministrador.export') }}" method="POST">
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
                <!--selccion de estado-->
                <div class="mb-6 mt-2">
                    <label class="text-black">
                        Estado
                    </label>
                    <select name="estado" id="estado" class="p-1 mt-1 block w-full border border-gray-300 rounded-md shadow-sm bg-gray-200 text-black" >
                        <option value="">-- Seleccione --</option>
                        <option value="pagado">Pagado</option>
                        <option value="parcial">Parcial</option>
                        <option value="pendiente">Pendiente
                    </select>
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
<!--modal para el estado-->
<script >
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.estado-select');

        selects.forEach(select => {
            select.addEventListener('change', function() {
                const id = this.getAttribute('data-id');
                const estado = this.value;

                fetch(`/cuentasporpagar/${id}/estado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ estado })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Estado actualizado correctamente');
                        location.reload();
                    } else {
                        alert('Error al actualizar el estado');
                    }
                })
                .catch(err => console.error(err));
            });
        });
    });
</script>

<!--modal para las graficas-->
<script>
    let graficaAnual = null;
    let graficaProyecto = null;

    // Abrir modal
    function openModal() {
        document.getElementById("chartsmModal").classList.remove("hidden");

        cargarGraficaAnual(); // Cargar primera gráfica
    }

    //  Cerrar modal
    function closeModal() {
        document.getElementById("chartsmModal").classList.add("hidden");
    }

    // ===============================
    //      GRAFICA ANUAL GENERAL
    // ===============================
    async function cargarGraficaAnual() {
        const year = document.getElementById("filtroYear").value;

        const resp = await fetch(`/cuentas/grafica-anual/${year}`);
        const datos = await resp.json();

        const labels = datos.map(x => x.mes);
        const pagados = datos.map(x => x.pagados);
        const noPagados = datos.map(x => x.no_pagados);

        const ctx = document.getElementById("graficaAnual");

        if (graficaAnual) graficaAnual.destroy();

        graficaAnual = new Chart(ctx, {
            type: "bar",
            data: {
                labels,
                datasets: [
                    { label: "Pagados", data: pagados, backgroundColor: "#2f2f2f" },
                    { label: "No Pagados", data: noPagados, backgroundColor: "#c9a143" }
                ]
            },
            options: {
                scales: {
                    y: {
                        ticks: {
                            callback(value) {
                                return "$" + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return context.dataset.label + ": $" + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });


        
    }



    // ===============================
    //   GRAFICA ANUAL POR PROYECTO
    // ===============================

    // Evento al cambiar el proyecto
    document.addEventListener("DOMContentLoaded", function () {
        const selectProyecto = document.getElementById("selectProyecto");

        if (selectProyecto) {
            selectProyecto.addEventListener("change", cargarGraficaProyecto);
        }
    });

    async function cargarGraficaProyecto() {
        const year = document.getElementById("filtroYear").value;
        const proyecto = document.getElementById("selectProyecto").value;

        if (!proyecto) return;

        const resp = await fetch(`/cuentas/grafica-anual-proyecto/${year}/${proyecto}`);
        const datos = await resp.json();

        const labels = datos.map(x => x.mes);
        const pagados = datos.map(x => x.pagados);
        const noPagados = datos.map(x => x.no_pagados);

        const ctx = document.getElementById("graficaProyecto");

        if (graficaProyecto) graficaProyecto.destroy();

        graficaProyecto = new Chart(ctx, {
            type: "bar",
            data: {
                labels,
                datasets: [
                    { label: "Pagados", data: pagados, backgroundColor: "#2f2f2f" },
                    { label: "No Pagados", data: noPagados, backgroundColor: "#c9a143" }
                ]
            },
            options: {
                scales:{
                    y:{
                        ticks:{
                            callback(value){
                                return "$" + value.toLocaleString();
                            }
                        }
                    }
                },
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

<!--modal para la impresion-->
<script>
    function openModalDescarga() {
        document.getElementById("descargaModal").classList.remove("hidden");
    }
    function closeModalDescarga() {
        document.getElementById("descargaModal").classList.add("hidden");
    }

</script>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection