@extends('layouts.user')

@section('content')
<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-[#d8c495] uppercase tracking-wide">Mis Pagos en Efectivo</h2>
        <div class="bg-[#112134]/60 border border-[#d8c495]/20 rounded-xl px-4 py-2 text-sm">
            <span class="text-gray-400">Total recibido:</span>
            <span class="font-bold text-green-400 ml-2">$ {{ number_format($total, 2) }}</span>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="table-container overflow-x-auto rounded-xl border border-[#d8c495]/10 bg-[#112134]/40">
        <table class="w-full min-w-[500px]">
            <thead>
                <tr>
                    <th class="bg-[#0b1624] text-[#d8c495] px-4 py-3 text-left text-xs uppercase tracking-wide font-semibold">Fecha</th>
                    <th class="bg-[#0b1624] text-[#d8c495] px-4 py-3 text-left text-xs uppercase tracking-wide font-semibold">Mes</th>
                    <th class="bg-[#0b1624] text-[#d8c495] px-4 py-3 text-right text-xs uppercase tracking-wide font-semibold">Monto</th>
                    <th class="bg-[#0b1624] text-[#d8c495] px-4 py-3 text-left text-xs uppercase tracking-wide font-semibold">Concepto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pagos as $pago)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="px-4 py-3 text-white text-sm">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-300 text-sm">{{ $pago->mes_pago }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-green-400">$ {{ number_format($pago->monto, 2) }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $pago->concepto ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-400 py-10">No tienes pagos en efectivo registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($pagos->hasPages())
        <div class="flex justify-center">
            {{ $pagos->links() }}
        </div>
    @endif

</div>
@endsection
