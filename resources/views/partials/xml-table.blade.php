<div class="max-w-7xl mx-auto mt-10 px-4">
    <!-- Tabla de resultados -->
    <div class="overflow-x-auto rounded-2xl shadow-lg border border-gray-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gradient-to-r from-gray-100 to-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">📄 Archivo XML</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">🔑 UUID</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">📌 Estado</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">⚠️ Errores</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($xmlFiles as $xmlFile)
                    <tr class="hover:bg-gray-50 transition">
                        <!-- Archivo -->
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $xmlFile->filename }}
                        </td>

                        <!-- UUID -->
                        <td class="px-6 py-4 font-mono text-xs text-gray-700">
                            @if($xmlFile->uuid)
                                {{ $xmlFile->uuid }}
                            @else
                                <span class="text-red-500">No encontrado</span>
                            @endif
                        </td>

                        <!-- Estado -->
                        <td class="px-6 py-4">
                            @if($xmlFile->is_valid)
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">
                                    ✅ Válido
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">
                                    ❌ Inválido
                                </span>
                            @endif
                        </td>

                        <!-- Errores -->
                        <td class="px-6 py-4">
                            @if(!$xmlFile->is_valid && $xmlFile->validation_errors)
                                <ul class="list-disc list-inside text-red-700 space-y-1">
                                    @foreach($xmlFile->validation_errors as $error)
                                        <li>
                                            <span class="font-semibold">{{ $error['Campo'] ?? 'Campo desconocido' }}:</span> 
                                            {{ $error['Error Detectado'] ?? '' }}. 
                                            <i class="text-gray-600">{{ $error['Corrección Sugerida'] ?? '' }}</i>
                                        </li>
                                        
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif

                            
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
    </div>

</div>
