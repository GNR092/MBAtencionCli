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
                <span class="text-[#D4A017] text-sm font-serif italic">04</span>
                <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                    Estados de Cuenta<span class="text-[#D4A017] animate-pulse">_</span>
                </h1>
            </div>
            <p class="text-white/20 text-xs tracking-[0.3em] uppercase mt-6 ml-12">
                Historial detallado y rendimiento de activos
            </p>
        </header>

        {{-- Dashboard de Estados de Cuenta --}}
        <div class="w-full px-2 mb-20">
            <div class="bg-[#1A1A1A]/80 backdrop-blur-3xl border border-white/5 p-12 md:p-20 shadow-2xl">

                {{-- Buscador y Acciones Superiores --}}
                <div class="flex flex-col lg:flex-row items-end justify-between gap-12 mb-16 border-b border-white/5 pb-12">
                    <form method="GET" action="{{ route('estadosDeCuenta') }}" class="flex-1 flex flex-col md:flex-row items-end gap-8 w-full">
                        @csrf
                        <div class="flex-1 w-full">
                            <label class="block text-[9px] uppercase tracking-[0.3em] text-[#D4A017] mb-4 opacity-60">Filtrar Documentos</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="ESCRIBIR..."
                                   class="w-full bg-transparent border-b border-white/10 py-4 text-2xl text-white font-light focus:border-[#D4A017] outline-none transition-all duration-700 uppercase tracking-tighter">
                        </div>

                        <div class="w-full md:w-48">
                            <label class="block text-[9px] uppercase tracking-[0.3em] text-[#D4A017] mb-4 opacity-60">Categoría</label>
                            <select name="categoria" class="w-full bg-transparent border-b border-white/10 py-4 text-xl text-white font-light focus:border-[#D4A017] outline-none appearance-none cursor-pointer">
                                <option value="mes" {{ request('categoria') == 'mes' ? 'selected' : '' }} class="bg-[#1A1A1A]">MES</option>
                                <option value="estado" {{ request('categoria') == 'estado' ? 'selected' : '' }} class="bg-[#1A1A1A]">ESTADO</option>
                                <option value="id" {{ request('categoria') == 'id' ? 'selected' : '' }} class="bg-[#1A1A1A]">ID</option>
                            </select>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="bg-white text-black text-[10px] tracking-[0.3em] uppercase font-bold px-10 py-4 hover:bg-[#D4A017] transition-all duration-700 shadow-xl">
                                Buscar
                            </button>
                            <a href="{{ route('estadosDeCuenta.limpiar') }}" class="flex items-center text-white/40 text-[10px] tracking-[0.3em] uppercase font-bold px-8 py-4 border border-white/10 hover:text-white transition-all">
                                Limpiar
                            </a>
                        </div>
                    </form>

                    <button onClick="openModalDescarga()" class="bg-[#D4A017] text-black text-[10px] tracking-[0.4em] uppercase font-bold px-12 py-5 hover:bg-white transition-all duration-700 shadow-2xl">
                        Imprimir
                    </button>
                </div>

                {{-- Tabla con Letras Más Grandes --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="border-b border-white/10">
                        <tr class="text-[11px] tracking-[0.4em] uppercase text-[#D4A017] opacity-80 font-bold">
                            <th class="px-6 py-10">ID</th>
                            <th class="px-6 py-10">Proyecto</th>
                            <th class="px-6 py-10">Estado</th>
                            <th class="px-6 py-10">Fecha</th>
                            <th class="px-6 py-10 text-right">Saldo Neto</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-white">
                        @forelse($cuentas as $cuenta)
                            <tr class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-10 text-xl text-white/20 font-light italic">{{$cuenta->id_cuentas_por_pagar }}</td>
                                <td class="px-6 py-10 text-2xl font-light tracking-tight uppercase">{{$cuenta->proyectos}}</td>
                                <td class="px-6 py-10">
                                    @if($cuenta->estado === 'parcial')
                                        <select class="estado-select bg-transparent border border-[#D4A017] text-[#D4A017] text-[10px] tracking-widest uppercase px-3 py-2 outline-none cursor-pointer" data-id="{{ $cuenta->id_cuentas_por_pagar }}">
                                            <option value="parcial" class="bg-[#1A1A1A]" selected>Parcial</option>
                                            <option value="pagado" class="bg-[#1A1A1A]">Pagado</option>
                                        </select>
                                    @else
                                        <span class="text-[10px] px-4 py-2 border tracking-[0.3em] uppercase font-bold {{ $cuenta->estado === 'pendiente' ? 'border-red-900/40 text-red-500/60' : 'border-[#D4A017] text-[#D4A017]' }}">
                                                {{ ucfirst($cuenta->estado) }}
                                            </span>
                                    @endif
                                </td>
                                <td class="px-6 py-10 text-xl font-serif italic text-white/40 tracking-widest">{{ json_decode($cuenta->mesesdepago)->mes ?? '-' }}</td>
                                <td class="px-6 py-10 text-3xl font-bold text-right text-white tracking-tighter">${{number_format($cuenta->saldo_neto,2)}}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-32 text-center text-white/10 text-[11px] tracking-[0.6em] uppercase">No hay facturas registradas</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Botón de Gráficas --}}
                <div class="mt-20">
                    <button type="submit" class="bg-white/5 border border-white/10 text-white text-[10px] tracking-[0.4em] uppercase font-bold px-12 py-6 hover:bg-[#D4A017] hover:text-black transition-all duration-700 shadow-2xl" onClick="openModal()">
                        Mostrar gráficas
                    </button>
                </div>
            </div>
        </div>

        {{-- Modales (Gráficas e Impresión) --}}
        {{-- ... Estructura de modales integrada con el estilo de la referencia ... --}}

        <div id="chartsmModal" class="fixed inset-0 z-50 flex items-center justify-center hidden p-6">
            <div class="absolute inset-0 bg-black/95 backdrop-blur-xl" onclick="closeModal()"></div>
            <div class="bg-[#1A1A1A] border border-white/5 w-full max-w-4xl max-h-[90vh] overflow-y-auto relative z-10 shadow-3xl">
                <div class="p-12 md:p-20 text-white">
                    <div class="flex justify-between items-start mb-16 border-b border-white/5 pb-8">
                        <h2 class="text-5xl font-extralight uppercase tracking-tighter text-white">Reporte<br><span class="text-[#D4A017] font-bold">Gráfico</span></h2>
                        <button onclick="closeModal()" class="text-white/20 hover:text-white text-4xl font-light">&times;</button>
                    </div>
                    <div class="flex flex-col gap-12">
                        <div class="flex items-center gap-8">
                            <label class="text-[10px] tracking-[0.4em] text-[#D4A017] uppercase font-bold">Año:</label>
                            <select id="filtroYear" onchange="cargarGraficaAnual()" class="bg-transparent border-b border-white/10 py-2 text-2xl text-white outline-none">
                                @for($y = 2023; $y <= now()->year; $y++)
                                    <option value="{{ $y }}" class="bg-[#1A1A1A]">{{ $y }}</option>
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
            <div class="bg-[#1A1A1A] border border-white/5 w-full max-w-2xl relative z-10 shadow-3xl p-12 md:p-20">
                <h2 class="text-white text-4xl font-extralight uppercase tracking-tighter mb-12">Opciones de<br><span class="text-[#D4A017] font-bold">Descarga</span></h2>
                <form action="{{ route('estadoCuenta.descargarPdf') }}" method="POST" class="space-y-12">
                    @csrf
                    <input type="hidden" name="id_usuario" value="{{ $usuario->id }}">
                    <div class="group">
                        <label class="block text-[9px] uppercase tracking-[0.3em] text-[#D4A017] mb-4 opacity-60 font-bold">Desde</label>
                        <input type="date" name="desde" class="w-full bg-transparent border-b border-white/10 py-4 text-xl text-white outline-none focus:border-[#D4A017] transition-all">
                    </div>
                    <div class="group">
                        <label class="block text-[9px] uppercase tracking-[0.3em] text-[#D4A017] mb-4 opacity-60 font-bold">Hasta</label>
                        <input type="date" name="hasta" class="w-full bg-transparent border-b border-white/10 py-4 text-xl text-white outline-none focus:border-[#D4A017] transition-all">
                    </div>
                    <div class="flex justify-end gap-6 pt-10">
                        <button type="button" onclick="closeModalDescarga()" class="text-white/40 text-[10px] tracking-[0.3em] uppercase font-bold px-10 py-4">Cerrar</button>
                        <button type="submit" class="bg-[#D4A017] text-black text-[10px] tracking-[0.3em] uppercase font-bold px-12 py-5 hover:bg-white transition-all duration-700 shadow-xl">Generar PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let graficaAnual = null;
        function openModal() { document.getElementById("chartsmModal").classList.remove("hidden"); cargarGraficaAnual(); }
        function closeModal() { document.getElementById("chartsmModal").classList.add("hidden"); }
        function openModalDescarga() { document.getElementById("descargaModal").classList.remove("hidden"); }
        function closeModalDescarga() { document.getElementById("descargaModal").classList.add("hidden"); }

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
                    datasets: [{ label: "Pagados", data: Pagados, backgroundColor: "#D4A017" }]
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
