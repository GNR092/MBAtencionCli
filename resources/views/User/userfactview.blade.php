@extends('layouts.user-simple')
@section('content')

<div class="max-w-5xl mx-auto py-8 px-4 space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-light text-dorado-400 tracking-widest uppercase">Detalle de Factura</h1>
        <a href="{{ route('user.factura.nueva') }}" onclick="return confirm('Si regresas, se borrarán las facturas subidas. ¿Deseas continuar?')"
           class="text-sm text-white/50 hover:text-white border border-white/10 rounded-lg px-4 py-2 transition">
            &larr; Regresar
        </a>
    </div>

    {{-- Mensaje de advertencia si el proyecto no coincide --}}
    @if(isset($projectMismatch) && $projectMismatch)
    <div class="bg-red-800/20 border border-red-700/50 rounded-xl p-4 text-red-300">
        <p class="font-bold">Advertencia: El proyecto asociado a esta factura no coincide.</p>
        <p class="text-sm">Esta factura está vinculada al proyecto <span class="font-mono">{{ $parsedProjectId ?? 'N/A' }}</span> según su descripción,
            pero el proyecto seleccionado es <span class="font-mono">{{ $selectedProjectId ?? 'N/A' }}</span>. Por favor, elimina esta factura o regresa para seleccionar el proyecto correcto.</p>
    </div>
    @endif

    {{-- Navegación y Confirmación/Eliminación --}}
    <div class="flex items-center justify-between mt-4">
        <div class="flex items-center space-x-4">
            @if($index > 0)
                <a href="{{ route('user.factura.view', ['index' => $index - 1]) }}" class="text-sm text-white/50 hover:text-white border border-white/10 rounded-lg px-4 py-2 transition">
                    &larr; Anterior
                </a>
            @endif
            <span class="text-white text-lg font-semibold">Factura {{ $index + 1 }} de {{ $totalFacturas }}</span>
            @if($index < $totalFacturas - 1)
                <a href="{{ route('user.factura.view', ['index' => $index + 1]) }}" class="text-sm text-white/50 hover:text-white border border-white/10 rounded-lg px-4 py-2 transition">
                    Siguiente &rarr;
                </a>
            @endif
        </div>
        <div class="flex space-x-2">
            @if(isset($projectMismatch) && $projectMismatch)
                <form action="{{ route('user.factura.delete', ['index' => $index]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-red-700 transition"
                            onclick="return confirm('¿Estás seguro de que deseas eliminar esta factura? Esta acción no se puede deshacer.')">
                        Eliminar Factura
                    </button>
                </form>
            @endif
            <form action="{{ route('user.factura.confirm', ['index' => $index]) }}" method="POST">
                @csrf
                <button type="submit" class="bg-dorado-200 text-carbon-900 font-bold py-2 px-6 rounded-lg hover:bg-[#c2ae84] transition {{ (isset($projectMismatch) && $projectMismatch) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ (isset($projectMismatch) && $projectMismatch) ? 'disabled' : '' }}>
                    Confirmar Factura
                </button>
            </form>
        </div>
    </div>

    {{-- Datos Generales del Comprobante --}}
    <div class="bg-carbon-900 border border-white/10 rounded-xl p-6 space-y-4">
        <h2 class="text-lg font-semibold text-dorado-200 tracking-widest uppercase border-b border-white/10 pb-3">
            Datos del Comprobante
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="block text-white/40 uppercase text-xs tracking-wider">Folio</span>
                <span class="text-white">{{ $factura['folio'] ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-white/40 uppercase text-xs tracking-wider">Fecha</span>
                <span class="text-white">{{ $factura['fecha'] ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-white/40 uppercase text-xs tracking-wider">Forma de Pago</span>
                <span class="text-white">{{ $factura['forma_pago'] ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-white/40 uppercase text-xs tracking-wider">No. Certificado</span>
                <span class="text-white font-mono text-xs break-all">{{ $factura['no_certificado'] ?? 'N/A' }}</span>
            </div>
        </div>
        <div>
            <span class="block text-white/40 uppercase text-xs tracking-wider mb-1">SubTotal</span>
            <span class="text-white text-lg font-semibold">${{ number_format($factura['subtotal'] ?? 0, 2) }} MXN</span>
        </div>
        <div>
            <span class="block text-white/40 uppercase text-xs tracking-wider mb-1">Total</span>
            <span class="text-dorado-200  text-2xl font-bold">${{ number_format($factura['total'] ?? 0, 2) }} MXN</span>
        </div>
    </div>

    {{-- Emisor y Receptor --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Emisor --}}
        <div class="bg-carbon-900 border border-white/10 rounded-xl p-6 space-y-3">
            <h2 class="text-lg font-semibold text-dorado-200  tracking-wide uppercase border-b border-white/10 pb-3">
                Emisor
            </h2>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="block text-white/40 uppercase text-xs tracking-wider">RFC</span>
                    <span class="text-white font-mono">{{ $factura['emisor_rfc'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-white/40 uppercase text-xs tracking-wider">Nombre</span>
                    <span class="text-white">{{ $factura['emisor_nombre'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-white/40 uppercase text-xs tracking-wider">Regimen Fiscal</span>
                    <span class="text-white">{{ $factura['emisor_regimen'] ?? 'N/A' }}</span>   
                </div>
            </div>
        </div>

        {{-- Receptor --}}
        <div class="bg-carbon-900 border border-white/10 rounded-xl p-6 space-y-3">
            <h2 class="text-lg font-semibold text-dorado-200  tracking-wide uppercase border-b border-white/10 pb-3">
                Receptor
            </h2>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="block text-white/40 uppercase text-xs tracking-wider">RFC</span>
                    <span class="text-white font-mono">{{ $factura['receptor_rfc'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-white/40 uppercase text-xs tracking-wider">Nombre</span>
                    <span class="text-white">{{ $factura['receptor_nombre'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-white/40 uppercase text-xs tracking-wider">Domicilio Fiscal</span>
                    <span class="text-white">{{ $factura['receptor_domicilio'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-white/40 uppercase text-xs tracking-wider">Uso CFDI</span>
                    <span class="text-white">{{ $factura['receptor_uso_cfdi'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Conceptos --}}
    <div class="bg-carbon-900 border border-white/10 rounded-xl p-6 space-y-4">
        <h2 class="text-lg font-semibold text-dorado-200  tracking-wide uppercase border-b border-white/10 pb-3">
            Conceptos
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-white/10 text-white/40 uppercase text-xs tracking-wider">
                        <th class="py-3 px-2">Clave Prod/Serv</th>
                        <th class="py-3 px-2">Descripcion</th>
                        <th class="py-3 px-2 text-center">Cantidad</th>
                        <th class="py-3 px-2">Unidad</th>
                        <th class="py-3 px-2">Objeto Imp.</th>
                        <th class="py-3 px-2 text-right">Valor Unitario</th>
                        <th class="py-3 px-2 text-right">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($factura['conceptos'] as $concepto)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition">
                        <td class="py-3 px-2 text-white font-mono">{{ $concepto['clave_prod_serv'] }}</td>
                        <td class="py-3 px-2 text-white max-w-xs">{{ $concepto['descripcion'] }}</td>
                        <td class="py-3 px-2 text-white text-center">{{ $concepto['cantidad'] }}</td>
                        <td class="py-3 px-2 text-white">{{ $concepto['unidad'] }}</td>
                        <td class="py-3 px-2 text-white">{{ $concepto['objeto_imp'] }}</td>
                        <td class="py-3 px-2 text-white text-right">${{ number_format($concepto['valor_unitario'], 2) }}</td>
                        <td class="py-3 px-2 text-dorado-200  font-semibold text-right">${{ number_format($concepto['importe'], 2) }}</td>
                    </tr>

                    {{-- Impuestos del concepto --}}
                    @if(!empty($concepto['traslados']) || !empty($concepto['retenciones']))
                    <tr class="bg-white/2">
                        <td colspan="7" class="py-2 px-4">
                            <div class="flex flex-wrap gap-4 text-xs text-white/50">
                                @foreach($concepto['traslados'] ?? [] as $traslado)
                                <span>
                                    Traslado: {{ $traslado['impuesto'] == '002' ? 'IVA' : $traslado['impuesto'] }}
                                    - {{ $traslado['tipo_factor'] }}
                                    @if($traslado['tipo_factor'] !== 'Exento')
                                        ({{ $traslado['tasa'] * 100 }}%) = ${{ number_format($traslado['importe'] ?? 0, 2) }}
                                    @endif
                                </span>
                                @endforeach
                                @foreach($concepto['retenciones'] ?? [] as $retencion)
                                <span class="text-red-400/70">
                                    Retencion: {{ $retencion['impuesto'] == '001' ? 'ISR' : 'IVA' }}
                                    ({{ $retencion['tasa'] * 100 }}%) = -${{ number_format($retencion['importe'], 2) }}
                                </span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endif

                    {{-- Cuenta Predial --}}
                    @if(!empty($concepto['cuenta_predial']))
                    <tr class="bg-white/2">
                        <td colspan="7" class="py-1 px-4 text-xs text-white/40">
                            Folio Predial: <span class="text-white/60 font-mono">{{ $concepto['cuenta_predial'] }}</span>
                        </td>
                    </tr>
                    @endif

                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Impuestos Totales --}}
    <div class="bg-carbon-900 border border-white/10 rounded-xl p-6 space-y-4">
        <h2 class="text-lg font-semibold text-dorado-200  tracking-wide uppercase border-b border-white/10 pb-3">
            Impuestos
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Traslados --}}
            <div class="space-y-2">
                <h3 class="text-sm text-white/40 uppercase tracking-wider">Traslados</h3>
                @foreach($factura['impuestos_traslados'] ?? [] as $traslado)
                <div class="flex justify-between text-sm bg-white/5 rounded-lg px-4 py-2">
                    <span class="text-white">{{ $traslado['impuesto'] == '002' ? 'IVA' : $traslado['impuesto'] }} - {{ $traslado['tipo_factor'] }}</span>
                    <span class="text-white font-mono">
                        @if($traslado['tipo_factor'] === 'Exento')
                            Exento
                        @else
                            ${{ number_format($traslado['importe'] ?? 0, 2) }}
                        @endif
                    </span>
                </div>
                @endforeach
            </div>

            {{-- Retenciones --}}
            <div class="space-y-2">
                <h3 class="text-sm text-white/40 uppercase tracking-wider">Retenciones</h3>
                @foreach($factura['impuestos_retenciones'] ?? [] as $retencion)
                <div class="flex justify-between text-sm bg-white/5 rounded-lg px-4 py-2">
                    <span class="text-white">{{ $retencion['impuesto'] == '001' ? 'ISR' : 'IVA' }}</span>
                    <span class="text-red-400 font-mono">-${{ number_format($retencion['importe'], 2) }}</span>
                </div>
                @endforeach

                @if(!empty($factura['total_retenciones']))
                <div class="flex justify-between text-sm font-semibold border-t border-white/10 pt-2 mt-2">
                    <span class="text-white/60">Total Retenciones</span>
                    <span class="text-red-400 font-mono">-${{ number_format($factura['total_retenciones'], 2) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Timbre Fiscal Digital --}}
    <div class="bg-carbon-900 border border-white/10 rounded-xl p-6 space-y-4">
        <h2 class="text-lg font-semibold text-dorado-200  tracking-wide uppercase border-b border-white/10 pb-3">
            Timbre Fiscal Digital
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="block text-white/40 uppercase text-xs tracking-wider">UUID</span>
                <span class="text-dorado-200  font-mono text-xs">{{ $factura['uuid'] ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-white/40 uppercase text-xs tracking-wider">Fecha Timbrado</span>
                <span class="text-white">{{ $factura['fecha_timbrado'] ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-white/40 uppercase text-xs tracking-wider">No. Certificado SAT</span>
                <span class="text-white font-mono text-xs">{{ $factura['no_certificado_sat'] ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-white/40 uppercase text-xs tracking-wider">RFC Proveedor</span>
                <span class="text-white font-mono">{{ $factura['rfc_prov_certif'] ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

</div>

@endsection