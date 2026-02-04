@extends('layouts.user-simple')

@section('content')
    <div class="w-full flex flex-col min-h-screen">

        {{-- Header Minimalista --}}
        <nav class="flex justify-between items-center mb-16 px-2">
            <a href="/vista-usuario" class="group flex items-center gap-4 text-[10px] tracking-[0.4em] uppercase text-white/40 hover:text-[#D4A017] transition-all duration-700">
                <span class="text-lg group-hover:-translate-x-2 transition-transform duration-500">←</span>
                <span>Volver al Panel</span>
            </a>
            <div class="h-[1px] flex-1 mx-10 bg-gradient-to-r from-[#8B6B23]/40 to-transparent"></div>
            <span class="text-[9px] text-[#D4A017] tracking-[0.5em] uppercase opacity-70">
                MB Signature Properties •
            </span>
        </nav>

        {{-- Hero Section --}}
        <header class="mb-20 px-2">
            <div class="flex items-baseline gap-4">
                <span class="text-[#D4A017] text-sm font-serif italic">01</span>
                <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                    Factura<span class="font-light">ción</span><span class="text-[#D4A017] animate-pulse">_</span>
                </h1>
            </div>
            <p class="text-white/20 text-xs tracking-[0.3em] uppercase mt-6 ml-12">
                Gestión y validación de comprobantes fiscales digitales
            </p>
        </header>

        {{-- Dashboard de Carga --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-1 px-2 mb-20">

            {{-- Panel Izquierdo: Formulario --}}
            <div class="lg:col-span-8 bg-[#1A1A1A]/80 backdrop-blur-3xl border border-white/5 p-12 md:p-20 shadow-2xl">
                @if(!$batch || ($batch && $batch->total_files === 0))
                    <form id="xmlForm" method="POST" action="{{ route('upload-xml') }}" enctype="multipart/form-data" class="space-y-20">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
                            <div class="group relative">
                                <label class="block text-[9px] uppercase tracking-[0.3em] text-[#D4A017] mb-4 opacity-60 group-focus-within:opacity-100 transition-opacity">Correo Institucional</label>
                                <input type="email" name="user_email" required
                                       class="w-full bg-transparent border-b border-white/10 py-4 text-xl text-white font-light focus:border-[#D4A017] outline-none transition-all duration-700"
                                       placeholder="usuario@mbsignature.com">
                            </div>

                            <div class="group relative">
                                <label class="block text-[9px] uppercase tracking-[0.3em] text-[#D4A017] mb-4 opacity-60 group-focus-within:opacity-100 transition-opacity">Asignación de Proyecto</label>
                                <select name="proyect" id="proyect" required
                                        class="w-full bg-transparent border-b border-white/10 py-4 text-xl text-white font-light focus:border-[#D4A017] outline-none appearance-none cursor-pointer">
                                    <option value="" disabled selected class="bg-[#1A1A1A]">Seleccionar Unidad...</option>
                                    <option value="RESIDENT 1" class="bg-[#1A1A1A]">RESIDENT 1</option>
                                    {{-- ... --}}
                                </select>
                            </div>
                        </div>

                        <div class="relative group border border-white/5 bg-black/20 rounded-sm overflow-hidden transition-all duration-1000 hover:border-[#D4A017]/30">
                            <input type="file" name="xml_files[]" accept=".xml" multiple required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-30">
                            <div class="py-24 flex flex-col items-center justify-center relative z-10">
                                <div class="w-12 h-[1px] bg-[#D4A017] mb-8 group-hover:w-24 transition-all duration-700"></div>
                                <p class="text-[11px] tracking-[0.6em] uppercase text-white/30 group-hover:text-white/80 transition-colors">
                                    Cargar Archivos XML
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-start">
                            <button type="submit" class="group relative px-20 py-5 bg-white text-black text-[10px] tracking-[0.5em] uppercase font-bold hover:bg-[#D4A017] transition-all duration-700 shadow-[0_20px_50px_rgba(0,0,0,0.5)]">
                                Validar Documentación
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- Panel Derecho: Información --}}
            <div class="lg:col-span-4 bg-[#D4A017] p-12 md:p-16 flex flex-col justify-between shadow-2xl">
                <div>
                    <h3 class="text-black text-xs tracking-[0.4em] uppercase font-bold mb-10 border-b border-black/10 pb-4">Guía de Usuario</h3>
                    <ul class="space-y-8">
                        <li class="flex gap-4">
                            <span class="text-black/40 font-serif italic text-sm">01</span>
                            <p class="text-black/80 text-[11px] leading-relaxed tracking-wide uppercase">Verifique que el CFDI sea versión 4.0 para evitar rechazos del sistema.</p>
                        </li>
                        <li class="flex gap-4">
                            <span class="text-black/40 font-serif italic text-sm">02</span>
                            <p class="text-black/80 text-[11px] leading-relaxed tracking-wide uppercase">El sistema ERP validará el UUID contra la base de datos de MB Signature.</p>
                        </li>
                    </ul>
                </div>

                {{-- Texto decorativo actualizado --}}
                <div class="mt-20">
                    <div class="text-[32px] font-extralight text-black/20 leading-none tracking-tighter uppercase select-none">
                        MB Signature<br>Properties<br>ERP System
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Tutorial --}}
        <footer class="grid grid-cols-1 lg:grid-cols-12 gap-20 px-2 py-20 border-t border-white/5">
            <div class="lg:col-span-4">
                <h4 class="text-[#D4A017] text-[10px] tracking-[0.4em] uppercase mb-6">Mesa de Ayuda</h4>
                <p class="text-white/20 text-xs font-light leading-relaxed">
                    Si el sistema detecta inconsistencias en sus archivos, consulte el videotutorial de soporte técnico para la corrección de errores de estructura fiscal.
                </p>
            </div>
            <div class="lg:col-span-8 group relative bg-black/40 p-2 overflow-hidden border border-white/5">
                <video controls class="w-full aspect-video grayscale opacity-40 group-hover:opacity-100 group-hover:grayscale-0 transition-all duration-1000 shadow-2xl">
                    <source src="videos/tutorial_errores.mp4" type="video/mp4">
                    Tu navegador no soporta la reproducción de video.
                </video>
            </div>
        </footer>
    </div>
@endsection
