@extends('layouts.user-simple')

@section('content')
<div class="w-full flex flex-col min-h-screen">

    <nav class="flex justify-between items-center mb-12 px-2">
        <a href="{{ route('user.dashboard') }}"
            class="group flex items-center gap-4 text-[10px] tracking-[0.4em] uppercase text-white/40 hover:text-[#d8c495] transition-all duration-700">
            <span class="text-lg group-hover:-translate-x-2 transition-transform duration-500">←</span>
            <span class="text-[#d8c495]">Volver al Panel</span>
        </a>
        <div class="h-px flex-1 mx-10 bg-gradient-to-r from-[#d8c495]/30 to-transparent"></div>
        <span class="text-[9px] text-[#d8c495] tracking-[0.5em] uppercase opacity-70">
            MB Signature Properties •
        </span>
    </nav>

    <header class="mb-16 px-2">
        <div class="flex items-baseline gap-4">
            <h1 class="page-title">
                Facturación
            </h1>
        </div>
        <p class="text-white/40 text-sm tracking-[0.25em] uppercase mt-6 ml-12">
            Gestión y validación de comprobantes fiscales digitales
        </p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 px-2 mb-16">

        <div class="lg:col-span-8 rounded-2xl overflow-hidden"
            style="background: rgba(13,31,48,0.85); backdrop-filter: blur(12px); box-shadow: 0 4px 24px rgba(0,0,0,0.25), 0 0 0 1px rgba(216,196,149,0.15);">

            <div class="px-6 py-4 flex justify-between items-center"
                style="border-bottom: 1px solid rgba(216,196,149,0.25);">
                <h2 class="text-[#d8c495] text-base font-semibold tracking-widest flex items-center gap-3">
                    <span class="p-1.5 rounded-lg bg-[#d8c495]/10">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                    </span>
                    Carga de Documentación XML
                </h2>
            </div>

            <div class="p-8">
                @if(!$batch || ($batch && $batch->total_files === 0))
                <form id="xmlForm" method="POST" action="{{ route('facturacion.subir-xml') }}" enctype="multipart/form-data"
                    class="space-y-8">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[11px] font-semibold uppercase mb-2.5 tracking-wide"
                                style="color: rgba(216,196,149,0.8);">
                                Correo Institucional
                            </label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none" style="color: rgba(216,196,149,0.4);">
                                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <input type="email" name="user_email" required
                                    class="block w-full rounded-lg pl-10 pr-4 py-3 transition-all duration-200"
                                    style="background: rgba(255,255,255,0.08); border: 1px solid rgba(216,196,149,0.25); color: #d8c495;"
                                    placeholder="usuario@mbsignature.com" value="{{ $user->email ?? '' }}" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold uppercase mb-2.5 tracking-wide"
                                style="color: rgba(216,196,149,0.8);">
                                Asignación de Proyecto
                            </label>
                            @if(!empty($facturacionNotice))
                            <div class="mb-3 px-4 py-3 rounded-lg text-xs leading-relaxed" style="background: rgba(217, 119, 6, 0.12); border: 1px solid rgba(217, 119, 6, 0.3); color: #d8c495;">
                                <strong class="font-semibold">Atención:</strong> {{ $facturacionNotice }}
                            </div>
                            @endif
                            <div class="relative">
                                <select name="proyect" id="proyect" {{ $proyectos->isEmpty() ? 'disabled' : 'required' }}
                                    class="block w-full rounded-lg pl-3 pr-10 py-3 transition-all duration-200 appearance-none cursor-pointer"
                                    style="background: rgba(255,255,255,0.08); border: 1px solid rgba(216,196,149,0.25); color: #d8c495;">
                                    <option value="" disabled selected>Seleccionar Proyecto...</option>
                                    @if($proyectos->isEmpty())
                                        <option value="" disabled>No hay proyectos disponibles</option>
                                    @endif
                                    @foreach($proyectos as $proyecto)
                                        <option value="{{ $proyecto->id_proyecto }}">{{ $proyecto->nombre_proyecto }}</option>
                                    @endforeach
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none" style="color: rgba(216,196,149,0.5);">
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
                        <label class="block text-[11px] font-semibold uppercase mb-2.5 tracking-wide"
                            style="color: rgba(216,196,149,0.8);">
                            Archivos XML
                        </label>
                        <div id="dropzone"
                            class="w-full h-44 rounded-xl flex flex-col justify-center items-center cursor-pointer overflow-hidden relative transition-all duration-300"
                            style="border: 2px dashed rgba(216,196,149,0.35); background: rgba(216,196,149,0.04);"
                            onmouseover="this.style.borderColor='rgba(216,196,149,0.65)';this.style.background='rgba(216,196,149,0.08)'"
                            onmouseout="this.style.borderColor='rgba(216,196,149,0.35)';this.style.background='rgba(216,196,149,0.04)'">

                            <div id="dropzone_text" class="text-center px-4">
                                <div class="mb-4 p-3.5 rounded-xl transition-transform duration-300 relative z-10 inline-block"
                                    style="background: rgba(216,196,149,0.12); box-shadow: 0 2px 12px rgba(216,196,149,0.15);">
                                    <svg style="width: 28px; height: 28px;" class="text-[#d8c495]" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                        </path>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold uppercase tracking-wider mb-1.5 relative z-10"
                                    style="color: rgba(216,196,149,0.9);">
                                    Arrastra tus archivos aquí</p>
                                <p class="text-xs relative z-10" style="color: rgba(216,196,149,0.45);">Solo archivos .xml permitidos</p>
                            </div>

                            <div id="file_list_container" class="hidden w-full p-4 overflow-y-auto max-h-full">
                            </div>

                            <input type="file" id="xml_input" name="xml_files[]" accept=".xml" multiple required
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        </div>
                    </div>

                    <div class="flex justify-end pt-6" style="border-top: 1px solid rgba(216,196,149,0.15);">
                        <button type="submit"
                            {{ $proyectos->isEmpty() ? 'disabled' : '' }}
                            class="text-white px-8 py-3.5 rounded-lg text-xs font-bold uppercase tracking-widest flex items-center gap-2.5 transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed"
                            style="background: #d8c495;"
                            onmouseover="this.style.background='#c4a87a'"
                            onmouseout="this.style.background='#d8c495'">
                            <span>Validar Documentación</span>
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor"
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

        <div class="lg:col-span-4 flex flex-col h-full">
            <div class="bg-[#0d1f30] rounded-2xl p-8 flex flex-col h-full relative overflow-hidden"
                style="box-shadow: 0 4px 24px rgba(0,0,0,0.2); border: 1px solid rgba(216,196,149,0.12);">

                <div class="absolute top-0 right-0 w-40 h-40 bg-[#d8c495] rounded-full opacity-[0.06] blur-3xl"></div>

                <h3 class="text-[#d8c495] text-xs tracking-[0.25em] uppercase font-semibold mb-6 pb-4"
                    style="border-bottom: 1px solid rgba(216,196,149,0.2);">
                    Guía de Usuario
                </h3>

                <ul class="space-y-6 relative z-10">
                    <li class="flex gap-4 items-start group">
                        <span class="text-[#d8c495]/40 font-serif italic text-xl leading-none transition-opacity duration-300 group-hover:opacity-80">01</span>
                        <p class="text-white/50 text-[11px] leading-relaxed tracking-wide uppercase transition-colors duration-300 group-hover:text-white/70">
                            Verifique que el CFDI sea <strong class="text-white/80 font-semibold">versión 4.0</strong> para evitar
                            rechazos del sistema.
                        </p>
                    </li>
                    <li class="flex gap-4 items-start group">
                        <span class="text-[#d8c495]/40 font-serif italic text-xl leading-none transition-opacity duration-300 group-hover:opacity-80">02</span>
                        <p class="text-white/50 text-[11px] leading-relaxed tracking-wide uppercase transition-colors duration-300 group-hover:text-white/70">
                            El sistema validará el UUID contra la base de datos de <strong class="text-white/80 font-semibold">MB
                                Signature</strong>.
                        </p>
                    </li>
                </ul>

                <div class="mt-auto pt-10 text-center opacity-[0.08]">
                    <div class="text-3xl font-extralight text-white leading-none tracking-tighter uppercase select-none">
                        MB Signature<br>Properties
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="grid grid-cols-1 lg:grid-cols-12 gap-16 px-2 py-16" style="border-top: 1px solid rgba(255,255,255,0.05);">
        <div class="lg:col-span-4">
            <h4 class="text-[#d8c495] text-[10px] tracking-[0.4em] uppercase mb-4 opacity-70">Mesa de Ayuda</h4>
            <p class="text-white/20 text-xs font-light leading-relaxed">
                Si el sistema detecta inconsistencias en sus archivos, consulte el videotutorial de soporte técnico para
                la corrección de errores de estructura fiscal.
            </p>
        </div>
        <div class="lg:col-span-8 group relative overflow-hidden rounded-xl" style="border: 1px solid rgba(255,255,255,0.05);">
            <video controls
                class="w-full aspect-video transition-all duration-700"
                style="filter: grayscale(100%) opacity(0.35);"
                onmouseover="this.style.filter='grayscale(0%) opacity(1)'"
                onmouseout="this.style.filter='grayscale(100%) opacity(0.35)'">
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

        let dt = new DataTransfer();

        xmlInput.addEventListener('change', function() {
            addFiles(this.files);
        });

        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'rgba(216,196,149,0.7)';
            this.style.background = 'rgba(216,196,149,0.06)';
        });

        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = 'rgba(216,196,149,0.35)';
            this.style.background = 'rgba(216,196,149,0.04)';
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = 'rgba(216,196,149,0.35)';
            this.style.background = 'rgba(216,196,149,0.04)';
            addFiles(e.dataTransfer.files);
        });

        function addFiles(newFiles) {
            for (let i = 0; i < newFiles.length; i++) {
                let alreadyExists = false;
                for (let j = 0; j < dt.files.length; j++) {
                    if (dt.files[j].name === newFiles[i].name) {
                        alreadyExists = true;
                        break;
                    }
                }
                if (!alreadyExists) {
                    dt.items.add(newFiles[i]);
                }
            }
            xmlInput.files = dt.files;
            updateFileList();
        }

        window.removeFile = function(index) {
            dt.items.remove(index);
            xmlInput.files = dt.files;
            updateFileList();
        };

        function updateFileList() {
            if (dt.files.length > 0) {
                dropzoneText.classList.add('hidden');
                fileListContainer.classList.remove('hidden');
                let fileListHtml = '<ul class="list-none space-y-2 w-full">';
                for (let i = 0; i < dt.files.length; i++) {
                    fileListHtml += `<li class="text-xs rounded-lg px-3 py-2.5 flex items-center gap-3" style="background: rgba(216,196,149,0.08); border: 1px solid rgba(216,196,149,0.2);">
                        <span class="font-mono truncate flex-1" style="color: rgba(216,196,149,0.9);">${dt.files[i].name}</span>
                        <span class="shrink-0" style="color: rgba(216,196,149,0.45);">${(dt.files[i].size / 1024).toFixed(2)} KB</span>
                        <button type="button" onclick="removeFile(${i})"
                            class="shrink-0 w-5 h-5 flex items-center justify-center rounded-full transition-all duration-200 font-bold leading-none z-30 relative"
                            style="color: #d8c495;"
                            onmouseover="this.style.color='#dc2626';this.style.background='rgba(220,38,38,0.15)'"
                            onmouseout="this.style.color='#d8c495';this.style.background='transparent'"
                            title="Eliminar archivo">✕</button>
                    </li>`;
                }
                fileListHtml += '</ul>';
                fileListHtml += '<p class="text-center text-[10px] mt-3 tracking-wider uppercase" style="color: rgba(216,196,149,0.35);">Haz clic para agregar más archivos</p>';
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
