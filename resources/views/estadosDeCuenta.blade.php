@extends('layouts.user-simple')

@section('content')
<div class="w-full flex flex-col min-h-screen">

    {{-- Header Minimalista (Proporciones Exactas de Referencia) --}}
    <nav class="flex justify-between items-center mb-16 px-2">
        <a href="{{ route('user.dashboard') }}"
            class="group flex items-center gap-4 text-[10px] tracking-[0.4em] uppercase text-white/40 hover:text-dorado-400 transition-all duration-700">
            <span class="text-lg group-hover:-translate-x-2 transition-transform duration-500">←</span>
            <span class="text-[#d8c495]">Volver al Panel</span>
        </a>
        <div class="h-px flex-1 mx-10 bg-linear-to-r from-[#8B6B23]/40 to-transparent"></div>
        <span class="text-[9px] text-dorado-400 tracking-[0.5em] uppercase opacity-70">
            MB Signature Properties •
        </span>
    </nav>

    {{-- Hero Section (Proporciones Exactas de Referencia) --}}
    <header class="mb-20 px-2">
        <div class="flex items-baseline gap-4">
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none uppercase">
                Rendimientos
            </h1>
        </div>
        <p class="text-white/50 text-s tracking-[0.3em] uppercase mt-6 ml-12">
            Historial detallado y rendimiento de activos
        </p>
    </header>

    {{-- Dashboard de Estados de Cuenta --}}
    <div class="w-full px-2 mb-20">
        <div class="flex flex-col gap-8 bg-transparent">

            {{-- ISLA 1: BUSCADOR Y ACCIONES --}}
            <div class="rounded-3xl border border-dorado-400/20 bg-[#0d1f30]/75 backdrop-blur-md p-6 md:p-8 lg:p-10 shadow-xl shadow-black/20">

                <div class="flex flex-col xl:flex-row items-end justify-between gap-8">

                    <form method="GET" action="{{ route('estados-cuenta.index') }}"
                        class="flex-1 flex flex-col lg:flex-row items-end gap-5 md:gap-6 w-full">
                        @csrf

                        <div class="flex-1 w-full">
                            <label class="block text-[11px] font-bold uppercase tracking-[0.2em] text-dorado-400 mb-3">
                                Filtrar Documentos
                            </label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Escribir..."
                                class="w-full bg-black/25 border border-dorado-400/20 rounded-xl py-3.5 px-4 text-lg md:text-xl text-white font-light focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-all uppercase tracking-tight placeholder:text-white/35">
                        </div>

                        <div class="w-full lg:w-48">
                            <label class="block text-[11px] font-bold uppercase tracking-[0.2em] text-dorado-400 mb-3">
                                Categoria
                            </label>
                            <div class="relative">
                                <select name="categoria"
                                    class="w-full bg-black/25 border border-dorado-400/20 rounded-xl py-3.5 pl-4 pr-10 text-base text-white font-light focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 appearance-none cursor-pointer transition-all">
                                    <option value="mes" {{ request('categoria') == 'mes' ? 'selected' : '' }}>Mes</option>
                                    <option value="estado" {{ request('categoria') == 'estado' ? 'selected' : '' }}>Estado</option>
                                    <option value="id" {{ request('categoria') == 'id' ? 'selected' : '' }}>ID</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-dorado-400/70">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 w-full lg:w-auto">
                            <button type="submit"
                                class="bg-carbon-900 text-white text-xs md:text-sm tracking-[0.2em] uppercase font-bold px-6 md:px-8 py-3.5 rounded-xl hover:bg-dorado-400 hover:text-carbon-900 transition-all duration-300">
                                Buscar
                            </button>
                            <a href="{{ route('estados-cuenta.limpiar') }}"
                                class="flex items-center justify-center border border-dorado-400/25 text-white/70 text-xs md:text-sm tracking-[0.2em] uppercase font-bold px-6 md:px-8 py-3.5 rounded-xl hover:border-dorado-400 hover:text-dorado-400 transition-all duration-300">
                                Limpiar
                            </a>
                        </div>
                    </form>

                    <div class="w-full xl:w-auto border-t xl:border-t-0 xl:border-l border-dorado-400/10 pt-6 xl:pt-0 xl:pl-8 flex items-end">
                        <button onClick="openModalDescarga()"
                            class="w-full xl:w-auto bg-dorado-400 text-carbon-900 text-xs md:text-sm tracking-[0.2em] uppercase font-bold px-8 py-3.5 rounded-xl hover:bg-[#b58714] transition-all duration-300 shadow-md hover:shadow-xl flex items-center justify-center gap-3">
                            <svg style="width: 16px; height: 16px;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            <span>Imprimir</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="tabla-dorada-container rounded-3xl overflow-hidden">
                <div class="overflow-x-auto custom-scroll">
                    <table class="tabla-dorada">
                        <thead>
                            <tr>
                                <th class="text-left pl-6">ID</th>
                                <th class="text-left">Proyecto</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-right pr-6">Saldo Neto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cuentas as $cuenta)
                            <tr>
                                <td class="text-left pl-6 font-bold text-white/40">
                                    #{{ $cuenta->id_cuentas_por_pagar }}
                                </td>

                                <td class="font-bold text-white uppercase">
                                    {{ $cuenta->proyecto ?? 'Sin proyecto' }}
                                </td>

                                <td class="text-center">
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border bg-dorado/10 text-dorado-400 border-dorado/20">
                                        {{ ucfirst($cuenta->estado) }}
                                    </span>
                                </td>

                                <td class="text-center text-xs font-medium text-white/65 uppercase tracking-wide">
                                    {{ $cuenta->mes_pago ?? '-' }}
                                </td>

                                <td class="text-right pr-6 font-bold text-dorado-400 font-mono text-lg">
                                    ${{ number_format($cuenta->saldo_neto, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5"
                                    class="py-16 text-center text-white/40 text-xs uppercase tracking-widest font-bold">
                                    No hay facturas registradas
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-dorado-400/20 bg-[#0d1f30]/75 backdrop-blur-sm p-6 md:p-8 flex justify-center md:justify-start">
                <button onClick="openModal()"
                    class="w-full md:w-auto bg-dorado-400 text-carbon-900 text-xs md:text-sm tracking-[0.2em] uppercase font-bold px-12 md:px-16 py-4 rounded-xl hover:bg-[#b58714] transition-all duration-300 shadow-md hover:shadow-xl">
                    Visualizar graficas
                </button>
            </div>

        </div>
    </div>
    {{-- Modales (Gráficas e Impresión) --}}
    <div id="chartsmModal" class="fixed inset-0 z-50 flex items-center justify-center hidden p-6">
        <div class="absolute inset-0 bg-black/95 backdrop-blur-xl" onclick="closeModal()"></div>
        <div
            class="bg-carbon-900 border border-white/5 w-full max-w-4xl max-h-[90vh] overflow-y-auto relative z-10 shadow-3xl">
            <div class="p-12 md:p-20 text-white">
                <div class="flex justify-between items-start mb-16 border-b border-white/5 pb-8">
                    <h2 class="text-5xl font-extralight uppercase tracking-tighter text-white">Reporte<br><span
                            class="text-dorado-400 font-bold">Gráfico</span></h2>
                    <button onclick="closeModal()"
                        class="text-white/20 hover:text-white text-4xl font-light">&times;</button>
                </div>
                <div class="flex flex-col gap-12">
                    <div class="flex items-center gap-8">
                        <label class="text-[10px] tracking-[0.4em] text-dorado-400 uppercase font-bold">Año:</label>
                        <select id="filtroYear" onchange="cargarGraficaAnual()"
                            class="bg-transparent border-b border-white/10 py-2 text-2xl text-white outline-none">
                            @for($y = $minYear; $y <= now()->year; $y++)
                                <option value="{{ $y }}" class="bg-carbon-900">{{ $y }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="bg-black/40 p-8 border border-white/5">
                        <canvas id="graficaAnual"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="descargaModal" class="fixed inset-0 z-50 flex items-center justify-center hidden p-6">
        <div class="absolute inset-0 bg-black/95 backdrop-blur-xl" onclick="closeModalDescarga()"></div>
        <div class="bg-carbon-900 border border-white/5 w-full max-w-2xl relative z-10 shadow-3xl p-12 md:p-20">
            <h2 class="text-white text-4xl font-extralight uppercase tracking-tighter mb-12">Opciones de<br><span
                    class="text-dorado-400 font-bold">Descarga</span></h2>
            <form action="{{ route('estados-cuenta.pdf') }}" method="POST" class="space-y-12">
                @csrf
                <div class="group">
                    <label
                        class="block text-[9px] uppercase tracking-[0.3em] text-dorado-400 mb-4 opacity-60 font-bold">Desde</label>
                    <input type="date" name="desde"
                        class="w-full bg-transparent border-b border-white/10 py-4 text-xl text-white outline-none focus:border-dorado-400 transition-all">
                </div>
                <div class="group">
                    <label
                        class="block text-[9px] uppercase tracking-[0.3em] text-dorado-400 mb-4 opacity-60 font-bold">Hasta</label>
                    <input type="date" name="hasta"
                        class="w-full bg-transparent border-b border-white/10 py-4 text-xl text-white outline-none focus:border-dorado-400 transition-all">
                </div>
                <div class="flex justify-end gap-6 pt-10">
                    <button type="button" onclick="closeModalDescarga()"
                        class="text-white/40 text-[10px] tracking-[0.3em] uppercase font-bold px-10 py-4">Cerrar</button>
                    <button type="submit"
                        class="bg-dorado-400 text-black text-[10px] tracking-[0.3em] uppercase font-bold px-12 py-5 hover:bg-white transition-all duration-700 shadow-xl">Generar
                        PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let graficaAnual = null;

function openModal() {
    document.getElementById("chartsmModal").classList.remove("hidden");
    cargarGraficaAnual();
}

function closeModal() {
    document.getElementById("chartsmModal").classList.add("hidden");
}

function openModalDescarga() {
    document.getElementById("descargaModal").classList.remove("hidden");
}

function closeModalDescarga() {
    document.getElementById("descargaModal").classList.add("hidden");
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
            datasets: [{
                label: "Pagados",
                data: Pagados,
                backgroundColor: "#D4A017"
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    ticks: {
                        color: 'rgba(255,255,255,0.4)'
                    },
                    grid: {
                        color: 'rgba(255,255,255,0.05)'
                    }
                },
                x: {
                    ticks: {
                        color: 'rgba(255,255,255,0.4)'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}
</script>
@endsection
