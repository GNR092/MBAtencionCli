@extends('layouts.admin')

@section('content')
    <div class="max-w-full mx-auto p-4 md:p-6">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="page-title">
                Retroactivos
            </h1>
        </div>
    </header>

    <div class="max-w-full mx-auto space-y-6">

        <!-- Barra de búsqueda -->
        <form method="GET" action="{{ route('retroactivo.index') }}" class="tabla-dorada-search">
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
            <a href="{{ route('retroactivo.index') }}"
                class="bg-white/10 hover:bg-white/20 text-white px-6 py-2.5 rounded-xl font-bold transition text-center">
                LIMPIAR
            </a>
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
                            <td colspan="11" class="py-10 text-white/40 italic">No se encontraron retroactivos.</td>
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
            <a href="{{ route('cuentas-pagar.index') }}"
                class="bg-[#d8c495] text-[#0d1f30] px-8 py-3 rounded-xl font-bold hover:bg-[#c9a143] transition shadow-lg w-full md:w-auto text-center">
                IR A PAGAR CUENTAS
            </a>
            <a href="{{ route('retroactivo.eliminados') }}"
                class="bg-red-800/50 text-white px-8 py-3 rounded-xl font-bold hover:bg-red-700/50 transition shadow-lg w-full md:w-auto text-center border border-red-600/50">
                VER ELIMINADOS
            </a>
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Modal detalle inversionista -->
<div id="detalleModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-[9999] flex items-center justify-center hidden p-4">
    <div class="bg-[#112134] border border-[#d8c495]/20 rounded-3xl shadow-2xl p-4 md:p-6 relative w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b border-[#d8c495]/20 pb-3">
            <h2 class="text-xl font-bold text-[#d8c495]" id="detalleTitulo">Detalle de Retroactivos</h2>
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
                        <th class="px-2 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody id="detalleCuerpo">
                </tbody>
                <tfoot class="sticky bottom-0 bg-[#0d1f30]">
                    <tr>
                        <td colspan="9" class="text-right px-2 py-2 text-white/60">Total:</td>
                        <td id="detalleTotalNeto" class="text-[#d8c495] font-bold px-2 py-2"></td>
                        <td id="detalleTotalPagado" class="text-green-400 font-bold px-2 py-2"> </td>
                        <td id="detalleTotalPendiente" class="text-red-400 font-bold px-2 py-2"></td>
                        <td></td>
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

<!-- Modal eliminar retroactivo -->
<div id="eliminarModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-[99999] flex items-center justify-center hidden p-4">
    <div class="bg-[#112134] border border-red-600/50 rounded-3xl shadow-2xl p-6 relative w-full max-w-md">
        <div class="flex justify-between items-center mb-4 border-b border-red-600/30 pb-3">
            <h2 class="text-xl font-bold text-red-400">Eliminar Retroactivo</h2>
            <button type="button" onclick="closeEliminarModal()" class="text-white/50 hover:text-white text-2xl">&times;</button>
        </div>
        <p class="text-white/70 mb-4">¿Está seguro de eliminar este retroactivo? Esta acción lo moverá a la papelera de eliminados.</p>
        <form id="eliminarForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label for="motivo" class="block text-white/50 text-sm mb-1">Motivo (opcional):</label>
                <textarea id="motivo" name="motivo" rows="2" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg p-3 text-white focus:border-red-500 focus:outline-none resize-none"
                    placeholder="Ej: No se pagará por falta de documentos"></textarea>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeEliminarModal()"
                    class="bg-white/10 text-white px-6 py-2 rounded-xl font-bold hover:bg-white/20 transition">CANCELAR</button>
                <button type="submit"
                    class="bg-red-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-red-500 transition">ELIMINAR</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
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

    document.getElementById('detalleTitulo').textContent = 'Detalle de Retroactivos - ' + grupo.nombre;

    const tbody = document.getElementById('detalleCuerpo');
    tbody.innerHTML = '';

    let totalNeto = 0, totalPagado = 0, totalPendiente = 0;

    grupo.cuentas.forEach(cuenta => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-white/5 border-b border-[#d8c495]/10';

        const estadoColor = getEstadoColor(cuenta.estado);
        const tieneFacturaSubida = !!cuenta.xml_file_id;
        const disabledAttr = tieneFacturaSubida ? '' : 'disabled';
        const bloqueoVisual = tieneFacturaSubida ? '' : 'opacity-60 cursor-not-allowed';
        const bloqueoTitle = tieneFacturaSubida ? '' : 'title="Sube factura para cambiar estado"';
        const selectHtml = '<select class="estado-select bg-[#0d1f30] border border-white/20 rounded-lg px-2 py-1 text-xs outline-none focus:ring-2 focus:ring-[#d8c495] ' + estadoColor + ' ' + bloqueoVisual + '" ' +
            'data-id="' + cuenta.id_cuentas_por_pagar + '" data-prev="' + (cuenta.estado || 'pendiente') + '" data-saldo-neto="' + (cuenta.saldo_neto || 0) + '" data-tiene-factura="' + (tieneFacturaSubida ? '1' : '0') + '" ' + disabledAttr + ' ' + bloqueoTitle + '>' +
            '<option value="pendiente" ' + (cuenta.estado === 'pendiente' ? 'selected' : '') + '>Pendiente</option>' +
            '<option value="parcial" ' + (cuenta.estado === 'parcial' ? 'selected' : '') + '>Parcial</option>' +
            '<option value="pagado" ' + (cuenta.estado === 'pagado' ? 'selected' : '') + '>Pagado</option>' +
            '<option value="vencido" ' + (cuenta.estado === 'vencido' ? 'selected' : '') + '>Vencido</option>' +
            '</select>';

        tr.innerHTML = `
            <td class="px-2 py-2 hidden">${cuenta.id_cuentas_por_pagar || ''}</td>
            <td class="px-2 py-2 text-white/70 font-mono text-[10px]">${cuenta.uuid ? cuenta.uuid.substring(0, 8) + '...' : 'N/A'}</td>
            <td class="px-2 py-2 text-white">${grupo.nombre}</td>
            <td class="px-2 py-2 text-white/70">${cuenta.proyecto || 'Sin proyecto'}</td>
            <td class="px-2 py-2">${selectHtml}</td>
            <td class="px-2 py-2 text-white/70">${cuenta.mes_pago || 'N/A'}</td>
            <td class="px-2 py-2 text-white/70">${cuenta.mes_subida || 'N/A'}</td>
            <td class="px-2 py-2 text-white/70 text-right">$${parseFloat(cuenta.importeBase || 0).toFixed(2)}</td>
            <td class="px-2 py-2 text-white/70 text-right">$${parseFloat(cuenta.isr || 0).toFixed(2)}</td>
            <td class="px-2 py-2 text-[#d8c495] font-bold text-right">$${parseFloat(cuenta.saldo_neto || 0).toFixed(2)}</td>
            <td class="px-2 py-2 text-green-400 text-right cell-pagado">$${parseFloat(cuenta.monto_pagado || 0).toFixed(2)}</td>
            <td class="px-2 py-2 text-red-400 text-right cell-pendiente">$${parseFloat(cuenta.saldo_pendiente || 0).toFixed(2)}</td>
            <td class="px-2 py-2">
                <button type="button" onclick="openEliminarModal(${cuenta.id_cuentas_por_pagar})"
                    class="bg-red-800/50 hover:bg-red-700/50 text-red-300 px-3 py-1 rounded-lg text-xs font-bold transition border border-red-600/30">
                    ELIMINAR
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
        
        totalNeto += parseFloat(cuenta.saldo_neto || 0);
        totalPagado += parseFloat(cuenta.monto_pagado || 0);
        totalPendiente += parseFloat(cuenta.saldo_pendiente || 0);
    });

    document.getElementById('detalleTotalNeto').textContent = '$' + totalNeto.toFixed(2);
    document.getElementById('detalleTotalPagado').textContent = '$' + totalPagado.toFixed(2);
    document.getElementById('detalleTotalPendiente').textContent = '$' + totalPendiente.toFixed(2);

    attachEstadoListeners();
    document.getElementById('detalleModal').classList.remove('hidden');
}

function getEstadoColor(estado) {
    const colors = {
        pendiente: 'text-red-300',
        parcial: 'text-yellow-300',
        pagado: 'text-green-300',
        vencido: 'text-orange-300',
    };

    return colors[estado] || 'text-white/70';
}

function updateEstadoSelect(sel, estado) {
    sel.classList.remove('text-red-300', 'text-yellow-300', 'text-green-300', 'text-orange-300', 'text-white/70');
    sel.classList.add(getEstadoColor(estado));
}

function attachEstadoListeners() {
    document.querySelectorAll('#detalleCuerpo .estado-select').forEach(function(sel) {
        sel.addEventListener('change', function() {
            if (this.dataset.tieneFactura !== '1') {
                this.value = this.dataset.prev || 'pendiente';
                updateEstadoSelect(this, this.value);
                return;
            }

            const prev = this.dataset.prev;
            const id = this.dataset.id;
            const selectEl = this;

            fetch('/cuentasporpagar/' + id + '/estado', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ estado: selectEl.value }),
            })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (!d.success) {
                        selectEl.value = prev;
                        updateEstadoSelect(selectEl, prev);
                        return;
                    }

                    updateEstadoSelect(selectEl, selectEl.value);
                    selectEl.dataset.prev = selectEl.value;

                    const row = selectEl.closest('tr');
                    row.querySelector('.cell-pagado').textContent = '$' + Number((d.montoPagado || '0').replace(/,/g, '')).toFixed(2);
                    row.querySelector('.cell-pendiente').textContent = '$' + Number((d.saldoPendiente || '0').replace(/,/g, '')).toFixed(2);

                    if (grupoActual) {
                        const cuentaId = parseInt(id, 10);
                        const idx = grupoActual.cuentas.findIndex(function(c) { return c.id_cuentas_por_pagar === cuentaId; });
                        if (idx !== -1) {
                            grupoActual.cuentas[idx].estado = selectEl.value;
                            grupoActual.cuentas[idx].monto_pagado = Number((d.montoPagado || '0').replace(/,/g, ''));
                            grupoActual.cuentas[idx].saldo_pendiente = Number((d.saldoPendiente || '0').replace(/,/g, ''));
                        }

                        const idxGlobal = gruposData.findIndex(function(g) { return g.nombre === grupoActual.nombre; });
                        if (idxGlobal !== -1) {
                            gruposData[idxGlobal] = grupoActual;
                        }
                    }

                    recalcularTotalesModal();
                    recalcularTotalesGlobales();
                    actualizarFilaGrupo();
                })
                .catch(function() {
                    selectEl.value = prev;
                    updateEstadoSelect(selectEl, prev);
                });
        });
    });
}

function recalcularTotalesModal() {
    let totalNeto = 0;
    let totalPagado = 0;
    let totalPendiente = 0;

    document.querySelectorAll('#detalleCuerpo tr').forEach(function(row) {
        const neto = parseFloat(row.querySelector('td:nth-child(10)').textContent.replace(/[^0-9.-]/g, '')) || 0;
        const pagado = parseFloat(row.querySelector('.cell-pagado').textContent.replace(/[^0-9.-]/g, '')) || 0;
        const pendiente = parseFloat(row.querySelector('.cell-pendiente').textContent.replace(/[^0-9.-]/g, '')) || 0;

        totalNeto += neto;
        totalPagado += pagado;
        totalPendiente += pendiente;
    });

    document.getElementById('detalleTotalNeto').textContent = '$' + totalNeto.toFixed(2);
    document.getElementById('detalleTotalPagado').textContent = '$' + totalPagado.toFixed(2);
    document.getElementById('detalleTotalPendiente').textContent = '$' + totalPendiente.toFixed(2);
}

function actualizarFilaGrupo() {
    if (!grupoActualNombre || !grupoActual) {
        return;
    }

    const grupoRow = document.querySelector('[data-grupo-nombre="' + grupoActualNombre + '"]');
    if (!grupoRow) {
        return;
    }

    const totalPagado = grupoActual.cuentas.reduce(function(acc, c) { return acc + Number(c.monto_pagado || 0); }, 0);
    const totalPendiente = grupoActual.cuentas.reduce(function(acc, c) { return acc + Number(c.saldo_pendiente || 0); }, 0);

    const pagadoEl = grupoRow.querySelector('.grupo-pagado');
    const pendienteEl = grupoRow.querySelector('.grupo-pendiente');
    if (pagadoEl) {
        pagadoEl.textContent = 'Pag: $' + totalPagado.toFixed(2);
    }
    if (pendienteEl) {
        pendienteEl.textContent = 'Pen: $' + totalPendiente.toFixed(2);
    }
}

function recalcularTotalesGlobales() {
    let totalPagado = 0;
    let totalPendiente = 0;

    gruposData.forEach(function(grupo) {
        (grupo.cuentas || []).forEach(function(cuenta) {
            totalPagado += Number(cuenta.monto_pagado || 0);
            totalPendiente += Number(cuenta.saldo_pendiente || 0);
        });
    });

    document.getElementById('totalPagado').textContent = '$' + totalPagado.toFixed(2);
    document.getElementById('totalPendiente').textContent = '$' + totalPendiente.toFixed(2);
}

function closeDetalleModal() {
    document.getElementById('detalleModal').classList.add('hidden');
}

function openEliminarModal(id) {
    const form = document.getElementById('eliminarForm');
    form.action = '/retroactivo/' + id + '/eliminar';
    document.getElementById('eliminarModal').classList.remove('hidden');
}

function closeEliminarModal() {
    document.getElementById('eliminarModal').classList.add('hidden');
}
</script>
@endpush
