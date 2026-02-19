@extends('layouts.admin')

@section('content')
<div class="p-6">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                Impuestos<span class="font-light text-dorado"></span><span class="text-dorado-400 animate-pulse">_</span>
            </h1>
        </div>
    </header>

    <!-- Barra de búsqueda -->
    <form method="GET" action="{{ route('impuestos.index') }}" class="tabla-dorada-search">
        @csrf
        <label for="searchInput">BUSCAR POR:</label>
        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Buscar..." class="flex-1 min-w-[160px]">
        <select name="categoria" id="categoria">
            <option value="proyecto" {{ request('categoria') == 'proyecto' ? 'selected' : '' }}>Proyecto</option>
            <option value="departamento" {{ request('categoria') == 'departamento' ? 'selected' : '' }}>Departamento</option>
            <option value="inversionista" {{ request('categoria') == 'inversionista' ? 'selected' : '' }}>Inversionista</option>
        </select>
        <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-[#0d1f30] px-6 py-2 rounded-xl font-bold text-sm uppercase tracking-wider transition">
            BUSCAR
        </button>
        <a href="{{ route('impuestos.limpiar') }}" class="bg-white/10 hover:bg-white/20 text-white px-6 py-2 rounded-xl text-sm font-bold transition">
            LIMPIAR
        </a>
        <button type="button" onClick="openModalDescarga()" class="bg-[#d8c495] hover:bg-[#c9a143] text-[#0d1f30] px-6 py-2 rounded-xl font-bold text-sm uppercase tracking-wider transition">
            DESCARGAR
        </button>
    </form>

    <!-- Tabla -->
    <div class="tabla-dorada-container">
        <div class="overflow-x-auto custom-scroll">
            <table class="tabla-dorada">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>UUID</th>
                        <th>Fecha</th>
                        <th>Proyecto</th>
                        <th>Departamento</th>
                        <th>Inversionista</th>
                        <th>Tipo Factor</th>
                        <th>Régimen Fiscal</th>
                        <th>Importe Base</th>
                        <th>Importe ISR</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($xmlFiles as $file)
                    <tr>
                        <td class="font-bold text-[#d8c495]">{{ $file->id }}</td>
                        <td class="text-xs font-mono text-white/60">{{ Str::limit($file->uuid, 8) }}</td>
                        <td class="text-white/70 text-xs">{{ $file->created_at }}</td>
                        <td class="text-white/80">{{ $file->proyecto ?? '—' }}</td>
                        <td class="text-white/80">{{ $file->departamento }}</td>
                        <td class="font-medium">{{ $file->emisor_name }}</td>
                        <td class="text-white/70">{{ $file->tipoFactor }}</td>
                        <td class="text-white/70">{{ $file->regimenFiscal ?? '—' }}</td>
                        <td class="font-medium">${{ number_format($file->importeBase, 2) }}</td>
                        <td class="font-medium text-red-400">${{ number_format($file->isr, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-10 text-white/40 italic">No se encontraron facturas</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="9" class="text-right px-6 py-3 uppercase text-xs tracking-wider text-white/60">TOTAL BASE:</td>
                        <td class="px-4 py-3 font-black text-[#d8c495]">${{ number_format($totalBase, 2) }}</td>
                    </tr>
                    <tr class="border-t border-[#d8c495]/10">
                        <td colspan="9" class="text-right px-6 py-3 uppercase text-xs tracking-wider text-white/60">TOTAL ISR RETENIDO:</td>
                        <td class="px-4 py-3 font-black text-red-400">${{ number_format($totalISR, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="tabla-dorada-footer">
            {{ $xmlFiles->links('pagination::tailwind') }}
        </div>
    </div>

    <!-- Modal descarga -->
    <div id="descargaModal" class="bg-black/60 backdrop-blur-sm fixed inset-0 z-[9999] flex items-center justify-center hidden p-4">
        <div class="bg-[#112134] border border-[#d8c495]/20 rounded-3xl shadow-2xl p-6 md:p-10 w-full max-w-md">
            <h2 class="text-xl font-bold mb-6 text-center text-[#d8c495]">Configuración de Descarga</h2>
            <form action="{{ route('impuestos.export') }}" method="POST" class="space-y-5">
                @csrf
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
                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit" class="bg-[#c9a143] text-white py-3 rounded-xl font-bold shadow-lg hover:bg-[#b08b35] transition">
                        DESCARGAR EXCEL
                    </button>
                    <button type="button" onclick="closeModalDescarga()" class="text-white/50 font-bold hover:text-white transition">CANCELAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModalDescarga() { document.getElementById("descargaModal").classList.remove("hidden"); }
function closeModalDescarga() { document.getElementById("descargaModal").classList.add("hidden"); }
</script>
@endsection
