@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-6xl mx-auto">
        <header class="mb-8">
            <div class="flex items-baseline gap-4">
                <span class="text-dorado-400 text-sm font-serif italic">|</span>
                <h1 class="text-white text-5xl md:text-7xl font-extralight tracking-[-0.02em] leading-none uppercase">
                    Logos
                </h1>
            </div>
        </header>

        <div class="bg-[#112134]/60 backdrop-blur-md rounded-2xl border border-[#d8c495]/20 shadow-2xl overflow-hidden mb-8">
            <div class="px-8 py-6 border-b border-[#d8c495]/20">
                <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">
                    Cargar Nuevo Logo
                </h2>
                <p class="text-[10px] text-[#d8c495]/50 uppercase tracking-[0.3em] mt-1">
                    Agrega un nuevo logo al carrusel
                </p>
            </div>

            <form action="{{ route('admin.logos.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Nombre de la Empresa
                        </label>
                        <input type="text" name="nombre" required
                            class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            URL de Redirección (Opcional)
                        </label>
                        <input type="url" name="url_redireccion" placeholder="https://ejemplo.com"
                            class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Archivo del Logo
                        </label>
                        <div class="border-2 border-dashed border-[#d8c495]/30 rounded-lg p-4 text-center hover:border-[#d8c495]/50 transition-colors">
                            <input type="file" name="logo" required accept=".png, .svg" 
                                class="hidden" id="logoInput"
                                onchange="document.getElementById('logoLabel').textContent = this.files[0]?.name || 'Seleccionar archivo'">
                            <label for="logoInput" class="cursor-pointer">
                                <svg class="w-8 h-8 mx-auto text-[#d8c495]/50 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span id="logoLabel" class="text-white/70 text-sm">Seleccionar archivo</span>
                                <p class="text-[10px] text-white/40 mt-1">PNG o SVG</p>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] font-bold px-8 py-3 rounded-xl transition-all uppercase tracking-wider text-sm">
                        Guardar Logo
                    </button>
                </div>
            </form>
        </div>

        <div class="tabla-dorada-container">
            <div class="overflow-x-auto custom-scroll">
                <table class="tabla-dorada">
                    <thead>
                        <tr>
                            <th>Vista Previa</th>
                            <th>Nombre Empresa</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($logos as $logo)
                        <tr>
                            <td>
                                <div class="w-16 h-12 mx-auto bg-white/10 border border-[#d8c495]/20 rounded-lg p-1 flex items-center justify-center">
                                    <img src="{{ asset('storage/' . $logo->imagen_ruta) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                                </div>
                            </td>
                            <td class="font-bold text-[#d8c495]">{{ $logo->nombre }}</td>
                            <td class="max-w-[150px]">
                                <a href="{{ $logo->url_redireccion }}" target="_blank"
                                    class="text-[#d8c495] hover:text-white transition text-xs font-medium truncate block">
                                    {{ $logo->url_redireccion ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                <form action="{{ route('admin.logos.toggle', $logo->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition border
                                        {{ $logo->activo ? 'bg-green-500/20 text-green-400 border-green-500/30 hover:bg-green-500/30' : 'bg-red-500/20 text-red-400 border-red-500/30 hover:bg-red-500/30' }}">
                                        {{ $logo->activo ? '● ACTIVO' : '○ INACTIVO' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('admin.logos.destroy', $logo->id) }}" method="POST"
                                    onsubmit="return confirm('¿Eliminar este logo permanentemente?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center bg-red-500/10 hover:bg-red-500/20 text-red-400 w-10 h-10 rounded-xl transition border border-red-500/20 hover:border-red-500/40">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-10 text-white/30 font-medium italic text-center">
                                No hay logos cargados en el carrusel actualmente.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
