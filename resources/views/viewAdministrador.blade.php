@extends('layouts.admin')

@section('content')
    <div class="max-w-full mx-auto p-4 md:p-6">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none uppercase">
                Pagar Cuentas
            </h1>
        </div>
    </header>

    <div class="max-w-full mx-auto space-y-6">

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
                            <th>Inversionista</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($grupos as $grupo)
                        <tr class="group-header bg-[#0d1f30]/80 cursor-pointer hover:bg-[#0d1f30] transition" 
                            data-grupo-nombre="{{ $grupo['nombre'] }}"
                            onclick='openDetalleModal(@json($grupo))'>
                            <td colspan="6" class="text-left font-bold text-[#d8c495] py-3 px-4">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    {{ $grupo['nombre'] }}
                                </span>
                                <span class="text-white/50 text-xs font-normal ml-6">({{ $grupo['count'] }} cuenta{{ $grupo['count'] > 1 ? 's' : '' }})</span>
                            </td>
                            <td colspan="5" class="text-right py-3 px-4">
                                <span class="text-white/60 text-xs">Total: </span>
                                <span class="text-[#d8c495] font-bold grupo-total"> ${{ number_format($grupo['total_neto'], 2) }}</span>
                                <span class="text-white/40 mx-2">|</span>
                                <span class="text-green-400 text-xs grupo-pagado">Pag: ${{ number_format($grupo['total_pagado'], 2) }}</span>
                                <span class="text-white/40 mx-2">|</span>
                                <span class="text-red-400 text-xs grupo-pendiente">Pen: ${{ number_format($grupo['total_pendiente'], 2) }}</span>
                            </td>
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
        </div>
    </div>
</div>
@endsection

@push('modals')
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
                    <option value="{{ $p->id_proyecto }}">{{ $p->nombre_proyecto }}</option>
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
        <h2 class="text-xl font-bold mb-6 text-[#d8c495]">Configuración de Reporte</h2>
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

<!-- Modal detalle inversionista -->
<div id="detalleModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-9999 flex items-center justify-center hidden p-4">
    <div class="bg-[#112134] border border-[#d8c495]/20 rounded-3xl shadow-2xl p-4 md:p-6 relative w-full max-w-auto max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b border-[#d8c495]/20 pb-3">
            <h2 class="text-xl font-bold text-[#d8c495]" id="detalleTitulo">Detalle de Cuentas</h2>
            <button type="button" onclick="closeDetalleModal()" class="text-white/50 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="overflow-auto flex-1 custom-scroll">
            <table class="tabla-dorada w-full text-xs">
                <thead class="sticky top-0 bg-[#0d1f30]">
                    <tr>
                        <th class="px-2 py-2 hidden">ID</th>
                        <th class="px-2 py-2">Folio Fiscal</th>
                        <th class="px-2 py-2">Inversionista</th>
                        <th class="px-2 py-2">Proyecto</th>
                        <th class="px-2 py-2">Estado</th>
                        <th class="px-2 py-2">Mes Correspondiente</th>
                        <th class="px-2 py-2">Mes Subida</th>
                        <th class="px-2 py-2">Base</th>
                        <th class="px-2 py-2">ISR</th>
                        <th class="px-2 py-2">Neto</th>
                        <th class="px-2 py-2">Pagado</th>
                        <th class="px-2 py-2">Pendiente</th>
                    </tr>
                </thead>
                <tbody id="detalleCuerpo">
                </tbody>
                <tfoot class="sticky bottom-0 bg-[#0d1f30]">
                    <tr>
                        <td colspan="8" class="text-right px-2 py-2 text-white/60">Total:</td>
                        <td id="detalleTotalNeto" class="text-[#d8c495] font-bold px-2 py-2"></td>
                        <td id="detalleTotalPagado" class="text-green-400 font-bold px-2 py-2"> </td>
                        <td id="detalleTotalPendiente" class="text-red-400 font-bold px-2 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="mt-4 pt-3 border-t border-[#d8c495]/20 flex justify-end">
            <button type="button" onclick="closeDetalleModal()"
                class="bg-white/10 text-white px-6 py-2 rounded-xl font-bold hover:bg-white/20 transition">CERRAR</button>
        </div>
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

let grupoActualNombre = '';
let grupoActual = null;
let gruposData = @json($grupos->toArray());

function openDetalleModal(grupo) {
    const nombre = grupo.nombre;
    const grupoActualizado = gruposData.find(function(g) { return g.nombre === nombre; });
    if (grupoActualizado) {
        grupo = grupoActualizado;
        grupoActual = grupoActualizado;
    }
    grupoActualNombre = nombre;
    document.getElementById('detalleTitulo').textContent = 'Cuentas de ' + grupo.nombre;
    const tbody = document.getElementById('detalleCuerpo');
    tbody.innerHTML = '';
    
    let totalNeto = 0, totalPagado = 0, totalPendiente = 0;
    
    grupo.cuentas.forEach(function(cuenta) {
        const mesPago = cuenta.mesesdepago ? JSON.parse(cuenta.mesesdepago).mes : 'N/A';
        
        const row = document.createElement('tr');
        row.className = 'border-b border-white/5 hover:bg-white/5';
        row.dataset.id = cuenta.id_cuentas_por_pagar;
        row.dataset.saldoNeto = cuenta.saldo_neto || 0;
        
        const hasUuid = cuenta.uuid && cuenta.uuid.trim() !== '';
        const estadoColor = hasUuid ? getEstadoColor(cuenta.estado) : 'text-white/50';
        
        const selectHtml = hasUuid 
            ? '<select class="estado-select bg-[#0d1f30] border border-white/20 rounded-lg px-2 py-1 text-xs outline-none focus:ring-2 focus:ring-[#d8c495] ' + estadoColor + '" ' +
              'data-id="' + cuenta.id_cuentas_por_pagar + '" data-prev="' + cuenta.estado + '" data-saldo-neto="' + (cuenta.saldo_neto || 0) + '">' +
              '<option value="pendiente" ' + (cuenta.estado === 'pendiente' ? 'selected' : '') + '>Pendiente</option>' +
              '<option value="parcial" ' + (cuenta.estado === 'parcial' ? 'selected' : '') + '>Parcial</option>' +
              '<option value="pagado" ' + (cuenta.estado === 'pagado' ? 'selected' : '') + '>Pagado</option>' +
              '<option value="vencido" ' + (cuenta.estado === 'vencido' ? 'selected' : '') + '>Vencido</option>' +
              '</select>'
            : '<span class="' + estadoColor + '">' + cuenta.estado + '</span>';
        
        row.innerHTML = 
            '<td class="px-2 py-2 font-bold text-[#d8c495] hidden">' + cuenta.id_cuentas_por_pagar + '</td>' +
            '<td class="px-2 py-2 text-xs font-mono text-white/50">' + (cuenta.uuid || 'N/A') + '</td>' +
            '<td class="px-2 py-2">' + cuenta.name + '</td>' +
            '<td class="px-2 py-2 text-white/80">' + cuenta.proyecto + '</td>' +
            '<td class="px-2 py-2">' + selectHtml + '</td>' +
            '<td class="px-2 py-2 text-white/70 text-xs">' + mesPago + '</td>' +
            '<td class="px-2 py-2 text-white/70 text-xs">' + (cuenta.mes_subida || 'N/A') + '</td>' +
            '<td class="px-2 py-2">$' + Number(cuenta.importe_base_final || 0).toLocaleString('es-MX', {minimumFractionDigits: 2}) + '</td>' +
            '<td class="px-2 py-2 text-red-400">$' + Number(cuenta.isr || 0).toLocaleString('es-MX', {minimumFractionDigits: 2}) + '</td>' +
            '<td class="px-2 py-2 font-bold cell-neto">$' + Number(cuenta.saldo_neto || 0).toLocaleString('es-MX', {minimumFractionDigits: 2}) + '</td>' +
            '<td class="px-2 py-2 text-green-400 cell-pagado">$' + Number(cuenta.monto_pagado || 0).toLocaleString('es-MX', {minimumFractionDigits: 2}) + '</td>' +
            '<td class="px-2 py-2 text-[#d8c495] font-bold cell-pendiente">$' + Number(cuenta.saldo_pendiente || 0).toLocaleString('es-MX', {minimumFractionDigits: 2}) + '</td>';
        tbody.appendChild(row);
        
        totalNeto += Number(cuenta.saldo_neto || 0);
        totalPagado += Number(cuenta.monto_pagado || 0);
        totalPendiente += Number(cuenta.saldo_pendiente || 0);
    });
    
    document.getElementById('detalleTotalNeto').textContent = '$' + totalNeto.toLocaleString('es-MX', {minimumFractionDigits: 2});
    document.getElementById('detalleTotalPagado').textContent = '$' + totalPagado.toLocaleString('es-MX', {minimumFractionDigits: 2});
    document.getElementById('detalleTotalPendiente').textContent = '$' + totalPendiente.toLocaleString('es-MX', {minimumFractionDigits: 2});
    
    attachEstadoListeners();
    document.getElementById('detalleModal').classList.remove('hidden');
}

function getEstadoColor(estado) {
    const colors = {
        'pendiente': 'text-red-300',
        'parcial': 'text-yellow-300',
        'pagado': 'text-green-300',
        'vencido': 'text-orange-300'
    };
    return colors[estado] || 'text-white/70';
}

function attachEstadoListeners() {
    document.querySelectorAll('#detalleCuerpo .estado-select').forEach(function(sel) {
        sel.addEventListener('change', function() {
            const prev = this.dataset.prev;
            const id = this.dataset.id;
            const selectEl = this;
            
            fetch('/cuentasporpagar/' + id + '/estado', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ estado: selectEl.value }),
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    updateEstadoSelect(selectEl, selectEl.value);
                    selectEl.dataset.prev = selectEl.value;
                    
                    const row = selectEl.closest('tr');
                    row.querySelector('.cell-pagado').textContent = '$' + d.montoPagado.replace(/,/g, '');
                    row.querySelector('.cell-pendiente').textContent = '$' + d.saldoPendiente.replace(/,/g, '');
                    
                    if (grupoActual) {
                        const cuentaId = parseInt(selectEl.dataset.id);
                        const cuentaIdx = grupoActual.cuentas.findIndex(function(c) { return c.id_cuentas_por_pagar === cuentaId; });
                        if (cuentaIdx !== -1) {
                            grupoActual.cuentas[cuentaIdx].estado = selectEl.value;
                            grupoActual.cuentas[cuentaIdx].monto_pagado = parseFloat(d.montoPagado.replace(/,/g, ''));
                            grupoActual.cuentas[cuentaIdx].saldo_pendiente = parseFloat(d.saldoPendiente.replace(/,/g, ''));
                        }
                        const idxGlobal = gruposData.findIndex(function(g) { return g.nombre === grupoActual.nombre; });
                        if (idxGlobal !== -1) {
                            gruposData[idxGlobal] = grupoActual;
                        }
                    }
                    
                    recalcularTotalesModal();
                    document.getElementById('totalPendiente').textContent = '$' + d.totalPendiente;
                    document.getElementById('totalPagado').textContent = '$' + d.totalPagado;
                    
                    if (grupoActualNombre) {
                        const grupoRow = document.querySelector('[data-grupo-nombre="' + grupoActualNombre + '"]');
                        if (grupoRow) {
                            let grupoPagado = 0, grupoPendiente = 0;
                            document.querySelectorAll('#detalleCuerpo tr').forEach(function(r) {
                                grupoPagado += parseFloat(r.querySelector('.cell-pagado').textContent.replace(/[^0-9.-]/g, '')) || 0;
                                grupoPendiente += parseFloat(r.querySelector('.cell-pendiente').textContent.replace(/[^0-9.-]/g, '')) || 0;
                            });
                            
                            grupoRow.querySelector('.grupo-pagado').textContent = 'Pag: $' + grupoPagado.toLocaleString('es-MX', {minimumFractionDigits: 2});
                            grupoRow.querySelector('.grupo-pendiente').textContent = 'Pen: $' + grupoPendiente.toLocaleString('es-MX', {minimumFractionDigits: 2});
                        }
                    }
                } else {
                    alert(d.message || 'Error');
                    selectEl.value = prev;
                    updateEstadoSelect(selectEl, prev);
                }
            })
            .catch(function() {
                selectEl.value = prev;
                updateEstadoSelect(selectEl, prev);
            });
        });
    });
}

function updateEstadoSelect(sel, estado) {
    sel.classList.remove('text-red-300', 'text-yellow-300', 'text-green-300', 'text-orange-300', 'text-white/70');
    sel.classList.add(getEstadoColor(estado));
}

function recalcularTotalesModal() {
    let totalNeto = 0, totalPagado = 0, totalPendiente = 0;
    document.querySelectorAll('#detalleCuerpo tr').forEach(function(row) {
        const neto = parseFloat(row.dataset.saldoNeto) || 0;
        const pagado = parseFloat(row.querySelector('.cell-pagado').textContent.replace(/[$,]/g, '')) || 0;
        const pendiente = parseFloat(row.querySelector('.cell-pendiente').textContent.replace(/[$,]/g, '')) || 0;
        
        totalNeto += neto;
        totalPagado += pagado;
        totalPendiente += pendiente;
    });
    
    document.getElementById('detalleTotalNeto').textContent = '$' + totalNeto.toLocaleString('es-MX', {minimumFractionDigits: 2});
    document.getElementById('detalleTotalPagado').textContent = '$' + totalPagado.toLocaleString('es-MX', {minimumFractionDigits: 2});
    document.getElementById('detalleTotalPendiente').textContent = '$' + totalPendiente.toLocaleString('es-MX', {minimumFractionDigits: 2});
}

function closeDetalleModal() {
    document.getElementById('detalleModal').classList.add('hidden');
}

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
    const id_proyecto = document.getElementById('selectProyecto').value;

    try {
        const r = await fetch(`/cuentas/grafica-anual/${year}`);
        const data = await r.json();
        if (_chartAnual) _chartAnual.destroy();
        _chartAnual = _buildChart('graficaAnual', data);
    } catch(e) { console.error('Error gráfica anual:', e); }

    if (id_proyecto) {
        try {
            const r2 = await fetch(`/cuentas/grafica-anual-proyecto/${year}/${id_proyecto}`);
            const data2 = await r2.json();
            if (_chartProyecto) _chartProyecto.destroy();
            const proyectoNombre = document.getElementById('selectProyecto').options[document.getElementById('selectProyecto').selectedIndex].text;
            _chartProyecto = _buildChart('graficaProyecto', data2, proyectoNombre);
        } catch(e) { console.error('Error gráfica proyecto:', e); }
    } else {
        if (_chartProyecto) { _chartProyecto.destroy(); _chartProyecto = null; }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('selectProyecto').addEventListener('change', cargarGraficaAnual);
});
</script>
@endpush

