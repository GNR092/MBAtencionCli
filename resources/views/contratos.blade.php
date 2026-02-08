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
            <div class="flex flex-col gap-8 bg-transparent">

                {{-- ISLA 1: BUSCADOR (Tarjeta Blanca) --}}
                <div class="bg-white rounded-2xl shadow-xl border border-[#c4c4c4] p-8 md:p-10">
                    <form method="post" action="{{ route('contratos.buscar') }}" class="flex flex-col lg:flex-row items-end gap-6">
                        @csrf

                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#1A1A1A] mb-3">
                                Buscar Documento
                            </label>
                            <div class="relative w-full">
                                <input type="text" name="search" value="{{ $search }}" placeholder="FOLIO O ID..."
                                       class="w-full bg-gray-50 border border-gray-300 rounded-lg py-4 pl-4 pr-4 text-xl text-[#1A1A1A] font-light focus:outline-none focus:border-[#D4A017] focus:ring-1 focus:ring-[#D4A017] transition-all uppercase tracking-tight placeholder-gray-300">
                            </div>
                        </div>

                        <div class="w-full lg:w-64">
                            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#1A1A1A] mb-3">
                                Categoría
                            </label>
                            <div class="relative">
                                <select name="categoria" class="w-full bg-gray-50 border border-gray-300 rounded-lg py-4 pl-4 pr-10 text-lg text-[#1A1A1A] font-light focus:outline-none focus:border-[#D4A017] focus:ring-1 focus:ring-[#D4A017] appearance-none cursor-pointer transition-all">
                                    <option value="id" {{ $categoria == 'id' ? 'selected' : '' }}>ID</option>
                                    <option value="folio" {{ $categoria == 'folio' ? 'selected' : '' }}>FOLIO</option>
                                    <option value="fecha" {{ $categoria == 'fecha' ? 'selected' : '' }}>FECHA</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4 w-full lg:w-auto">
                            <button type="submit" class="bg-[#1A1A1A] text-white text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg hover:bg-[#D4A017] hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5">
                                Buscar
                            </button>

                            <a href="{{ route('contratos.limpiar') }}" class="flex items-center justify-center border border-gray-300 text-gray-500 text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg hover:border-[#1A1A1A] hover:text-[#1A1A1A] transition-all duration-300">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>

                {{-- ISLA 2: TABLA (Tarjeta Blanca) --}}
                <div class="tabla-dorada-container bg-white rounded-2xl shadow-xl border border-[#c4c4c4] overflow-hidden">
                    <div class="overflow-x-auto custom-scroll">
                        <table class="tabla-dorada">
                            <thead>
                            <tr>
                                <th class="text-left pl-6">ID</th>
                                <th class="text-left">Folio</th>
                                <th class="text-left">Proyecto</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center pr-6">Acción</th>
                            </tr>
                            </thead>
                            <tbody id="tableBody">
                            @forelse($contratos as $contrato)
                                <tr>
                                    <td class="text-left pl-6 font-bold text-gray-400">
                                        #{{ $contrato->id }}
                                    </td>

                                    <td class="font-bold text-gris-carbon uppercase">
                                        {{ $contrato->folio }}
                                    </td>

                                    <td class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        {{ $contrato->proyecto }}
                                    </td>

                                    <td class="text-center font-medium text-gris-carbon">
                                        {{ \Carbon\Carbon::parse($contrato->fecha)->format('d/m/Y') }}
                                    </td>

                                    <td class="text-center">
                                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border
                                    {{ $contrato->estado === 'activo'
                                        ? 'bg-dorado/10 text-dorado border-dorado/20'
                                        : 'bg-red-100 text-red-700 border-red-200' }}">
                                    {{ ucfirst($contrato->estado) }}
                                </span>
                                    </td>

                                    <td class="text-center pr-6">
                                        <button
                                            class="bg-[#1A1A1A] text-white px-5 py-2 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-[#D4A017] transition-all shadow-sm hover:shadow-md transform hover:-translate-y-0.5"
                                            @click="show=true; docId={{ $contrato->id }}; password=''; error=''">
                                            Descargar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-16 text-center text-gray-400 text-xs uppercase tracking-widest font-bold">
                                        No hay contratos asignados
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ISLA 3: PAGINACIÓN (Tarjeta Blanca) --}}
                <div >
                    <div class="pagination-custom text-gray-600">
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
