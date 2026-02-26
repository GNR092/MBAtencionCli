@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none uppercase">
                Pagar cuentas
            </h1>
        </div>
    </header>

    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Barra de búsqueda -->
        <form method="GET" action="{{ route('cuentas-pagar.index') }}" class="tabla-dorada-search">
            @csrf
            <label for="searchInput">BUSCAR POR:</label>
            <input type="text" id="searchInput" name="search" value="{{ request('search') }}"
                placeholder="Nombre o ID..." class="flex-1 min-w-40">
            <select name="categoria" id="categoria">
                <option value="mes" {{ request('categoria') == 'mes' ? 'selected' : '' }}>Mes</option>
                <option value="estado" {{ request('categoria') == 'estado' ? 'selected' : '' }}>Estado</option>
                <option value="name" {{ request('categoria') == 'name' ? 'selected' : '' }}>Inversionista</option>
            </select>
            <button type="submit"
                class="bg-[#d8c495] hover:bg-[#c9a143] text-[#0d1f30] px-6 py-2.5 rounded-xl font-bold transition shadow-md">
                BUSCAR
            </button>
            <a href="{{ route('cuentas-pagar.limpiar') }}"
                class="bg-white/10 hover:bg-white/20 text-white px-6 py-2.5 rounded-xl font-bold transition text-center">
                LIMPIAR
            </a>
            <button type="button" onClick="openModalDescarga()"
                class="bg-[#d8c495] hover:bg-[#c9a143] text-[#0d1f30] px-8 py-2.5 rounded-xl font-bold transition shadow-md">
                DESCARGAR
            </button>
        </form>

        <!-- Tabla -->
        <div class="tabla-dorada-container">
            <div class="overflow-x-auto custom-scroll">
                <table class="tabla-dorada">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Folio Fiscal (UUID)</th>
                            <th>Inversionista</th>
                            <th>Proyecto</th>
                            <th>Estado</th>
                            <th>Mes</th>
                            <th>Base</th>
                            <th>ISR</th>
                            <th>Neto</th>
                            <th>Pagado</th>
                            <th>Pendiente</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($cuentas as $cuenta)
                        <tr>
                            <td class="font-bold text-[#d8c495]">{{ $cuenta->id_cuentas_por_pagar }}</td>
                            <td class="text-xs font-mono text-white/50 whitespace-nowrap">{{ $cuenta->uuid ?? 'N/A' }}</td>
                            <td class="text-left font-medium">{{ $cuenta->name }}</td>
                            <td class="text-white/80">{{ $cuenta->proyecto }}</td>
                            <td>
                                <select class="estado-select bg-[#0d1f30] border border-white/20 rounded-lg px-2 py-1 text-xs outline-none focus:ring-2 focus:ring-[#d8c495]
                                    @if($cuenta->estado === 'pendiente') text-red-300
                                    @elseif($cuenta->estado === 'parcial') text-yellow-300
                                    @elseif($cuenta->estado === 'pagado') text-green-300
                                    @elseif($cuenta->estado === 'vencido') text-orange-300
                                    @else text-white/70 @endif"
                                    data-id="{{ $cuenta->id_cuentas_por_pagar }}">
                                    <option value="pendiente" {{ $cuenta->estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="parcial" {{ $cuenta->estado === 'parcial' ? 'selected' : '' }}>Parcial</option>
                                    <option value="pagado" {{ $cuenta->estado === 'pagado' ? 'selected' : '' }}>Pagado</option>
                                    <option value="vencido" {{ $cuenta->estado === 'vencido' ? 'selected' : '' }}>Vencido</option>
                                </select>
                            </td>
                            <td class="text-white/70 text-xs">{{ json_decode($cuenta->mesesdepago)->mes ?? 'N/A' }}</td>
                            <td class="font-medium">${{ number_format($cuenta->importe_base_final, 2) }}</td>
                            <td class="text-red-400 font-medium">${{ number_format($cuenta->isr, 2) }}</td>
                            <td class="font-bold">${{ number_format($cuenta->saldo_neto, 2) }}</td>
                            <td class="text-green-400 font-medium cell-pagado">${{ number_format($cuenta->monto_pagado, 2) }}</td>
                            <td class="font-black text-[#d8c495] cell-pendiente">${{ number_format($cuenta->saldo_pendiente, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="py-10 text-white/40 italic">No se encontraron registros activos.</td>
                        </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="10" class="text-right px-6 py-3 uppercase text-xs tracking-wider text-white/60">Total Pendiente:</td>
                            <td id="totalPendiente" class="px-4 py-3 text-red-400">${{ number_format($totalPendiente, 2) }}</td>
                        </tr>
                        <tr class="border-t border-[#d8c495]/10">
                            <td colspan="10" class="text-right px-6 py-3 uppercase text-xs tracking-wider text-white/60">Total Pagado:</td>
                            <td id="totalPagado" class="px-4 py-3 text-green-400">${{ number_format($totalPagado, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <button type="button"
                class="bg-[#d8c495] text-[#0d1f30] px-8 py-3 rounded-xl font-bold hover:bg-[#c9a143] transition shadow-lg w-full md:w-auto"
                onClick="openModal()">
                MOSTRAR GRÁFICAS
            </button>
            <div class="tabla-dorada-footer rounded-xl border border-[#d8c495]/20 bg-[#112134]/60 backdrop-blur-md px-2">
                {{ $cuentas->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal gráficas -->
<div id="chartsmModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-[9999] flex items-center justify-center hidden p-4">
    <div class="bg-[#112134] border border-[#d8c495]/20 rounded-3xl shadow-2xl p-4 md:p-8 relative w-full max-w-4xl max-h-[90vh] overflow-y-auto custom-scroll">
        <h2 class="text-2xl font-bold mb-6 text-[#d8c495] border-b-2 border-[#d8c495]/40 pb-2">Análisis de Cuentas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#d8c495]/70 uppercase">Año de consulta:</label>
                <select id="filtroYear"
                    class="w-full border p-3 rounded-xl outline-none focus:ring-2 focus:ring-[#d8c495] bg-[#0d1f30] border-[#d8c495]/30 text-white"
                    onchange="cargarGraficaAnual()">
                    @for($y = $minYear; $y <= now()->year; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-[#d8c495]/70 uppercase">Filtrar por Proyecto:</label>
                <select id="selectProyecto"
                    class="w-full border p-3 rounded-xl outline-none focus:ring-2 focus:ring-[#d8c495] bg-[#0d1f30] border-[#d8c495]/30 text-white">
                    <option value="">-- Todos los proyectos --</option>
                    @foreach ($proyectos as $p)
                    <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="space-y-10">
            <div>
                <h3 class="font-bold text-white/80 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 bg-[#d8c495] rounded-full"></span> Resumen Anual General
                </h3>
                <canvas id="graficaAnual"></canvas>
            </div>
            <div>
                <h3 class="font-bold text-white/80 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 bg-[#d8c495]/60 rounded-full"></span> Desempeño por Proyecto
                </h3>
                <canvas id="graficaProyecto"></canvas>
            </div>
        </div>

        <div class="sticky bottom-0 bg-[#112134] pt-6 mt-6 border-t border-[#d8c495]/20 flex justify-end">
            <button type="button" onclick="closeModal()"
                class="bg-white/10 text-white px-8 py-2 rounded-xl font-bold hover:bg-white/20 transition">CERRAR</button>
        </div>
    </div>
</div>

<!-- Modal descarga -->
<div id="descargaModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-[9999] flex items-center justify-center hidden p-4">
    <div class="bg-[#112134] border border-[#d8c495]/20 rounded-3xl shadow-2xl p-6 md:p-10 w-full max-w-md">
        <h2 class="text-xl font-bold mb-6 text-center text-[#d8c495]">Configuración de Reporte</h2>
        <form action="{{ route('cuentas-pagar.export') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-[#d8c495]/70">DESDE</label>
                    <input type="date" name="desde" id="desde"
                        class="w-full p-3 bg-white/10 border border-[#d8c495]/30 text-white rounded-xl outline-none focus:ring-2 focus:ring-[#d8c495]">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-[#d8c495]/70">HASTA</label>
                    <input type="date" name="hasta" id="hasta"
                        class="w-full p-3 bg-white/10 border border-[#d8c495]/30 text-white rounded-xl outline-none focus:ring-2 focus:ring-[#d8c495]">
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-[#d8c495]/70">ESTADO DE PAGO</label>
                <select name="estado" id="estado"
                    class="w-full p-3 bg-[#0d1f30] border border-[#d8c495]/30 text-white rounded-xl outline-none focus:ring-2 focus:ring-[#d8c495]">
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
                    class="text-white/50 font-bold hover:text-white transition">CANCELAR</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let _chartAnual = null, _chartProyecto = null;
const _MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

function openModal()         { document.getElementById('chartsmModal').classList.remove('hidden'); cargarGraficaAnual(); }
function closeModal()        { document.getElementById('chartsmModal').classList.add('hidden'); }
function openModalDescarga() { document.getElementById('descargaModal').classList.remove('hidden'); }
function closeModalDescarga(){ document.getElementById('descargaModal').classList.add('hidden'); }

function _buildChart(canvasId, data, titulo) {
    return new Chart(document.getElementById(canvasId), {
        type: 'bar',
        data: {
            labels: data.map(x => _MESES[x.mes - 1]),
            datasets: [
                { label: 'Pagados',    data: data.map(x => x.pagados),    backgroundColor: '#10b981' },
                { label: 'Pendientes', data: data.map(x => x.no_pagados), backgroundColor: '#d8c495' }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: 'rgba(255,255,255,0.7)' } },
                title: titulo ? { display: true, text: titulo, color: '#d8c495', font: { size: 13 } } : { display: false }
            },
            scales: {
                y: { ticks: { color: 'rgba(255,255,255,0.5)', callback: v => '$' + Number(v).toLocaleString('es-MX') }, grid: { color: 'rgba(255,255,255,0.05)' } },
                x: { ticks: { color: 'rgba(255,255,255,0.5)' }, grid: { display: false } }
            }
        }
    });
}

async function cargarGraficaAnual() {
    const year    = document.getElementById('filtroYear').value;
    const proyecto = document.getElementById('selectProyecto').value;

    try {
        const r = await fetch(`/cuentas/grafica-anual/${year}`);
        const data = await r.json();
        if (_chartAnual) _chartAnual.destroy();
        _chartAnual = _buildChart('graficaAnual', data);
    } catch(e) { console.error('Error gráfica anual:', e); }

    if (proyecto) {
        try {
            const r2 = await fetch(`/cuentas/grafica-anual-proyecto/${year}/${encodeURIComponent(proyecto)}`);
            const data2 = await r2.json();
            if (_chartProyecto) _chartProyecto.destroy();
            _chartProyecto = _buildChart('graficaProyecto', data2, proyecto);
        } catch(e) { console.error('Error gráfica proyecto:', e); }
    } else {
        if (_chartProyecto) { _chartProyecto.destroy(); _chartProyecto = null; }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('selectProyecto').addEventListener('change', cargarGraficaAnual);

    const colorMap = { pendiente: 'text-red-300', parcial: 'text-yellow-300', pagado: 'text-green-300', vencido: 'text-orange-300' };
    function applyColor(sel) {
        Object.values(colorMap).forEach(c => sel.classList.remove(c));
        sel.classList.add(colorMap[sel.value] || 'text-white/70');
    }
    document.querySelectorAll('.estado-select').forEach(sel => {
        sel.dataset.prev = sel.value;
        sel.addEventListener('change', function () {
            const prev = this.dataset.prev;
            fetch(`/cuentasporpagar/${this.dataset.id}/estado`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ estado: this.value }),
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    applyColor(this); this.dataset.prev = this.value;
                    const row = this.closest('tr');
                    row.querySelector('.cell-pagado').textContent   = '$' + d.montoPagado;
                    row.querySelector('.cell-pendiente').textContent = '$' + d.saldoPendiente;
                    document.getElementById('totalPendiente').textContent = '$' + d.totalPendiente;
                    document.getElementById('totalPagado').textContent    = '$' + d.totalPagado;
                } else { alert(d.message || 'Error'); this.value = prev; applyColor(this); }
            })
            .catch(() => { this.value = prev; applyColor(this); });
        });
    });
});
</script>
@endsection
