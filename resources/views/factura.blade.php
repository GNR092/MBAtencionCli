@extends('layouts.user-simple')

@section('content')
<div class="w-full flex flex-col min-h-screen">

    {{-- Header Minimalista (INTACTO) --}}
    <nav class="flex justify-between items-center mb-16 px-2">
        <a href="/vista-usuario"
            class="group flex items-center gap-4 text-[10px] tracking-[0.4em] uppercase text-white/40 hover:text-dorado-400 transition-all duration-700">
            <span class="text-lg group-hover:-translate-x-2 transition-transform duration-500">←</span>
            <span>Volver al Panel</span>
        </a>
        <div class="h-px flex-1 mx-10 bg-linear-to-r from-[#8B6B23]/40 to-transparent"></div>
        <span class="text-[9px] text-dorado-400 tracking-[0.5em] uppercase opacity-70">
            MB Signature Properties •
        </span>
    </nav>

    {{-- Hero Section (INTACTO) --}}
    <header class="mb-20 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">01</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                Factura<span class="font-light">ción</span><span class="text-dorado-400 animate-pulse">_</span>
            </h1>
        </div>
        <p class="text-white/20 text-xs tracking-[0.3em] uppercase mt-6 ml-12">
            Gestión y validación de comprobantes fiscales digitales
        </p>
    </header>

    {{-- Dashboard de Carga (ESTILO TABLA-DORADA RESTAURADO) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 px-2 mb-20">

        {{-- Panel Izquierdo: Formulario (Tarjeta Blanca Estilo Tabla) --}}
        <div class="lg:col-span-8 bg-white rounded-2xl shadow-xl border border-carbon-200 overflow-hidden">

            <div class="bg-carbon-900 px-6 py-4 border-b-2 border-dorado-400 flex justify-between items-center">
                <h2 class="text-dorado-400 text-lg font-bold uppercase tracking-widest flex items-center gap-2">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                        </path>
                    </svg>
                    Carga de Documentación XML
                </h2>
            </div>

            <div class="p-8"> @if(!$batch || ($batch && $batch->total_files === 0))
                <form id="xmlForm" method="POST" action="{{ route('upload-xml') }}" enctype="multipart/form-data"
                    class="space-y-8">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-xs font-bold text-carbon-900 uppercase mb-2">Correo
                                Institucional</label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <input type="email" name="user_email" required
                                    class="block w-full border border-gray-300 rounded-lg pl-10 pr-3 py-3 text-carbon-900 focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors bg-white shadow-sm"
                                    placeholder="usuario@mbsignature.com" value="{{ $user->email ?? '' }}" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-carbon-900 uppercase mb-2">Asignación de
                                Proyecto</label>
                            <div class="relative">
                                <select name="proyect" id="proyect" required
                                    class="block w-full border border-gray-300 rounded-lg pl-3 pr-10 py-3 text-carbon-900 bg-white focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors appearance-none cursor-pointer shadow-sm">
                                    <option value="" disabled selected>Seleccionar Proyecto...</option>
                                    @foreach($proyectos as $proyecto)
                                        <option value="{{ $proyecto->id_proyecto }}">{{ $proyecto->nombre_proyecto }}</option>
                                    @endforeach
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="relative group">
                        <label class="block text-xs font-bold text-carbon-900 uppercase mb-2">Archivos XML</label>
                        <div id="dropzone"
                            class="w-full h-48 rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 flex flex-col justify-center items-center group-hover:border-dorado-400 group-hover:bg-dorado/5 transition-all duration-300 cursor-pointer overflow-hidden relative">

                            <div id="dropzone_text" class="text-center">
                                <div
                                    class="mb-3 p-3 rounded-full bg-white shadow-sm group-hover:scale-110 transition-transform duration-300 relative z-10 inline-block">
                                    <svg style="width: 32px; height: 32px;" class="text-dorado" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                        </path>
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-carbon-900 uppercase tracking-wider mb-1 relative z-10">
                                    Arrastra tus archivos aquí</p>
                                <p class="text-xs text-gray-500 relative z-10">Solo archivos .xml permitidos</p>
                            </div>
                            
                            <div id="file_list_container" class="hidden w-full p-4 overflow-y-auto max-h-full">
                                <!-- File list will be injected here -->
                            </div>

                            <input type="file" id="xml_input" name="xml_files[]" accept=".xml" multiple required
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button type="submit"
                            class="bg-dorado-400 text-white px-6 py-3 rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-dorado/90 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center gap-2">
                            <span>Validar Documentación</span>
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>

        {{-- Panel Derecho: Información (Tarjeta Oscura Elegante) --}}
        <div class="lg:col-span-4 flex flex-col h-full">
            <div
                class="bg-carbon-900 rounded-2xl shadow-xl border border-carbon-200 p-8 flex flex-col h-full relative overflow-hidden">

                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-dorado-400 rounded-full opacity-10 blur-3xl">
                </div>

                <h3
                    class="text-dorado-400 text-sm tracking-[0.2em] uppercase font-bold mb-8 border-b border-dorado/30 pb-4">
                    Guía de Usuario
                </h3>

                <ul class="space-y-8 relative z-10">
                    <li class="flex gap-4 items-start group">
                        <span
                            class="text-dorado-400 font-serif italic text-2xl leading-none opacity-50 group-hover:opacity-100 transition">01</span>
                        <p
                            class="text-gray-300 text-xs leading-relaxed tracking-wide uppercase group-hover:text-white transition">
                            Verifique que el CFDI sea <strong class="text-white">versión 4.0</strong> para evitar
                            rechazos del sistema.
                        </p>
                    </li>
                    <li class="flex gap-4 items-start group">
                        <span
                            class="text-dorado-400 font-serif italic text-2xl leading-none opacity-50 group-hover:opacity-100 transition">02</span>
                        <p
                            class="text-gray-300 text-xs leading-relaxed tracking-wide uppercase group-hover:text-white transition">
                            El sistema validará el UUID contra la base de datos de <strong class="text-white">MB
                                Signature</strong>.
                        </p>
                    </li>
                </ul>

                <div class="mt-auto pt-12 text-center opacity-10">
                    <div
                        class="text-4xl font-extralight text-white leading-none tracking-tighter uppercase select-none">
                        MB Signature<br>Properties
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Tutorial (INTACTO) --}}
    <footer class="grid grid-cols-1 lg:grid-cols-12 gap-20 px-2 py-20 border-t border-white/5">
        <div class="lg:col-span-4">
            <h4 class="text-dorado-400 text-[10px] tracking-[0.4em] uppercase mb-6">Mesa de Ayuda</h4>
            <p class="text-white/20 text-xs font-light leading-relaxed">
                Si el sistema detecta inconsistencias en sus archivos, consulte el videotutorial de soporte técnico para
                la corrección de errores de estructura fiscal.
            </p>
        </div>
        <div class="lg:col-span-8 group relative bg-black/40 p-2 overflow-hidden border border-white/5">
            <video controls
                class="w-full aspect-video grayscale opacity-40 group-hover:opacity-100 group-hover:grayscale-0 transition-all duration-1000 shadow-2xl">
                <source src="videos/tutorial_errores.mp4" type="video/mp4">
                Tu navegador no soporta la reproducción de video.
            </video>
        </div>
    </footer>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const xmlInput = document.getElementById('xml_input');
        const dropzone = document.getElementById('dropzone');
        const dropzoneText = document.getElementById('dropzone_text');
        const fileListContainer = document.getElementById('file_list_container');

        xmlInput.addEventListener('change', function() {
            updateFileList();
        });

        // Optional: Drag and drop functionality
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropzone.classList.add('border-dorado-400', 'bg-dorado/5');
        });

        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropzone.classList.remove('border-dorado-400', 'bg-dorado/5');
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropzone.classList.remove('border-dorado-400', 'bg-dorado/5');
            xmlInput.files = e.dataTransfer.files;
            updateFileList();
        });

        function updateFileList() {
            if (xmlInput.files.length > 0) {
                dropzoneText.classList.add('hidden');
                fileListContainer.classList.remove('hidden');
                let fileListHtml = '<ul class="list-none text-center space-y-2">';
                for (let i = 0; i < xmlInput.files.length; i++) {
                    fileListHtml += `<li class="text-xs text-carbon-700 bg-dorado-200/20 border border-dorado-400/30 rounded-md px-3 py-2 flex items-center justify-between">
                        <span class="font-mono">${xmlInput.files[i].name}</span>
                        <span class="text-gray-500 text-2xs">${(xmlInput.files[i].size / 1024).toFixed(2)} KB</span>
                    </li>`;
                }
                fileListHtml += '</ul>';
                fileListContainer.innerHTML = fileListHtml;
            } else {
                dropzoneText.classList.remove('hidden');
                fileListContainer.classList.add('hidden');
                fileListContainer.innerHTML = '';
            }
        }
    });
</script>
@endsection