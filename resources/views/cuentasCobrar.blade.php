@extends('layouts.user-simple')

@section('content')
    <div class="w-full flex flex-col min-h-screen">

        {{-- Header Minimalista (Proporciones Exactas de Referencia) --}}
        <nav class="flex justify-between items-center mb-16 px-2">
            <a href="/vista-usuario" class="group flex items-center gap-4 text-[10px] tracking-[0.4em] uppercase text-white/40 hover:text-[#D4A017] transition-all duration-700">
                <span class="text-lg group-hover:-translate-x-2 transition-transform duration-500">←</span>
                <span>Volver al Panel</span>
            </a>
            <div class="h-[1px] flex-1 mx-10 bg-gradient-to-r from-[#8B6B23]/40 to-transparent"></div>
            <span class="text-[9px] text-[#D4A017] tracking-[0.5em] uppercase opacity-70">
                MB Signature Properties •
            </span>
        </nav>

        {{-- Hero Section (Proporciones Exactas de Referencia) --}}
        <header class="mb-20 px-2">
            <div class="flex items-baseline gap-4">
                <span class="text-[#D4A017] text-sm font-serif italic">03</span>
                <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                    Cuentas por Cobrar<span class="font-light text-[#D4A017]"></span><span class="text-[#D4A017] animate-pulse">_</span>
                </h1>
            </div>
            <p class="text-white/20 text-xs tracking-[0.3em] uppercase mt-6 ml-12">
                Estado financiero y control de inversiones
            </p>
        </header>

        {{-- Dashboard de Cuentas --}}
        <div class="w-full px-2 mb-20">
            <div class="bg-[#1A1A1A]/80 backdrop-blur-3xl border border-white/5 p-12 md:p-20 shadow-2xl">

                {{-- Buscador Estilizado --}}
                <form method="GET" action="{{ route('cuentasCobrar') }}" class="flex flex-col lg:flex-row items-end gap-12 mb-16 border-b border-white/5 pb-12">
                    @csrf
                    <div class="flex-1 w-full">
                        <label class="block text-[9px] uppercase tracking-[0.3em] text-[#D4A017] mb-4 opacity-60">Buscar Inversionista</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="ESCRIBIR..."
                               class="w-full bg-transparent border-b border-white/10 py-4 text-2xl text-white font-light focus:border-[#D4A017] outline-none transition-all duration-700 uppercase tracking-tighter">
                    </div>

                    <div class="w-full lg:w-64">
                        <label class="block text-[9px] uppercase tracking-[0.3em] text-[#D4A017] mb-4 opacity-60">Filtrar por</label>
                        <select name="categoria" class="w-full bg-transparent border-b border-white/10 py-4 text-xl text-white font-light focus:border-[#D4A017] outline-none appearance-none cursor-pointer">
                            <option value="mes" {{ request('categoria') == 'mes' ? 'selected' : '' }} class="bg-[#1A1A1A]">MES</option>
                            <option value="estado" {{ request('categoria') == 'estado' ? 'selected' : '' }} class="bg-[#1A1A1A]">ESTADO</option>
                            <option value="id" {{ request('categoria') == 'id' ? 'selected' : '' }} class="bg-[#1A1A1A]">ID</option>
                        </select>
                    </div>

                    <div class="flex gap-4 w-full lg:w-auto">
                        <button type="submit" class="bg-white text-black text-[10px] tracking-[0.3em] uppercase font-bold px-10 py-4 hover:bg-[#D4A017] transition-all duration-700 shadow-xl">
                            Buscar
                        </button>
                        <a href="{{ route('cuentasCobrar.limpiar') }}" class="text-center border border-white/10 text-white/40 text-[10px] tracking-[0.3em] uppercase font-bold px-10 py-4 hover:text-white transition-all duration-700 flex items-center">
                            Limpiar
                        </a>
                    </div>
                </form>

                {{-- Tabla con Letras Más Grandes --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="border-b border-white/10">
                        <tr class="text-[11px] tracking-[0.4em] uppercase text-[#D4A017] opacity-80 font-bold">
                            <th class="px-6 py-10">ID</th>
                            <th class="px-6 py-10">Inversionista</th>
                            <th class="px-6 py-10">Proyecto</th>
                            <th class="px-6 py-10">Estado</th>
                            <th class="px-6 py-10 text-right">Saldo Neto</th>
                            <th class="px-6 py-10 text-right">Pendiente</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                        @forelse($cuentas as $cuenta)
                            <tr class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-10 text-xl text-white/20 font-light">{{ $cuenta->id_cuentas_por_pagar }}</td>
                                <td class="px-6 py-10 text-2xl text-white font-light tracking-tight uppercase">{{ $cuenta->name }}</td>
                                <td class="px-6 py-10 text-sm tracking-[0.2em] text-white/40 uppercase">{{ $cuenta->proyecto }}</td>
                                <td class="px-6 py-10">
                                        <span class="text-[10px] px-4 py-2 border tracking-[0.3em] uppercase font-bold {{ $cuenta->estado === 'parcial' ? 'border-[#D4A017] text-[#D4A017]' : 'border-red-900/40 text-red-500/60' }}">
                                            {{ ucfirst($cuenta->estado) }}
                                        </span>
                                </td>
                                <td class="px-6 py-10 text-3xl font-light text-right text-[#D4A017] tracking-tighter">${{ number_format($cuenta->saldo_neto, 2) }}</td>
                                <td class="px-6 py-10 text-3xl font-bold text-right text-white tracking-tighter">${{ number_format($cuenta->saldo_pendiente, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-32 text-center text-white/10 text-[11px] tracking-[0.6em] uppercase">No se encontraron registros</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación y Botón de Gráficas --}}
                <div class="mt-20 flex flex-col md:flex-row justify-between items-center gap-10">
                    <button onclick="openModal()" class="bg-[#D4A017] text-black text-[10px] tracking-[0.4em] uppercase font-bold px-12 py-6 hover:bg-white transition-all duration-700 shadow-2xl w-full md:w-auto">
                        Visualizar Gráficas
                    </button>

                    <div class="pagination-custom text-white">
                        {{ $cuentas->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal de Gráficas (Estilizado) --}}
        <div id="chartsmModal" class="fixed inset-0 z-50 flex items-center justify-center hidden p-6">
            <div class="absolute inset-0 bg-black/95 backdrop-blur-xl" onclick="closeModal()"></div>
            <div class="bg-[#1A1A1A] border border-white/5 w-full max-w-5xl h-auto max-h-[90vh] overflow-y-auto relative z-10 shadow-3xl">
                <div class="p-12 md:p-20">
                    <div class="flex justify-between items-start mb-16">
                        <h2 class="text-white text-5xl font-extralight uppercase tracking-tighter">Reporte<br><span class="text-[#D4A017] font-bold">Financiero</span></h2>
                        <button onclick="closeModal()" class="text-white/20 hover:text-white text-4xl font-light transition-colors">&times;</button>
                    </div>

                    <div class="flex flex-col gap-12">
                        <div class="flex items-center gap-8">
                            <label class="text-[10px] tracking-[0.4em] text-[#D4A017] uppercase">Año fiscal:</label>
                            <select id="filtroYear" onchange="cargarGraficaAnual()" class="bg-transparent border-b border-white/10 py-2 text-2xl text-white outline-none">
                                @for($y = 2023; $y <= now()->year; $y++)
                                    <option value="{{ $y }}" class="bg-[#1A1A1A]">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="bg-black/40 p-10 border border-white/5">
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
        function openModal() { document.getElementById("chartsmModal").classList.remove("hidden"); cargarGraficaAnual(); }
        function closeModal() { document.getElementById("chartsmModal").classList.add("hidden"); }

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
                    datasets: [{ label: "Cuentas Pendientes", data: noPagados, backgroundColor: "#D4A017" }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { ticks: { color: 'rgba(255,255,255,0.4)' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        x: { ticks: { color: 'rgba(255,255,255,0.4)' }, grid: { display: false } }
                    }
                }
            });
        }
    </script>
@endsection
