@extends('layouts.BackFactura')
@section('content')

<div class="max-w-5xl mx-auto py-8 px-4 space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-light text-dorado-400 tracking-widest uppercase">Detalle de Factura</h1>
        <a href="{{ route('user.factura.reset') }}"
            class="text-sm text-white/50 hover:text-white border border-white/10 rounded-lg px-4 py-2 transition">
            &larr; Regresar
        </a>
    </div>

    {{-- Mensaje de advertencia si el proyecto no coincide --}}
    @if(isset($projectMismatch) && $projectMismatch)
    <div class="bg-red-800/20 border border-red-700 rounded-xl p-4 text-red-200">
        <p class="font-bold">Advertencia: El proyecto asociado a esta factura no coincide o no se detectó en la
            descripción.</p>
        <p class="text-sm">Proyecto detectado en la descripción: <span
                class="font-bold uppercase tracking-widest text-dorado-200">{{ $parsedProjectName ?? 'No detectado' }}</span>
        </p>
        <p class="text-sm">Proyecto seleccionado: <span
                class="font-bold uppercase tracking-widest text-dorado-200">{{ $selectedProjectName ?? 'N/A' }}</span>
        </p>
        <p class="text-xs mt-2 text-white/60 italic">Por favor, elimine esta factura y solicite una factura con el formato de descripción correcto.</p>
    </div>
    @endif

    {{-- Mensaje de advertencia si el usuario no coincide --}}
    @if(isset($userMismatch) && $userMismatch)
    <div class="bg-orange-800/20 border border-orange-700 rounded-xl p-4 text-orange-200">
        <p class="font-bold">Advertencia: El emisor de la factura no coincide con su nombre de usuario.</p>
        <p class="text-sm">El emisor de esta factura es <span class="font-mono">{{ $factura['emisor_nombre'] }}</span>,
            pero usted está autenticado como <span class="font-mono">{{ $user->name }}</span>.
            Por favor, asegúrese de que la factura sea correcta o elimínela.</p>
    </div>
    @endif

    {{-- Advertencia: UUID duplicado --}}
    @if(isset($uuidExists) && $uuidExists)
    <div class="bg-yellow-800/20 border border-yellow-600 rounded-xl p-4 text-yellow-200">
        <p class="font-bold">Advertencia: Este UUID ya existe en la base de datos.</p>
        <p class="text-sm">El UUID <span class="font-mono text-yellow-100">{{ $factura['uuid'] }}</span> ya fue registrado previamente.
            Elimine esta factura para evitar duplicados.</p>
    </div>
    @endif

    {{-- Aviso: Factura Retroactiva --}}
    @if(isset($retroactivo) && $retroactivo)
    <div class="bg-orange-800/20 border border-orange-600 rounded-xl p-4 text-orange-200">
        <p class="font-bold">Factura Retroactiva</p>
        <p class="text-sm">Esta factura corresponde a un periodo diferente al actual. Se marcará como retroactiva en el sistema.</p>
    </div>
    @endif

    {{-- Advertencia: Meses Mezclados --}}
    @if(isset($hayMesesMezclados) && $hayMesesMezclados)
    <div class="bg-blue-800/20 border border-blue-600 rounded-xl p-4 text-blue-200">
        <p class="font-bold">Factura con Meses Mezclados</p>
        <p class="text-sm">Esta factura contiene conceptos de {{ count($periodosDetectados) }} meses diferentes ({{ implode(', ', $periodosDetectados) }}). Se creará una cuenta por cobrar por cada mes.</p>
    </div>
    @endif

    {{-- Datos detectados en la descripción --}}
    <div class="bg-carbon-900 border border-white/10 rounded-xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-white/10 pb-3">
            <h2 class="text-lg font-semibold text-dorado-200 tracking-wide uppercase">
                Datos de Descripción
            </h2>
            <button type="button" onclick="toggleDatosDescripcion()" class="flex items-center space-x-2 text-white/60 hover:text-white transition">
                <span class="text-sm" id="datos-descripcion-label">Mostrar</span>
                <svg id="datos-descripcion-icon" class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
        
        <div id="datos-descripcion-content" class="hidden space-y-4">
            <p class="text-white/60 text-sm">Datos detectados automáticamente de la descripción del concepto. Verifique que sean correctos.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Proyecto --}}
                <div class="bg-white/5 rounded-lg p-4 {{ $projectMismatch ? 'border border-red-500' : '' }}">
                    <span class="block text-white/40 uppercase text-xs tracking-wider">Proyecto</span>
                    @if($projectMismatch)
                    <span class="text-red-400 font-medium text-xs">{{ $parsedProjectName }}</span>
                    @else
                    <span class="text-green-400 font-semibold text-sm">{{ $parsedProjectName }}</span>
                    @endif
                </div>

            {{-- Departamento --}}
            <div class="bg-white/5 rounded-lg p-4 {{ $departamentoMissing ? 'border border-red-500' : '' }}">
                <span class="block text-white/40 uppercase text-xs tracking-wider">Departamento</span>
                @if($departamentoMissing)
                <span class="text-red-400 font-medium">No detectado</span>
                @else
                <span class="text-green-400 font-semibold">{{ $departamentoText }}</span>
                @if($multipleDepartamentos ?? false)
                <span class="ml-2 text-xs text-yellow-400">(mezclados)</span>
                @endif
                @endif
            </div>

            {{-- Mes --}}
            <div class="bg-white/5 rounded-lg p-4 {{ $mesMissing ? 'border border-red-500' : '' }}">
                <span class="block text-white/40 uppercase text-xs tracking-wider">Mes</span>
                @if($mesMissing)
                <span class="text-red-400 font-medium">No detectado</span>
                @else
                <span class="text-green-400 font-semibold">{{ $parsedMes }}</span>
                @if($multipleMeses ?? false)
                <span class="ml-2 text-xs text-yellow-400">(mezclados)</span>
                @endif
                @if(isset($hayMesesMezclados) && $hayMesesMezclados && isset($gruposFactura) && isset($gruposFactura[0]))
                    <span class="ml-2 px-1.5 py-0.5 text-xs rounded {{ $gruposFactura[0]['retroactivo'] ? 'bg-orange-800 text-orange-200' : 'bg-green-800 text-green-200' }}">
                        {{ $gruposFactura[0]['periodo'] }}
                    </span>
                @endif
                @endif
            </div>

            {{-- Año --}}
            <div class="bg-white/5 rounded-lg p-4 {{ $anioMissing ? 'border border-red-500' : '' }}">
                <span class="block text-white/40 uppercase text-xs tracking-wider">Año</span>
                @if($anioMissing)
                <span class="text-red-400 font-medium">No detectado</span>
                @else
                <span class="text-green-400 font-semibold">{{ $parsedAnio }}</span>
                @if($multipleAnios ?? false)
                <span class="ml-2 text-xs text-yellow-400">(mezclados)</span>
                @endif
                @endif
            </div>
        </div>

        {{-- Cuenta Predial (Opcional) --}}
        @if($folioPredial)
        <div class="mt-4 bg-white/5 rounded-lg p-4">
            <span class="block text-white/40 uppercase text-xs tracking-wider">Cuenta Predial</span>
            <span class="text-blue-400 font-semibold">{{ $folioPredial }}</span>
        </div>
        @endif

        {{-- Grupos de meses adicionales (cuando hay meses mezclados) --}}
        @if(isset($hayMesesMezclados) && $hayMesesMezclados && isset($gruposFactura) && count($gruposFactura) > 1)
        <div class="mt-4 border-t border-white/10 pt-4">
            <h3 class="text-sm font-semibold text-dorado-200 mb-3">Periodos Adicionales Detectados</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($gruposFactura as $index => $grupo)
                    @if($index > 0)
                    <div class="bg-white/5 rounded-lg p-3 {{ $grupo['retroactivo'] ? 'border border-orange-500/50' : '' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-white/40 uppercase tracking-wider">Periodo</span>
                            @if($grupo['retroactivo'])
                            <span class="px-2 py-0.5 text-xs bg-orange-800 text-orange-200 rounded">Retroactivo</span>
                            @else
                            <span class="px-2 py-0.5 text-xs bg-green-800 text-green-200 rounded">Actual</span>
                            @endif
                        </div>
                        <p class="text-lg font-bold text-dorado-200">{{ $grupo['periodo'] }}</p>
                        @if(!empty($grupo['departamentos']))
                        <p class="text-xs text-white/60 mt-1">
                            Deptos: {{ implode(', ', $grupo['departamentos']) }}
                        </p>
                        @endif
                        <p class="text-sm text-white/70 mt-1">
                            Total: <span class="text-dorado-200 font-semibold">${{ number_format($grupo['total'], 2) }}</span>
                        </p>
                        <p class="text-xs text-white/50 mt-1">
                            {{ count($grupo['conceptos']) }} concepto(s)
                        </p>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        @if($projectMismatch || $departamentoMissing || $mesMissing || $anioMissing)
        <div class="bg-red-800/20 border border-red-700 rounded-lg p-4 text-red-200">
            <p class="font-bold">Faltan datos obligatorios en la descripción:</p>
            <ul class="text-sm mt-2 list-disc list-inside">
                @if($projectMismatch)
                <li>Proyecto: No coincide o no se detectó. Ejemplo: "Campus University City", "Aldea Borboleta"</li>
                @endif
                @if($departamentoMissing)
                <li>Departamento: Ejemplo "Depto A3" o "Departamento 2203"</li>
                @endif
                @if($mesMissing)
                <li>Mes: Ejemplo "Enero 2025" o "Septiembre de 2025"</li>
                @endif
                @if($anioMissing)
                <li>Año: Ejemplo "Enero 2025"</li>
                @endif
            </ul>
            <p class="text-xs mt-2 text-white/60 italic">Por favor, elimine esta factura y solicite una factura con el formato de descripción correcto.</p>
        </div>
        @endif
        </div>
    </div>

    {{-- Navegación y Confirmación/Eliminación --}}
    <div class="flex items-center justify-between mt-4">
        <div class="flex items-center space-x-4">
            @if($index > 0)
            <a href="{{ route('user.factura.view', ['index' => $index - 1]) }}"
                class="text-sm text-white/50 hover:text-white border border-white/10 rounded-lg px-4 py-2 transition">
                &larr; Anterior
            </a>
            @endif
            <span class="text-white text-lg font-semibold">Factura {{ $index + 1 }} de {{ $totalFacturas }}</span>
            @if($index < $totalFacturas - 1) <a href="{{ route('user.factura.view', ['index' => $index + 1]) }}"
                class="text-sm text-white/50 hover:text-white border border-white/10 rounded-lg px-4 py-2 transition">
                Siguiente &rarr;
                </a>
                @endif
        </div>
        <div class="flex space-x-2">
            @if((isset($projectMismatch) && $projectMismatch) || (isset($userMismatch) && $userMismatch) || (isset($uuidExists) && $uuidExists))
            <form action="{{ route('user.factura.delete', ['index' => $index]) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-red-700 transition"
                    onclick="return confirm('¿Estás seguro de que deseas eliminar esta factura? Esta acción no se puede deshacer.')">
                    Eliminar Factura
                </button>
            </form>
            @endif
            <form action="{{ route('user.factura.confirm', ['index' => $index]) }}" method="POST">
                @csrf
                @php
                $isInvalid = (isset($projectMismatch) && $projectMismatch) || (isset($userMismatch) && $userMismatch) || (isset($uuidExists) && $uuidExists);
                @endphp
                <button type="submit"
                    class="bg-dorado-200 text-carbon-900 font-bold py-2 px-6 rounded-lg hover:bg-[#c2ae84] transition {{ $isInvalid ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ $isInvalid ? 'disabled' : '' }}>
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
                        <td class="py-3 px-2 text-white font-mono">
                            {{ $concepto['clave_prod_serv'] }}
                            @if(isset($hayMesesMezclados) && $hayMesesMezclados && !empty($concepto['periodo']))
                                <span class="ml-2 px-1.5 py-0.5 text-xs rounded {{ $concepto['periodo'] < date('Y-m') ? 'bg-orange-800 text-orange-200' : 'bg-green-800 text-green-200' }}">
                                    {{ $concepto['periodo'] }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-2 text-white max-w-xs">{{ $concepto['descripcion'] }}</td>
                        <td class="py-3 px-2 text-white text-center">{{ $concepto['cantidad'] }}</td>
                        <td class="py-3 px-2 text-white">{{ $concepto['unidad'] }}</td>
                        <td class="py-3 px-2 text-white">{{ $concepto['objeto_imp'] }}</td>
                        <td class="py-3 px-2 text-white text-right">${{ number_format($concepto['valor_unitario'], 2) }}
                        </td>
                        <td class="py-3 px-2 text-dorado-200  font-semibold text-right">
                            ${{ number_format($concepto['importe'], 2) }}</td>
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
                                    ({{ $traslado['tasa'] * 100 }}%) =
                                    ${{ number_format($traslado['importe'] ?? 0, 2) }}
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
                            Folio Predial: <span
                                class="text-white/60 font-mono">{{ $concepto['cuenta_predial'] }}</span>
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
                    <span class="text-white">{{ $traslado['impuesto'] == '002' ? 'IVA' : $traslado['impuesto'] }} -
                        {{ $traslado['tipo_factor'] }}</span>
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

    {{-- Subir PDF de Factura --}}
    <div class="bg-carbon-900 border border-white/10 rounded-xl p-6 space-y-4">
        <h2 class="text-lg font-semibold text-dorado-200  tracking-wide uppercase border-b border-white/10 pb-3">
            PDF de la Factura
        </h2>
        
        <div class="bg-blue-900/20 border border-blue-700 rounded-lg p-4 text-blue-200">
            <p class="text-sm">
                <strong>Por favor suba el PDF correspondiente a la factura XML</strong> y verifique que los datos son correctos.
            </p>
        </div>

        <div id="pdf-upload-section">
            @if($pdfUploaded)
            <div class="bg-green-900/20 border border-green-700 rounded-lg p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-green-200 font-medium">PDF subido correctamente</p>
                            <p class="text-green-300/60 text-sm">{{ $pdfFilename }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('pdf-file-input').click()" class="text-sm text-green-300 hover:text-green-200 underline">
                        Cambiar PDF
                    </button>
                </div>
                
                <div class="flex space-x-3 pt-2">
                    <a href="{{ route('user.factura.view-pdf', ['index' => $index]) }}" 
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-dorado-200 text-carbon-900 text-sm font-medium rounded-lg hover:bg-[#c2ae84] transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Visualizar PDF
                    </a>
                </div>
            </div>
            @else
            <div class="border-2 border-dashed border-white/20 rounded-lg p-8 text-center hover:border-dorado-200/50 transition" 
                 id="pdf-drop-zone"
                 onclick="document.getElementById('pdf-file-input').click()">
                <svg class="mx-auto h-12 w-12 text-white/40" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mt-2 text-white/60 text-sm">Haga clic para seleccionar el archivo PDF</p>
                <p class="text-white/40 text-xs mt-1">Máximo 20MB</p>
            </div>
            @endif

            <input type="file" id="pdf-file-input" accept="application/pdf" class="hidden" onchange="uploadPdf(this)">
            
            <div id="pdf-upload-loading" class="hidden mt-4 text-center">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-dorado-200"></div>
                <p class="text-white/60 text-sm mt-2">Subiendo PDF...</p>
            </div>

            <div id="pdf-upload-error" class="hidden mt-4 bg-red-900/20 border border-red-700 rounded-lg p-3 text-red-200 text-sm"></div>
        </div>
    </div>

</div>

{{-- Modal de Confirmación --}}
<div id="confirm-modal" class="fixed inset-0 bg-black/70 hidden z-50 flex items-center justify-center">
    <div class="bg-carbon-900 border border-white/10 rounded-xl p-6 max-w-md mx-4">
        <h3 class="text-lg font-semibold text-dorado-200 mb-4">Confirmar Factura</h3>
        <p class="text-white/80 mb-6">
            ¿El PDF y los datos de la factura son correctos?<br>
            <span class="text-white/60 text-sm">Confirme que sí, si no vuelva a subir el PDF.</span>
        </p>
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeConfirmModal()" class="px-4 py-2 text-white/60 hover:text-white transition">
                Cancelar
            </button>
            <button type="button" onclick="submitConfirmForm()" class="bg-dorado-200 text-carbon-900 font-bold py-2 px-6 rounded-lg hover:bg-[#c2ae84] transition">
                Sí, confirmar
            </button>
        </div>
    </div>
</div>

<script>
let confirmForm = null;

function uploadPdf(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    if (file.type !== 'application/pdf') {
        showPdfError('Por favor seleccione un archivo PDF válido.');
        return;
    }

    if (file.size > 20 * 1024 * 1024) {
        showPdfError('El archivo excede el límite de 20MB.');
        return;
    }

    document.getElementById('pdf-upload-loading').classList.remove('hidden');
    document.getElementById('pdf-upload-error').classList.add('hidden');

    const formData = new FormData();
    formData.append('pdf_file', file);

    fetch('{{ route("user.factura.upload-pdf", ["index" => $index]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('pdf-upload-loading').classList.add('hidden');
        if (data.success) {
            location.reload();
        } else {
            showPdfError(data.message || 'Error al subir el PDF.');
        }
    })
    .catch(error => {
        document.getElementById('pdf-upload-loading').classList.add('hidden');
        showPdfError('Error de conexión. Intente de nuevo.');
    });
}

function showPdfError(message) {
    const errorDiv = document.getElementById('pdf-upload-error');
    errorDiv.textContent = message;
    errorDiv.classList.remove('hidden');
}

function showConfirmModal(event, form) {
    event.preventDefault();
    confirmForm = form;
    document.getElementById('confirm-modal').classList.remove('hidden');
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
    confirmForm = null;
}

function submitConfirmForm() {
    if (confirmForm) {
        confirmForm.submit();
    }
}

function toggleDatosDescripcion() {
    const content = document.getElementById('datos-descripcion-content');
    const icon = document.getElementById('datos-descripcion-icon');
    const label = document.getElementById('datos-descripcion-label');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
        label.textContent = 'Ocultar';
    } else {
        content.classList.add('hidden');
        icon.classList.remove('rotate-180');
        label.textContent = 'Mostrar';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const confirmButton = document.querySelector('form[action*="confirm"] button[type="submit"]');
    if (confirmButton) {
        const form = confirmButton.closest('form');
        confirmButton.addEventListener('click', function(e) {
            @if(!$pdfUploaded)
            e.preventDefault();
            alert('Debe subir el PDF de la factura antes de confirmar.');
            return false;
            @elseif($projectMismatch || $departamentoMissing || $mesMissing || $anioMissing)
            e.preventDefault();
            alert('Faltan datos obligatorios en la descripción (proyecto, departamento, mes o año). Por favor elimine esta factura y solicite una con el formato de descripción correcto.');
            return false;
            @else
            showConfirmModal(e, form);
            @endif
        });
    }
});
</script>

@endsection