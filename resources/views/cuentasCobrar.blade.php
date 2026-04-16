@extends('layouts.user-simple')

@section('content')
<div class="w-full flex flex-col min-h-screen">

    {{-- Header Minimalista --}}
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

    {{-- Hero Section --}}
    <header class="mb-20 px-2">
        <div class="flex items-baseline gap-4">

            <h1 class="page-title">
                Cobrar Rentas
            </h1>
        </div>
        <p class="text-white/50 text-xs tracking-[0.3em] uppercase mt-6 ml-12">
            Estado financiero y control de inversiones
        </p>
    </header>

    {{-- Dashboard de Cuentas --}}
    <div class="w-full px-2 mb-20">
        <div class="flex flex-col gap-8 bg-transparent">

            {{-- BLOQUE 1: BUSCADOR --}}
            <div class="bg-[#112134]/60 backdrop-blur-md rounded-xl border border-[#d8c495]/20 p-8 md:p-10">
                <form method="GET" action="{{ route('cuentas-cobrar.index') }}"
                    class="flex flex-col lg:flex-row items-end gap-6">
                    @csrf

                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Buscar Inversionista
                        </label>
                        <div class="relative w-full">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="ESCRIBIR..."
                                class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg py-4 pl-4 pr-4 text-xl text-white font-light focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-all uppercase tracking-tight placeholder-white/20">
                        </div>
                    </div>

                    <div class="w-full lg:w-64">
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Filtrar por
                        </label>
                        <div class="relative">
                            <select name="categoria"
                                class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg py-4 pl-4 pr-10 text-lg text-white font-light focus:outline-none focus:border-[#d8c495] appearance-none cursor-pointer transition-all">
                                <option value="mes" {{ request('categoria') == 'mes' ? 'selected' : '' }}>MES</option>
                                <option value="estado" {{ request('categoria') == 'estado' ? 'selected' : '' }}>ESTADO</option>
                                <option value="id" {{ request('categoria') == 'id' ? 'selected' : '' }}>ID</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-[#d8c495]/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 w-full lg:w-auto">
                        <button type="submit"
                            class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg transition-all duration-300">
                            Buscar
                        </button>

                        <a href="{{ route('cuentas-cobrar.limpiar') }}"
                            class="flex items-center justify-center border border-[#d8c495]/30 text-[#d8c495]/60 text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg hover:border-[#d8c495] hover:text-[#d8c495] transition-all duration-300">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            {{-- BLOQUE 2: TABLA --}}
            <div class="tabla-dorada-container overflow-hidden">
                <div class="overflow-x-auto custom-scroll">
                    <table class="tabla-dorada">
                        <thead>
                            <tr>
                                <th class="text-left pl-6">ID</th>
                                <th class="text-left">Inversionista</th>
                                <th class="text-left">Proyecto</th>
                                <th class="text-center">Estado</th>
                                <th class="text-right">Saldo Neto</th>
                                <th class="text-right pr-6">Pendiente</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cuentas as $cuenta)
                            <tr>
                                <td class="text-left pl-6 font-bold text-[#d8c495]/50">
                                    #{{ $cuenta->id_cuentas_por_pagar }}
                                </td>
                                <td class="font-bold uppercase">
                                    {{ $cuenta->name }}
                                </td>
                                <td class="text-xs font-medium text-white/50 uppercase tracking-wide">
                                    {{ $cuenta->proyecto }}
                                </td>
                                <td class="text-center">
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border
                                    {{ $cuenta->estado === 'parcial'
                                        ? 'bg-dorado/10 text-dorado-400 border-dorado/20'
                                        : 'bg-red-900/40 text-red-400 border-red-400/30' }}">
                                        {{ ucfirst($cuenta->estado) }}
                                    </span>
                                </td>
                                <td class="text-right font-bold text-dorado-400 font-mono">
                                    ${{ number_format($cuenta->saldo_neto, 2) }}
                                </td>
                                <td class="text-right pr-6 font-bold font-mono">
                                    ${{ number_format($cuenta->saldo_pendiente, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6"
                                    class="py-16 text-center text-white/30 text-xs uppercase tracking-widest font-bold">
                                    No se encontraron registros
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-[#d8c495]/10 p-4 flex justify-center">
                    {{ $cuentas->links('pagination::tailwind') }}
                </div>
            </div>

            {{-- BLOQUE 3: FOOTER --}}
            <div>
                <button onclick="openModal()"
                    class="w-full md:w-auto bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] text-sm tracking-[0.2em] uppercase font-bold px-20 py-10 rounded-lg transition-all duration-300 shadow-md hover:shadow-xl">
                    Visualizar Gráficas
                </button>

                <div class="pagination-custom text-white/40">
                    {{ $cuentas->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Gráficas --}}
    <div id="chartsmModal" class="fixed inset-0 z-50 flex items-center justify-center hidden p-6">
        <div class="absolute inset-0 bg-black/95 backdrop-blur-xl"></div>
        <div
            class="bg-[#112134] border border-[#d8c495]/20 w-full max-w-5xl h-auto max-h-[90vh] overflow-y-auto relative z-10 shadow-2xl rounded-xl">
            <div class="p-12 md:p-20">
                <div class="flex justify-between items-start mb-16">
                    <h2 class="text-white text-5xl font-extralight uppercase tracking-tighter">Reporte<br><span
                            class="text-dorado-400 font-bold">Financiero</span></h2>
                    <button onclick="closeModal()"
                        class="text-white/20 hover:text-white text-4xl font-light transition-colors">&times;</button>
                </div>

                <div class="flex flex-col gap-12">
                    <div class="flex items-center gap-8">
                        <label class="text-[10px] tracking-[0.4em] text-dorado-400 uppercase">Año fiscal:</label>
                        <select id="filtroYear" onchange="cargarGraficaAnual()"
                            class="bg-transparent border-b border-white/10 py-2 text-2xl text-white outline-none">
                            @for($y = $minYear; $y <= now()->year; $y++)
                                <option value="{{ $y }}" class="bg-[#112134]">{{ $y }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="bg-black/40 p-10 border border-white/5 rounded-lg">
                        <canvas id="graficaAnual"></canvas>
                    </div>
                </div>
            </div>
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

async function cargarGraficaAnual() {
    const year = document.getElementById("filtroYear").value;
    const resp = await fetch(`/cuentas-por-cobrar/grafica-anual/${year}`);
    const datos = await resp.json();
    const labels = datos.map(x => x.mes);
    const noPagados = datos.map(x => x.no_pagados);
    const ctx = document.getElementById("graficaAnual");
    if (graficaAnual) graficaAnual.destroy();
    graficaAnual = new Chart(ctx, {
        type: "bar",
        data: {
            labels,
            datasets: [{
                label: "Cuentas Pendientes",
                data: noPagados,
                backgroundColor: "#D4A017"
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    ticks: { color: 'rgba(255,255,255,0.4)' },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                x: {
                    ticks: { color: 'rgba(255,255,255,0.4)' },
                    grid: { display: false }
                }
            }
        }
    });
}
</script>
@endsection
