@extends('layouts.admin')

@section('content')
<div class="p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-[#d8c495] uppercase tracking-wide">Pagos en Efectivo</h2>
        <button onclick="document.getElementById('modal-nuevo-pago').classList.remove('hidden')"
            class="bg-[#d8c495] text-[#112134] font-bold px-4 py-2 rounded-lg hover:bg-[#c9b47e] transition-all text-sm">
            + Registrar Pago
        </button>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="/efectivo" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-400 mb-1">Mes</label>
            <input type="month" name="mes" value="{{ request('mes') }}"
                class="bg-white/10 border border-[#d8c495]/30 text-white rounded-lg px-3 py-2 text-sm focus:outline-none">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Inversionista</label>
            <select name="id_user" class="bg-[#0b1624] border border-[#d8c495]/30 text-white rounded-lg px-3 py-2 text-sm focus:outline-none">
                <option value="">Todos</option>
                @foreach($usuarios as $u)
                    <option value="{{ $u->id }}" {{ request('id_user') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-[#d8c495]/20 border border-[#d8c495]/40 text-[#d8c495] px-4 py-2 rounded-lg text-sm hover:bg-[#d8c495]/30 transition-all">
            Filtrar
        </button>
        <a href="/efectivo" class="text-gray-400 text-sm py-2 hover:text-white transition-colors">Limpiar</a>
    </form>

    {{-- Tabla de pagos --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha Pago</th>
                    <th>Inversionista</th>
                    <th>Contrato #</th>
                    <th>Mes</th>
                    <th>Monto</th>
                    <th>Concepto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pagos as $pago)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                        <td>{{ $pago->nombre_usuario }}</td>
                        <td>{{ $pago->id_contract }}</td>
                        <td>{{ $pago->mes_pago }}</td>
                        <td class="font-semibold text-green-400">$ {{ number_format($pago->monto, 2) }}</td>
                        <td class="text-gray-300 text-xs">{{ $pago->concepto ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-400 py-8">No hay pagos en efectivo registrados.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($pagos->count() > 0)
            <tfoot>
                <tr class="border-t-2 border-[#d8c495]/30">
                    <td colspan="4" class="text-right font-bold text-[#d8c495] pr-4">Total</td>
                    <td class="font-bold text-green-400">$ {{ number_format($total, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@push('modals')
{{-- Modal Registrar Pago --}}
<div id="modal-nuevo-pago" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-[#112134] border border-[#d8c495]/30 rounded-2xl shadow-2xl w-full max-w-lg mx-4">
        <div class="flex items-center justify-between p-5 border-b border-[#d8c495]/20">
            <h3 class="text-lg font-bold text-[#d8c495]">Registrar Pago en Efectivo</h3>
            <button onclick="document.getElementById('modal-nuevo-pago').classList.add('hidden')"
                class="text-gray-400 hover:text-white transition-colors text-2xl leading-none">&times;</button>
        </div>

        <form action="{{ route('admin.efectivo.store') }}" method="POST" class="p-5 space-y-4">
            @csrf

            <div>
                <label class="block text-xs text-gray-400 mb-1">Cuenta pendiente</label>
                <select name="id_cuentas_por_pagar" id="select-cuenta" required
                    class="w-full bg-[#0b1624] border border-[#d8c495]/30 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#d8c495]">
                    <option value="">Seleccionar cuenta...</option>
                    @foreach($cuentasPendientes as $c)
                        <option value="{{ $c->id_cuentas_por_pagar }}"
                            data-saldo="{{ $c->saldo_pendiente }}">
                            {{ $c->nombre_usuario }} — {{ $c->mes_pago }} — Pendiente: ${{ number_format($c->saldo_pendiente, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-400 mb-1">Monto</label>
                <input type="number" name="monto" id="input-monto" step="0.01" min="0.01" required
                    class="w-full bg-white/10 border border-[#d8c495]/30 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#d8c495]"
                    placeholder="0.00">
            </div>

            <div>
                <label class="block text-xs text-gray-400 mb-1">Fecha de pago</label>
                <input type="date" name="fecha_pago" value="{{ date('Y-m-d') }}" required
                    class="w-full bg-white/10 border border-[#d8c495]/30 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#d8c495]">
            </div>

            <div>
                <label class="block text-xs text-gray-400 mb-1">Concepto (opcional)</label>
                <textarea name="concepto" rows="2" maxlength="500"
                    class="w-full bg-white/10 border border-[#d8c495]/30 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#d8c495]"
                    placeholder="Descripción del pago..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-nuevo-pago').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-gray-400 hover:text-white transition-colors">Cancelar</button>
                <button type="submit"
                    class="bg-[#d8c495] text-[#112134] font-bold px-5 py-2 rounded-lg hover:bg-[#c9b47e] transition-all text-sm">
                    Guardar Pago
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.getElementById('select-cuenta').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const saldo = selected.getAttribute('data-saldo');
        if (saldo) {
            document.getElementById('input-monto').value = parseFloat(saldo).toFixed(2);
        } else {
            document.getElementById('input-monto').value = '';
        }
    });
</script>
@endpush
@endsection
