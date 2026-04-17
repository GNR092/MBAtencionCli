@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-full mx-auto space-y-6">
        <header class="px-2 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="flex items-baseline gap-4">
                    <span class="text-dorado-400 text-sm font-serif italic">|</span>
                    <h1 class="page-title">
                        Tablas de control
                    </h1>
                </div>
                <p class="text-white/55 mt-3 text-sm">Concentrado mensual para seguimiento financiero y estatus de pago por inversionista.</p>
            </div>
            <div class="bg-black/20 border border-[#d8c495]/15 rounded-xl px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.2em] text-[#d8c495]/70 font-bold">Mes en revision</p>
                <p class="text-white font-mono text-lg">{{ $selectedMonth }}</p>
            </div>
        </header>

        <div class="px-2">
            <div class="bg-[#112134]/55 border border-[#d8c495]/15 rounded-xl p-2 overflow-x-auto custom-scroll">
                <div class="flex gap-2 min-w-max">
                    @foreach($tabs as $tabKey => $tabLabel)
                        <a
                            href="{{ route('tablas-control.index', ['month' => $selectedMonth, 'tab' => $tabKey]) }}"
                            class="px-4 py-2 rounded-lg text-[11px] md:text-xs uppercase tracking-[0.14em] transition-all whitespace-nowrap {{ $activeTab === $tabKey ? 'bg-[#d8c495] text-[#0d1f30] font-bold' : 'bg-black/20 text-white/65 border border-white/10 hover:text-white hover:border-[#d8c495]/30' }}"
                        >
                            {{ $tabLabel }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        @if($pendingTabLabel)
            <div class="tabla-dorada-container">
                <div class="p-10 text-center text-white/70">
                    <p class="text-xs uppercase tracking-[0.24em] text-[#d8c495] font-bold">{{ $pendingTabLabel }}</p>
                    <p class="mt-3 text-sm">Esta seccion ya esta preparada en navegacion; su logica de datos se definira en una siguiente iteracion.</p>
                </div>
            </div>
        @else
            @php
                $registrosCount = $registros->count();
                $pagadosCount = $registros->where('estado', 'pagado')->count();
                $pendientesCount = max($registrosCount - $pagadosCount, 0);
                $avanceCobro = $totalNeto > 0 ? ($totalPagado / $totalNeto) * 100 : 0;
            @endphp

            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 px-2">
                <article class="bg-[#112134]/55 border border-[#d8c495]/15 rounded-xl p-4">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-[#d8c495]/65 font-bold">Total neto</p>
                    <p class="mt-2 text-2xl font-light text-[#d8c495]">${{ number_format($totalNeto, 2) }}</p>
                    <p class="text-[11px] text-white/45 mt-1">Base mensual de liquidacion.</p>
                </article>
                <article class="bg-[#112134]/55 border border-[#d8c495]/15 rounded-xl p-4">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-[#d8c495]/65 font-bold">Liquidado</p>
                    <p class="mt-2 text-2xl font-light text-emerald-300">${{ number_format($totalPagado, 2) }}</p>
                    <p class="text-[11px] text-white/45 mt-1">{{ number_format($avanceCobro, 1) }}% del total del mes.</p>
                </article>
                <article class="bg-[#112134]/55 border border-[#d8c495]/15 rounded-xl p-4">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-[#d8c495]/65 font-bold">Por pagar</p>
                    <p class="mt-2 text-2xl font-light text-red-300">${{ number_format($totalPendiente, 2) }}</p>
                    <p class="text-[11px] text-white/45 mt-1">Saldo pendiente por dispersar.</p>
                </article>
                <article class="bg-[#112134]/55 border border-[#d8c495]/15 rounded-xl p-4">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-[#d8c495]/65 font-bold">Registros</p>
                    <p class="mt-2 text-2xl font-light text-white">{{ $registrosCount }}</p>
                    <p class="text-[11px] text-white/45 mt-1">{{ $pagadosCount }} pagados / {{ $pendientesCount }} pendientes.</p>
                </article>
            </section>

            <div class="px-2">
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.18em] bg-black/20 border border-white/10 rounded-full px-3 py-1 text-white/65">
                        <span class="w-2 h-2 rounded-full bg-emerald-300"></span> Pagado
                    </span>
                    <span class="inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.18em] bg-black/20 border border-white/10 rounded-full px-3 py-1 text-white/65">
                        <span class="w-2 h-2 rounded-full bg-red-300"></span> Pendiente
                    </span>
                </div>
            </div>

            <div class="tabla-dorada-container">
                <div class="overflow-x-auto custom-scroll">
                    <table class="tabla-dorada">
                        <thead>
                            <tr>
                                <th class="col-id">NO</th>
                                <th class="col-text">RAZON SOCIAL</th>
                                <th class="col-text">PROYECTO</th>
                                <th>TIPO</th>
                                <th class="col-text">DEPARTAMENTO</th>
                                <th>CUENTA PREDIAL</th>
                                <th>FORMA DE PAGO</th>
                                <th class="col-text">NOMBRE INVERSIONISTA</th>
                                <th class="col-money">IMPORTE RENTA</th>
                                <th class="col-money">RETENCION ISR</th>
                                <th class="col-money">IMPORTE NETO</th>
                                <th>MES</th>
                                <th>ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registros as $registro)
                                <tr>
                                    <td class="col-id">{{ $registro->id_cuentas_por_pagar }}</td>
                                    <td class="col-text font-medium">{{ $registro->razon_social }}</td>
                                    <td class="col-text text-white/70">{{ $registro->proyecto }}</td>
                                    <td><span class="text-xs opacity-60 uppercase">{{ $registro->tipo_departamento }}</span></td>
                                    <td class="col-text">{{ $registro->departamento }}</td>
                                    <td class="font-mono text-xs opacity-70">{{ $registro->cuenta_predial }}</td>
                                    <td>
                                        <span class="text-[10px] bg-white/5 px-2 py-0.5 rounded border border-white/10 uppercase">
                                            {{ $registro->metodo_pago ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="col-text">{{ $registro->inversionista ?? 'N/A' }}</td>
                                    <td class="col-money text-white/60">${{ number_format((float) ($registro->importe_renta ?? 0), 2) }}</td>
                                    <td class="col-money text-red-400/80">-${{ number_format((float) ($registro->isr ?? 0), 2) }}</td>
                                    <td class="col-money font-bold text-[#d8c495]">${{ number_format((float) ($registro->saldo_neto ?? 0), 2) }}</td>
                                    <td class="text-xs opacity-60">{{ $registro->mes_pago ?? 'N/A' }}</td>
                                    <td>
                                        <span class="status-pill {{ $registro->estado === 'pagado' ? 'status-pagado' : 'status-pendiente' }}">
                                            {{ $registro->estado === 'pagado' ? 'PAGADO' : 'PENDIENTE' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="py-12 text-white/40 italic text-center">No se encontraron registros para el mes seleccionado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
