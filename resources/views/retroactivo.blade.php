@extends('layouts.admin')

@section('content')
    <div class="max-w-full mx-auto p-4 md:p-6">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none uppercase">
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
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Modal detalle inversionista -->
<div id="detalleModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-9999 flex items-center justify-center hidden p-4">
    <div class="bg-[#112134] border border-[#d8c495]/20 rounded-3xl shadow-2xl p-4 md:p-6 relative w-full max-w-auto max-h-[90vh] overflow-hidden flex flex-col">
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
@endpush

@push('scripts')
<script>
function openDetalleModal(grupo) {
    document.getElementById('detalleTitulo').textContent = 'Detalle de Retroactivos - ' + grupo.nombre;
    
    const tbody = document.getElementById('detalleCuerpo');
    tbody.innerHTML = '';
    
    let totalNeto = 0, totalPagado = 0, totalPendiente = 0;
    
    grupo.cuentas.forEach(cuenta => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-white/5 border-b border-[#d8c495]/10';
        
        const estadoClass = {
            'pagado': 'text-green-400',
            'parcial': 'text-yellow-400',
            'pendiente': 'text-red-400'
        }[cuenta.estado] || 'text-white';
        
        tr.innerHTML = `
            <td class="px-2 py-2 hidden">${cuenta.id_cuentas_por_pagar || ''}</td>
            <td class="px-2 py-2 text-white/70 font-mono text-[10px]">${cuenta.uuid ? cuenta.uuid.substring(0, 8) + '...' : 'N/A'}</td>
            <td class="px-2 py-2 text-white">${grupo.nombre}</td>
            <td class="px-2 py-2 text-white/70">${cuenta.proyecto || 'Sin proyecto'}</td>
            <td class="px-2 py-2 ${estadoClass} font-bold uppercase">${cuenta.estado || 'N/A'}</td>
            <td class="px-2 py-2 text-white/70">${cuenta.mes_pago || 'N/A'}</td>
            <td class="px-2 py-2 text-white/70">${cuenta.mes_subida || 'N/A'}</td>
            <td class="px-2 py-2 text-white/70 text-right">$${parseFloat(cuenta.importeBase || 0).toFixed(2)}</td>
            <td class="px-2 py-2 text-white/70 text-right">$${parseFloat(cuenta.isr || 0).toFixed(2)}</td>
            <td class="px-2 py-2 text-[#d8c495] font-bold text-right">$${parseFloat(cuenta.saldo_neto || 0).toFixed(2)}</td>
            <td class="px-2 py-2 text-green-400 text-right">$${parseFloat(cuenta.monto_pagado || 0).toFixed(2)}</td>
            <td class="px-2 py-2 text-red-400 text-right">$${parseFloat(cuenta.saldo_pendiente || 0).toFixed(2)}</td>
        `;
        
        tbody.appendChild(tr);
        
        totalNeto += parseFloat(cuenta.saldo_neto || 0);
        totalPagado += parseFloat(cuenta.monto_pagado || 0);
        totalPendiente += parseFloat(cuenta.saldo_pendiente || 0);
    });
    
    document.getElementById('detalleTotalNeto').textContent = '$' + totalNeto.toFixed(2);
    document.getElementById('detalleTotalPagado').textContent = '$' + totalPagado.toFixed(2);
    document.getElementById('detalleTotalPendiente').textContent = '$' + totalPendiente.toFixed(2);
    
    document.getElementById('detalleModal').classList.remove('hidden');
}

function closeDetalleModal() {
    document.getElementById('detalleModal').classList.add('hidden');
}
</script>
@endpush
