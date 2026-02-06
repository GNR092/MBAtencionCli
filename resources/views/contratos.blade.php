@extends('layouts.user-simple')

@section('content')
    <div class="w-full flex flex-col min-h-screen" x-data="{ show: false, docId: null, password: '', error: '' }">

        {{-- Header Minimalista (Proporciones Exactas de Referencia) --}}
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

        {{-- Hero Section (Proporciones Exactas de Referencia) --}}
        <header class="mb-20 px-2">
            <div class="flex items-baseline gap-4">
                <span class="text-[#D4A017] text-sm font-serif italic">05</span>
                <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                    Contratos<span class="font-light text-[#D4A017]"></span><span class="text-[#D4A017] animate-pulse">_</span>
                </h1>
            </div>
            <p class="text-white/20 text-xs tracking-[0.3em] uppercase mt-6 ml-12">
                Gestión de acuerdos y documentación legal
            </p>
        </header>

        {{-- Dashboard de Contratos --}}
        <div class="w-full px-2 mb-20">
            <div class="bg-[#1A1A1A]/80 backdrop-blur-3xl border border-white/5 p-12 md:p-20 shadow-2xl">

                {{-- Buscador Estilizado --}}
                <form method="post" action="{{ route('contratos.buscar') }}" class="flex flex-col lg:flex-row items-end gap-12 mb-16 border-b border-white/5 pb-12">
                    @csrf
                    <div class="flex-1 w-full">
                        <label class="block text-[9px] uppercase tracking-[0.3em] text-[#D4A017] mb-4 opacity-60">Buscar Documento</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="FOLIO O ID..."
                               class="w-full bg-transparent border-b border-white/10 py-4 text-2xl text-white font-light focus:border-[#D4A017] outline-none transition-all duration-700 uppercase tracking-tighter">
                    </div>

                    <div class="w-full lg:w-64">
                        <label class="block text-[9px] uppercase tracking-[0.3em] text-[#D4A017] mb-4 opacity-60">Categoría</label>
                        <select name="categoria" class="w-full bg-transparent border-b border-white/10 py-4 text-xl text-white font-light focus:border-[#D4A017] outline-none appearance-none cursor-pointer">
                            <option value="id" {{ $categoria == 'id' ? 'selected' : '' }} class="bg-[#1A1A1A]">ID</option>
                            <option value="folio" {{ $categoria == 'folio' ? 'selected' : '' }} class="bg-[#1A1A1A]">FOLIO</option>
                            <option value="fecha" {{ $categoria == 'fecha' ? 'selected' : '' }} class="bg-[#1A1A1A]">FECHA</option>
                        </select>
                    </div>

                    <div class="flex gap-4 w-full lg:w-auto">
                        <button type="submit" class="bg-white text-black text-[10px] tracking-[0.3em] uppercase font-bold px-10 py-4 hover:bg-[#D4A017] transition-all duration-700 shadow-xl">
                            Buscar
                        </button>
                        <a href="{{ route('contratos.limpiar') }}" class="text-center border border-white/10 text-white/40 text-[10px] tracking-[0.3em] uppercase font-bold px-10 py-4 hover:text-white transition-all duration-700 flex items-center">
                            Limpiar
                        </a>
                    </div>
                </form>

                {{-- Tabla con Letras Más Grandes --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="border-b border-white/10">
                        <tr class="text-[11px] tracking-[0.4em] uppercase text-[#D4A017] opacity-80 font-bold">
                            <th class="px-6 py-10 text-center">ID</th>
                            <th class="px-6 py-10">Folio</th>
                            <th class="px-6 py-10">Proyecto</th>
                            <th class="px-6 py-10">Fecha</th>
                            <th class="px-6 py-10">Estado</th>
                            <th class="px-6 py-10 text-right">Acción</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-white">
                        @forelse($contratos as $contrato)
                            <tr class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-10 text-xl text-white/20 font-light text-center">{{ $contrato->id }}</td>
                                <td class="px-6 py-10 text-2xl text-white font-light tracking-tight uppercase">{{ $contrato->folio }}</td>
                                <td class="px-6 py-10 text-sm tracking-[0.2em] text-white/40 uppercase">{{ $contrato->proyecto }}</td>
                                <td class="px-6 py-10 text-xl font-serif italic text-white/60 tracking-widest">
                                    {{ \Carbon\Carbon::parse($contrato->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-10">
                                    <span class="text-[10px] px-4 py-2 border tracking-[0.3em] uppercase font-bold {{ $contrato->estado === 'activo' ? 'border-[#D4A017] text-[#D4A017]' : 'border-red-900/40 text-red-500/60' }}">
                                        {{ ucfirst($contrato->estado) }}
                                    </span>
                                </td>
                                <td class="px-6 py-10 text-right">
                                    <button
                                        class="bg-white text-black text-[9px] tracking-[0.3em] uppercase font-bold px-6 py-3 hover:bg-[#D4A017] transition-all duration-700"
                                        @click="show=true; docId={{ $contrato->id }}; password=''; error=''">
                                        Descargar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-32 text-center text-white/10 text-[11px] tracking-[0.6em] uppercase">No hay contratos asignados</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación Estilizada --}}
                <div class="mt-20 flex justify-center">
                    <div class="pagination-custom text-white">
                        {{ $contratos->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal de Seguridad (Alpine.js) --}}
        <div
            x-show="show"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-6"
            style="display: none;"
        >
            <div class="absolute inset-0 bg-black/95 backdrop-blur-xl" @click="show=false"></div>
            <div class="bg-[#1A1A1A] border border-white/10 w-full max-w-md relative z-10 shadow-3xl p-12 md:p-16">
                <h2 class="text-white text-3xl font-extralight uppercase tracking-tighter mb-8">Validación de<br><span class="text-[#D4A017] font-bold">Seguridad</span></h2>

                <div class="space-y-8">
                    <div class="group">
                        <label class="block text-[9px] uppercase tracking-[0.3em] text-[#D4A017] mb-4 opacity-60 font-bold">Contraseña de Usuario</label>
                        <input type="password" x-model="password" placeholder="••••••••"
                               class="w-full bg-transparent border-b border-white/10 py-4 text-xl text-white outline-none focus:border-[#D4A017] transition-all">
                    </div>

                    <p x-show="error" x-text="error" class="text-red-500/80 text-[10px] tracking-widest uppercase font-bold italic"></p>

                    <div class="flex flex-col gap-4 pt-4">
                        <button class="w-full bg-[#D4A017] text-black text-[10px] tracking-[0.3em] uppercase font-bold py-5 hover:bg-white transition-all duration-700" @click="checkPassword">
                            Confirmar Acceso
                        </button>
                        <button class="w-full text-white/40 text-[9px] tracking-[0.3em] uppercase font-bold py-3 hover:text-white transition-all" @click="show=false">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/checkContratos.js"></script>
@endsection
