@extends('layouts.admin')

@section('content')
    <div class="max-w-full mx-auto p-4 md:p-6">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="page-title">
                Eliminados
            </h1>
        </div>
        <p class="text-white/50 mt-2 text-sm">Retroactivos eliminados. Puede restaurarlos o eliminarlos permanentemente.</p>
    </header>

    @if(session('success'))
    <div class="bg-green-800/20 border border-green-600 rounded-xl p-4 text-green-200 mb-6">
        <p class="font-bold">Exito</p>
        <p class="text-sm">{{ session('success') }}</p>
    </div>
    @endif

    <div class="max-w-full mx-auto space-y-6">

        <!-- Barra de búsqueda -->
        <form method="GET" action="{{ route('retroactivo.eliminados') }}" class="tabla-dorada-search">
            @csrf
            <label for="searchInput">BUSCAR POR:</label>
            <input type="text" id="searchInput" name="search" value="{{ request('search') }}"
                placeholder="Nombre o ID..." class="flex-1 min-w-40">
            <select name="categoria" id="categoria">
                <option value="mes" {{ request('categoria') == 'mes' ? 'selected' : '' }}>Mes</option>
                <option value="estado" {{ request('categoria') == 'estado' ? 'selected' : '' }}>Estado</option>
                <option value="name" {{ request('categoria') == 'name' ? 'selected' : '' }}>Inversionista</option>
                <option value="eliminado_por" {{ request('categoria') == 'eliminado_por' ? 'selected' : '' }}>Eliminado Por</option>
            </select>
            <button type="submit"
                class="bg-[#d8c495] hover:bg-[#c9a143] text-[#0d1f30] px-6 py-2.5 rounded-xl font-bold transition shadow-md">
                BUSCAR
            </button>
            <a href="{{ route('retroactivo.eliminados') }}"
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
                            <td colspan="5" class="text-left font-bold text-[#d8c495] py-3 px-4">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    {{ $grupo['nombre'] }}
                                </span>
                                <span class="text-white/50 text-xs font-normal ml-6">({{ $grupo['count'] }} eliminada{{ $grupo['count'] > 1 ? 's' : '' }})</span>
                            </td>
                            <td colspan="3" class="text-right py-3 px-4">
                                <span class="text-white/60 text-xs">Total Neto: </span>
                                <span class="text-red-400 font-bold"> ${{ number_format($grupo['total_neto'], 2) }}</span>
                            </td>
                        </tr>
                        
                        @empty
                        <tr>
                            <td colspan="8" class="py-10 text-white/40 italic">No se encontraron retroactivos eliminados.</td>
                        </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="7" class="text-right px-6 py-3 uppercase text-xs tracking-wider text-white/60">Total Eliminado:</td>
                            <td id="totalEliminado" class="px-4 py-3 text-red-400">${{ number_format($totalEliminado ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <a href="{{ route('retroactivo.index') }}"
                class="bg-[#d8c495] text-[#0d1f30] px-8 py-3 rounded-xl font-bold hover:bg-[#c9a143] transition shadow-lg w-full md:w-auto text-center">
                VOLVER A RETROACTIVOS
            </a>
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Modal detalle eliminados -->
<div id="detalleModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-[9999] flex items-center justify-center hidden p-4">
    <div class="bg-[#112134] border border-red-600/30 rounded-3xl shadow-2xl p-4 md:p-6 relative w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b border-red-600/30 pb-3">
            <h2 class="text-xl font-bold text-red-400" id="detalleTitulo">Detalle de Eliminados</h2>
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
                        <th class="px-2 py-2">Mes</th>
                        <th class="px-2 py-2">Neto</th>
                        <th class="px-2 py-2">Eliminado Por</th>
                        <th class="px-2 py-2">Fecha</th>
                        <th class="px-2 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody id="detalleCuerpo">
                </tbody>
            </table>
        </div>
        <div class="mt-4 pt-3 border-t border-red-600/30 flex justify-end">
            <button type="button" onclick="closeDetalleModal()"
                class="bg-white/10 text-white px-6 py-2 rounded-xl font-bold hover:bg-white/20 transition">CERRAR</button>
        </div>
    </div>
</div>

<!-- Modal confirmar restauracion -->
<div id="restaurarModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-[99999] flex items-center justify-center hidden p-4">
    <div class="bg-[#112134] border border-green-600/50 rounded-3xl shadow-2xl p-6 relative w-full max-w-md">
        <div class="flex justify-between items-center mb-4 border-b border-green-600/30 pb-3">
            <h2 class="text-xl font-bold text-green-400">Restaurar Retroactivo</h2>
            <button type="button" onclick="closeRestaurarModal()" class="text-white/50 hover:text-white text-2xl">&times;</button>
        </div>
        <p class="text-white/70 mb-4">¿Está seguro de restaurar este retroactivo? Regresará a la lista de retroactivos.</p>
        <form id="restaurarForm" method="POST" action="">
            @csrf
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeRestaurarModal()"
                    class="bg-white/10 text-white px-6 py-2 rounded-xl font-bold hover:bg-white/20 transition">CANCELAR</button>
                <button type="submit"
                    class="bg-green-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-green-500 transition">RESTAURAR</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal confirmar eliminacion permanente -->
<div id="destroyModal"
    class="bg-black/60 backdrop-blur-sm fixed inset-0 z-[99999] flex items-center justify-center hidden p-4">
    <div class="bg-[#112134] border border-red-600/50 rounded-3xl shadow-2xl p-6 relative w-full max-w-md">
        <div class="flex justify-between items-center mb-4 border-b border-red-600/30 pb-3">
            <h2 class="text-xl font-bold text-red-400">Eliminacion Permanente</h2>
            <button type="button" onclick="closeDestroyModal()" class="text-white/50 hover:text-white text-2xl">&times;</button>
        </div>
        <p class="text-white/70 mb-4">¿Está seguro de eliminar permanentemente este registro? Esta accion no se puede deshacer.</p>
        <form id="destroyForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeDestroyModal()"
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
function openDetalleModal(grupo) {
    document.getElementById('detalleTitulo').textContent = 'Detalle de Eliminados - ' + grupo.nombre;
    
    const tbody = document.getElementById('detalleCuerpo');
    tbody.innerHTML = '';
    
    grupo.eliminados.forEach(item => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-white/5 border-b border-[#d8c495]/10';
        
        const estadoClass = {
            'pagado': 'text-green-400',
            'parcial': 'text-yellow-400',
            'pendiente': 'text-red-400'
        }[item.estado] || 'text-white';
        
        const fechaEliminacion = item.updated_at ? new Date(item.updated_at).toLocaleDateString('es-MX') : 'N/A';
        
        tr.innerHTML = `
            <td class="px-2 py-2 hidden">${item.id || ''}</td>
            <td class="px-2 py-2 text-white/70 font-mono text-[10px]">${item.uuid ? item.uuid.substring(0, 8) + '...' : 'N/A'}</td>
            <td class="px-2 py-2 text-white">${grupo.nombre}</td>
            <td class="px-2 py-2 text-white/70">${item.proyecto || 'Sin proyecto'}</td>
            <td class="px-2 py-2 ${estadoClass} font-bold uppercase text-xs">${item.estado || 'N/A'}</td>
            <td class="px-2 py-2 text-white/70">${item.mes_pago || 'N/A'}</td>
            <td class="px-2 py-2 text-red-400 font-bold text-right">$${parseFloat(item.saldo_neto || 0).toFixed(2)}</td>
            <td class="px-2 py-2 text-white/50 text-xs">${item.eliminado_por || 'N/A'}</td>
            <td class="px-2 py-2 text-white/50 text-xs">${fechaEliminacion}</td>
            <td class="px-2 py-2">
                <div class="flex gap-1">
                    <button type="button" onclick="openRestaurarModal(${item.id})"
                        class="bg-green-800/50 hover:bg-green-700/50 text-green-300 px-2 py-1 rounded-lg text-xs font-bold transition border border-green-600/30">
                        REST.
                    </button>
                    <button type="button" onclick="openDestroyModal(${item.id})"
                        class="bg-red-800/50 hover:bg-red-700/50 text-red-300 px-2 py-1 rounded-lg text-xs font-bold transition border border-red-600/30">
                        DEST.
                    </button>
                </div>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
    
    document.getElementById('detalleModal').classList.remove('hidden');
}

function closeDetalleModal() {
    document.getElementById('detalleModal').classList.add('hidden');
}

function openRestaurarModal(id) {
    const form = document.getElementById('restaurarForm');
    form.action = '/retroactivo/eliminados/' + id + '/restaurar';
    document.getElementById('restaurarModal').classList.remove('hidden');
}

function closeRestaurarModal() {
    document.getElementById('restaurarModal').classList.add('hidden');
}

function openDestroyModal(id) {
    const form = document.getElementById('destroyForm');
    form.action = '/retroactivo/eliminados/' + id + '/destroy';
    document.getElementById('destroyModal').classList.remove('hidden');
}

function closeDestroyModal() {
    document.getElementById('destroyModal').classList.add('hidden');
}
</script>
@endpush
