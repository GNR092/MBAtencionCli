@extends('layouts.user')

@section('content')
<div class="space-y-4">

    {{-- Encabezado discreto --}}
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-xs font-bold text-[#d8c495] uppercase tracking-[0.2em] bg-transparent" style="font-family: 'Montserrat', sans-serif;">Pagos en Efectivo</h2>
        <div class="flex items-center gap-2">
            <span class="text-white/40 text-xs uppercase tracking-wider">Total</span>
            <span class="text-green-400 font-bold tabular-nums">$ {{ number_format($total, 2) }}</span>
        </div>
    </div>

    {{-- Tabla limpia --}}
    <div class="tabla-dorada-container">
        <div class="overflow-x-auto custom-scroll">
            <table class="tabla-dorada">
                <thead>
                    <tr>
                        <th class="col-text text-[10px]">Fecha</th>
                        <th class="col-text text-[10px]">Mes</th>
                        <th class="col-money text-[10px]">Monto</th>
                        <th class="col-text text-[10px]">Concepto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagos as $pago)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="text-white/70 text-xs">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                            <td class="text-white/50 text-xs">{{ $pago->mes_pago }}</td>
                            <td class="col-money text-green-400/90 text-xs">$ {{ number_format($pago->monto, 2) }}</td>
                            <td class="text-white/30 text-[11px]">{{ $pago->concepto ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-white/30 text-xs italic text-center">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($pagos->hasPages())
        <div class="tabla-dorada-footer">
            {{ $pagos->links() }}
        </div>
    @endif

</div>
@endsection
