@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado text-sm font-serif italic">|</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                Cuentas por pagar<span class="font-light text-dorado"></span><span
                    class="text-dorado animate-pulse">_</span>
            </h1>
        </div>
    </header>

    <div class="max-w-7xl mx-auto space-y-6">

        <div class="bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-lg border border-gray-200">
            <form method="GET" action="{{ route('viewAdministrador') }}"
                class="flex flex-col lg:flex-row gap-4 items-end lg:items-center">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-1 gap-4 w-full">
                    <div class="flex flex-col flex-1">
                        <label for="searchInput" class="text-xs font-bold text-gray-600 mb-1 ml-1">BUSCAR POR:</label>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}"
                            placeholder="Nombre o ID..."
                            class="w-full px-4 py-2 rounded-xl border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-[#d8c495] outline-none transition">
                    </div>

                    <div class="flex flex-col w-full sm:w-44">
                        <label for="categoria" class="text-xs font-bold text-gray-600 mb-1 ml-1">CATEGORÍA:</label>
                        <select name="categoria" id="categoria"
                            class="w-full p-2.5 rounded-xl border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-[#d8c495] outline-none">
                            <option value="mes" {{ request('categoria') == 'mes' ? 'selected' : '' }}>Mes</option>
                            <option value="estado" {{ request('categoria') == 'estado' ? 'selected' : '' }}>Estado
                            </option>
                            <option value="name" {{ request('categoria') == 'name' ? 'selected' : '' }}>Inversionista
                            </option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 mt-auto">
                        <button type="submit"
                            class="flex-1 lg:flex-none bg-[#3c3c3c] text-[#d8c495] px-6 py-2.5 rounded-xl font-bold hover:bg-black transition shadow-md">
                            BUSCAR
                        </button>
                        <a href="{{ route('viewAdministrador.limpiar') }}"
                            class="flex-1 lg:flex-none bg-gray-200 text-gray-700 px-6 py-2.5 rounded-xl font-bold hover:bg-gray-300 transition text-center">
                            LIMPIAR
                        </a>
                    </div>
                </div>

                <button type="button" onClick="openModalDescarga()"
                    class="w-full lg:w-auto bg-[#d8c495] text-[#3c3c3c] px-8 py-2.5 rounded-xl font-bold hover:bg-[#c9a143] transition shadow-md">
                    DESCARGAR
                </button>
            </form>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-carbon">
            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-sm text-center border-collapse">
                    <thead
                        class="bg-gris-carbon text-dorado uppercase text-xs tracking-widest border-b-2 border-dorado">
                        <tr>
                            <th class="px-4 py-4">ID</th>
                            <th class="px-4 py-4">Inversionista</th>
                            <th class="px-4 py-4">Proyecto</th>
                            <th class="px-4 py-4">Estado</th>
                            <th class="px-4 py-4">Mes</th>
                            <th class="px-4 py-4">Base</th>
                            <th class="px-4 py-4">ISR</th>
                            <th class="px-4 py-4">Neto</th>
                            <th class="px-4 py-4">Pagado</th>
                            <th class="px-4 py-4">Pendiente</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-carbon">
                        @forelse($cuentas as $cuenta)
                        <tr class="text-gris-carbon hover:bg-gray-50 transition duration-200">
                            <td class="px-4 py-4 font-bold">{{ $cuenta->id_cuentas_por_pagar }}</td>
                            <td class="px-4 py-4 text-left font-medium">{{ $cuenta->name }}</td>
                            <td class="px-4 py-4">{{ $cuenta->proyecto }}</td>
                            <td class="px-4 py-4">
                                @if($cuenta->estado === 'parcial')
                                <select
                                    class="estado-select bg-yellow-50 border border-yellow-300 rounded-lg px-2 py-1 text-xs text-yellow-800 outline-none focus:ring-2 focus:ring-dorado"
                                    data-id="{{ $cuenta->id_cuentas_por_pagar }}">
                                    <option value="parcial" selected>Parcial</option>
                                    <option value="pagado">Pagado</option>
                                </select>
                                @else
                                <span
                                    class="px-3 py-1 text-[10px] font-black rounded-full uppercase
                            {{ $cuenta->estado === 'pendiente' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $cuenta->estado }}
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs">{{ json_decode($cuenta->mesesdepago)->mes ?? 'N/A' }}</td>
                            <td class="px-4 py-4 font-medium">${{ number_format($cuenta->importe_base_final,2) }}</td>
                            <td class="px-4 py-4 text-red-600 font-medium">${{ number_format($cuenta->isr,2) }}</td>
                            <td class="px-4 py-4 font-bold text-gris-carbon">${{ number_format($cuenta->saldo_neto,2) }}
                            </td>
                            <td class="px-4 py-4 text-green-700 font-medium">
                                ${{ number_format($cuenta->monto_pagado,2) }}</td>
                            <td class="px-4 py-4 font-black text-gris-carbon">
                                ${{ number_format($cuenta->saldo_pendiente,2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="py-10 text-carbon italic">No se encontraron registros activos.</td>
                        </tr>
                        @endforelse
                    </tbody>

                    <tfoot class="bg-carbon border-t-2 border-gris-carbon">
                        <tr class="font-bold text-gris-carbon">
                            <td colspan="9" class="text-right px-6 py-3 uppercase text-xs tracking-wider">Total
                                Pendiente:</td>
                            <td class="px-4 py-3 text-red-700">${{ number_format($totalPendiente, 2) }}</td>
                        </tr>
                        <tr class="font-bold text-gris-carbon border-t border-gris-carbon/20">
                            <td colspan="9" class="text-right px-6 py-3 uppercase text-xs tracking-wider">Total Pagado:
                            </td>
                            <td class="px-4 py-3 text-green-800">${{ number_format($totalPagado, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="w-full md:w-auto">
                <button type="button"
                    class="bg-[#3c3c3c] text-[#d8c495] px-8 py-3 rounded-xl font-bold hover:bg-black transition shadow-lg w-full md:w-auto"
                    onClick="openModal()">
                    MOSTRAR GRÁFICAS
                </button>
            </div>
            <div class="bg-white rounded-xl shadow p-2">
                {{ $cuentas->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>

<div id="chartsmModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-[9999] flex items-center justify-center hidden p-4">
    <div
        class="bg-white rounded-3xl shadow-2xl p-4 md:p-8 relative w-full max-w-4xl max-h-[90vh] overflow-y-auto custom-scroll">
        <h2 class="text-2xl font-bold mb-6 text-[#3c3c3c] border-b-2 border-[#d8c495] pb-2">Análisis de Cuentas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Año de consulta:</label>
                <select id="filtroYear"
                    class="w-full border p-3 rounded-xl outline-none focus:ring-2 focus:ring-[#d8c495]"
                    onchange="cargarGraficaAnual()">
                    @for($y = 2023; $y <= now()->year; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Filtrar por Proyecto:</label>
                <select id="selectProyecto"
                    class="w-full border p-3 rounded-xl outline-none focus:ring-2 focus:ring-[#d8c495]">
                    <option value="">-- Todos los proyectos --</option>
                    @foreach ($proyectos as $p)
                    <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="space-y-10">
            <div>
                <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 bg-[#d8c495] rounded-full"></span> Resumen Anual General
                </h3>
                <canvas id="graficaAnual"></canvas>
            </div>
            <div>
                <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 bg-[#3c3c3c] rounded-full"></span> Desempeño por Proyecto
                </h3>
                <canvas id="graficaProyecto"></canvas>
            </div>
        </div>

        <div class="sticky bottom-0 bg-white pt-6 mt-6 border-t flex justify-end">
            <button type="button" onclick="closeModal()"
                class="bg-gray-800 text-white px-8 py-2 rounded-xl font-bold">CERRAR</button>
        </div>
    </div>
</div>

<div id="descargaModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-[9999] flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl p-6 md:p-10 w-full max-w-md">
        <h2 class="text-xl font-bold mb-6 text-center text-[#3c3c3c]">Configuración de Reporte</h2>
        <form action="{{ route('viewAdministrador.export') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-500">DESDE</label>
                    <input type="date" name="desde" id="desde"
                        class="w-full p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-[#d8c495]">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-500">HASTA</label>
                    <input type="date" name="hasta" id="hasta"
                        class="w-full p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-[#d8c495]">
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-gray-500">ESTADO DE PAGO</label>
                <select name="estado" id="estado"
                    class="w-full p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-[#d8c495]">
                    <option value="">Todos</option>
                    <option value="pagado">Pagado</option>
                    <option value="parcial">Parcial</option>
                    <option value="pendiente">Pendiente</option>
                </select>
            </div>
            <div class="flex flex-col gap-3 pt-4">
                <button type="submit"
                    class="bg-[#c9a143] text-white py-3 rounded-xl font-bold shadow-lg hover:bg-[#b08b35] transition">
                    GENERAR EXCEL
                </button>
                <button type="button" onclick="closeModalDescarga()"
                    class="text-gray-500 font-bold hover:text-black transition">CANCELAR</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection