@extends('layouts.user-simple')

@section('content')
    <a href="/vista-usuario" class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded mx-2">Regresar</a>
    <h1 class="text-center text-black text-3xl p-2">FACTURACIÓN</h1>

    <div class="max-w-6xl mx-auto bg-[#eee] rounded p-2 mt-4 shadow-lg text-black">
        <h1 class="text-2xl font-bold mb-4">Carga de XMLs con Validación UUID (hasta 2)</h1>

        <div class="bg-[#2f2f2f] border border-[#112134] rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-white mb-2">🔒 Sistema de Validación UUID</h3>
            <p class="text-white text-sm">
                Este sistema valida que los XML sean correctos mediante UUID.
            </p>
        </div>

        @php
            $isDeadlinePassed = $isDeadlinePassed ?? false;
            $batch = $batch ?? null;
        @endphp

        @if($isDeadlinePassed)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                La fecha límite ha vencido. Ya no es posible subir archivos.
            </div>
        @endif

        @if(session('success'))
            <div class="max-w-3xl mx-auto mt-6 bg-green-50 border border-green-300 rounded-lg p-4 shadow">
                <p class="text-green-700 font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        @if(!$batch || ($batch && $batch->total_files === 0))
            <form id="xmlForm" method="POST" action="{{ route('upload-xml') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="user_email" class="block text-sm font-medium text-gray-900">Email:</label>
                    <input type="email" name="user_email" required class="border w-70 rounded p-3 text-black" style="color: black !important;">

                    <select name="proyect" id="proyect" class="bg-[#eee] p-3 rounded-lg mx-2 border text-black" required style="color: black !important;">
                        <option value="" disabled selected>Selecciona proyecto</option>
                        <option value="RESIDENT 1">RESIDENT 1</option>
                        <option value="RESIDENT 2">RESIDENT 2</option>
                        <option value="CAMPUS RECIDENCIA">CAMPUS RECIDENCIA</option>
                        <option value="TMZN 122">TMZN 122</option>
                        <option value="GRAND TEMOZON">GRAND TEMOZÓN</option>
                        <option value="MB RESORT MERIDA">MB RESORT MÉRIDA</option>
                        <option value="Princess Village">Princess Village</option>
                        <option value="Royal Square Plaza">Royal Square Plaza</option>
                        <option value="RUM">RUM</option>
                        <option value="Avenue Temozon">Avenue Temozón</option>
                        <option value="MB Resort Orlando">MB Resort Orlando</option>
                        <option value="MB Wellness Resort">MB Wellness Resort</option>
                        <option value="Aldea Borboleta I">Aldea Borboleta I</option>
                        <option value="Aldea Borboleta II">Aldea Borboleta II </option>
                        <option value="Aldea Borboleta III">Aldea Borboleta III</option>
                    </select>
                </div>

                <div>
                    <input type="file" name="xml_files[]" accept=".xml" multiple required class="border p-2 w-full text-black" style="color: black !important;">
                </div>

                @if(!$isDeadlinePassed)
                    <button type="submit" class="bg-yellow-500 text-black px-4 py-2 rounded">Subir y validar XMLs</button>
                @else
                    <button type="button" disabled class="bg-gray-400 text-gray-700 px-4 py-2 rounded cursor-not-allowed">Subir y validar XMLs</button>
                    <p class="text-red-500 text-sm mt-2">La fecha límite ha pasado, no se pueden subir archivos</p>
                @endif
            </form>
        @else
            <h2 class="font-semibold mb-2">Resultados de validación XML</h2>
            @include('partials.xml-table', ['xmlFiles' => $batch->xmlFiles])

            <p class="mt-4 font-semibold">XML válidos: {{ $batch->valid_files }} / {{ $batch->total_files }}</p>


            @if($batch->valid_files > 0 && !$isDeadlinePassed)
            <div class="mt-8 bg-blue-50 border border-blue-300 rounded-lg p-4 shadow">
                <h2 class="text-lg font-semibold text-blue-800 mb-3">
                    📎 Carga de PDF de la factura
                </h2>

                <form method="POST" action="{{ route('upload-pdf') }}" enctype="multipart/form-data">
                    @csrf

                    <input type="file"
                        name="pdf_file"
                        accept=".pdf"
                        required
                        class="border p-2 w-full mb-3 text-black" style="color: black !important;">

                    <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded">
                        Subir PDF
                    </button>
                </form>

                <p class="text-sm text-gray-600 mt-2">
                    El PDF será validado contra el UUID del XML cargado.
                </p>
            </div>
        @endif


            @if(!$isDeadlinePassed)
                <form method="POST" action="{{ route('reset-batch') }}" class="mt-8">
                    @csrf
                    <button type="submit" name="reset_lote" class="bg-red-600 text-white px-4 py-2 rounded">Iniciar nuevo lote</button>
                </form>
            @endif
        @endif

        <!-- Tabla de errores -->
        @if($errors->any())
            <div class="max-w-7xl mx-auto mt-10 px-4">
                <h2 class="text-xl font-semibold text-red-800 mb-4">Errores detectados</h2>
                <div class="overflow-x-auto rounded-2xl shadow-lg border border-gray-200 bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gradient-to-r from-gray-100 to-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">📄 Archivo XML</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-900">⚠️ Error</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($errors->all() as $error)
                                @php
                                    $parts = explode(': ', $error, 2);
                                    $filename = $parts[0] ?? 'Desconocido';
                                    $message  = $parts[1] ?? $error;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $filename }}</td>
                                    <td class="px-6 py-4 text-red-700">{{ $message }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Recursos adicionales -->
        <div class="max-w-3xl mx-auto mt-10 bg-red-50 border border-red-300 rounded-lg p-6 shadow">
            <h2 class="text-xl font-semibold text-red-800 mb-4">¿No sabes cómo corregir los errores?</h2>
            <div class="mb-6">
                <p class="mb-2 text-gray-900">Te dejamos un video guía que explica cómo llenar correctamente tu XML CFDI:</p>
                <video controls class="w-full rounded-lg border border-gray-200 shadow-sm">
                    <source src="videos/tutorial_errores.mp4" type="video/mp4">
                    Tu navegador no soporta la reproducción de video.
                </video>
            </div>
        </div>
    </div>
@endsection
