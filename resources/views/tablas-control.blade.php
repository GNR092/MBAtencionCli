@extends('layouts.admin')

@section('content')
    <div class="w-full p-4 md:p-6 animate-fadeInUp">
        <div class="max-w-full mx-auto">
            <header class="mb-10 px-2">
                <div class="flex items-baseline gap-4">
                    <span class="text-dorado-400 text-sm font-serif italic">|</span>
                    <h1 class="text-white text-5xl md:text-7xl font-extralight tracking-[-0.02em] leading-none uppercase">
                        Tablas de control
                    </h1>
                </div>
                <p class="text-white/60 mt-4 text-sm">Mes correspondiente: {{ $selectedMonth }}</p>
            </header>

            <div class="tabla-dorada-container">
                <div class="overflow-x-auto custom-scroll">
                    <table class="tabla-dorada">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>RAZON SOCIAL</th>
                                <th>PROYECTO</th>
                                <th>TIPO</th>
                                <th>DEPARTAMENTO</th>
                                <th>Cuenta Predial</th>
                                <th>FORMA DE PAGO</th>
                                <th>NOMBRE INVERSIONISTA</th>
                                <th>IMPORTE RENTA</th>
                                <th>RETENCION ISR</th>
                                <th>IMPORTE NETO</th>
                                <th>MES</th>
                                <th>PAGADO / NO PAGADO</th>
                                {{-- <th>RFC</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registros as $registro)
                                <tr>
                                    <td>{{ $registro->id_cuentas_por_pagar }}</td>
                                    <td>{{ $registro->razon_social }}</td>
                                    <td>{{ $registro->proyecto }}</td>
                                    <td>{{ $registro->tipo_departamento }}</td>
                                    <td>{{ $registro->departamento }}</td>
                                    <td>{{ $registro->cuenta_predial }}</td>
                                    <td>{{ $registro->metodo_pago ?? 'N/A' }}</td>
                                    <td>{{ $registro->inversionista ?? 'N/A' }}</td>
                                    <td>${{ number_format((float) ($registro->importe_renta ?? 0), 2) }}</td>
                                    <td>${{ number_format((float) ($registro->isr ?? 0), 2) }}</td>
                                    <td>${{ number_format((float) ($registro->saldo_neto ?? 0), 2) }}</td>
                                    <td>{{ $registro->mes_pago ?? 'N/A' }}</td>
                                    <td>
                                        <span class="{{ $registro->estado === 'pagado' ? 'text-green-400' : 'text-red-400' }} font-semibold">
                                            {{ $registro->estado === 'pagado' ? 'PAGADO' : 'NO PAGADO' }}
                                        </span>
                                    </td>
                                    {{-- <td>{{ $registro->rfc_oculto }}</td> --}}
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="py-10 text-white/40 italic">No se encontraron registros para el mes seleccionado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="10" class="text-right px-6 py-3 uppercase text-xs tracking-wider text-white/60">Total Neto:</td>
                                <td class="px-4 py-3 text-[#d8c495] font-bold">${{ number_format($totalNeto, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                            <tr class="border-t border-[#d8c495]/10">
                                <td colspan="10" class="text-right px-6 py-3 uppercase text-xs tracking-wider text-white/60">Total Pagado:</td>
                                <td class="px-4 py-3 text-green-400">${{ number_format($totalPagado, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                            <tr class="border-t border-[#d8c495]/10">
                                <td colspan="10" class="text-right px-6 py-3 uppercase text-xs tracking-wider text-white/60">Total Pendiente:</td>
                                <td class="px-4 py-3 text-red-400">${{ number_format($totalPendiente, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
