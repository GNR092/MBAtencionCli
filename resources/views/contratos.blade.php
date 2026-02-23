@extends('layouts.user-simple')

@section('content')
<div class="w-full flex flex-col min-h-screen" x-data="{ show: false, docId: null, password: '', error: '' }">

    {{-- Header Minimalista (Proporciones Exactas de Referencia) --}}
    <nav class="flex justify-between items-center mb-16 px-2">
        <a href="{{ route('user.dashboard') }}"
            class="group flex items-center gap-4 text-[10px] tracking-[0.4em] uppercase text-white/40 hover:text-dorado-400 transition-all duration-700">
            <span class="text-lg group-hover:-translate-x-2 transition-transform duration-500">←</span>
            <span class="text-[#d8c495]">Volver al Panel</span>
        </a>
        <div class="h-px flex-1 mx-10 bg-linear-to-r from-[#8B6B23]/40 to-transparent"></div>
        <span class="text-[9px] text-dorado-400 tracking-[0.5em] uppercase opacity-70">
            MB Signature Properties •
        </span>
    </nav>

    {{-- Hero Section (Proporciones Exactas de Referencia) --}}
    <header class="mb-20 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">05</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                Contratos<span class="font-light text-dorado"></span><span class="text-dorado-400 animate-pulse">_</span>
            </h1>
        </div>
        <p class="text-white/50 text-s tracking-[0.3em] uppercase mt-6 ml-12">
            Gestión de acuerdos y documentación legal
        </p>
    </header>

    {{-- Dashboard de Contratos --}}
    <div class="w-full px-2 mb-20">
        <div class="flex flex-col gap-8 bg-transparent">

            {{-- ISLA 1: BUSCADOR (Tarjeta Blanca) --}}
            <div class="bg-white rounded-2xl shadow-xl border border-carbon-200 p-8 md:p-10">
                <form method="post" action="{{ route('contratos.buscar') }}"
                    class="flex flex-col lg:flex-row items-end gap-6">
                    @csrf

                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-carbon-900 mb-3">
                            Buscar Documento
                        </label>
                        <div class="relative w-full">
                            <input type="text" name="search" value="{{ $search }}" placeholder="FOLIO O ID..."
                                class="w-full bg-gray-50 border border-gray-300 rounded-lg py-4 pl-4 pr-4 text-xl text-carbon-900 font-light focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-all uppercase tracking-tight placeholder-gray-300">
                        </div>
                    </div>

                    <div class="w-full lg:w-64">
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-carbon-900 mb-3">
                            Categoría
                        </label>
                        <div class="relative">
                            <select name="categoria"
                                class="w-full bg-gray-50 border border-gray-300 rounded-lg py-4 pl-4 pr-10 text-lg text-carbon-900 font-light focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 appearance-none cursor-pointer transition-all">
                                <option value="id" {{ $categoria == 'id' ? 'selected' : '' }}>ID</option>
                                <option value="folio" {{ $categoria == 'folio' ? 'selected' : '' }}>FOLIO</option>
                                <option value="fecha" {{ $categoria == 'fecha' ? 'selected' : '' }}>FECHA</option>
                            </select>
                            <div
                                class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 w-full lg:w-auto">
                        <button type="submit"
                            class="bg-carbon-900 text-white text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg hover:bg-dorado-400 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5">
                            Buscar
                        </button>

                        <a href="{{ route('contratos.limpiar') }}"
                            class="flex items-center justify-center border border-gray-300 text-gray-500 text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg hover:border-carbon-900 hover:text-carbon-900 transition-all duration-300">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            {{-- ISLA 2: TABLA (Tarjeta Blanca) --}}
            <div class="tabla-dorada-container bg-white rounded-2xl shadow-xl border border-carbon-200 overflow-hidden">
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

                                <td class="font-bold text-carbon-900 uppercase">
                                    {{ $contrato->folio }}
                                </td>

                                <td class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                    {{ $contrato->proyecto ?? '—' }}
                                </td>

                                <td class="text-center font-medium text-carbon-900">
                                    {{ \Carbon\Carbon::parse($contrato->fecha)->format('d/m/Y') }}
                                </td>

                                <td class="text-center">
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border
                                    {{ $contrato->estado === 'activo'
                                        ? 'bg-dorado/10 text-dorado-400 border-dorado/20'
                                        : 'bg-red-100 text-red-700 border-red-200' }}">
                                        {{ ucfirst($contrato->estado) }}
                                    </span>
                                </td>

                                <td class="text-center pr-6">
                                    <button
                                        class="bg-carbon-900 text-white px-5 py-2 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-dorado-400 transition-all shadow-sm hover:shadow-md transform hover:-translate-y-0.5"
                                        @click="show=true; docId={{ $contrato->id }}; password=''; error=''">
                                        Descargar
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6"
                                    class="py-16 text-center text-gray-400 text-xs uppercase tracking-widest font-bold">
                                    No hay contratos asignados
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ISLA 3: PAGINACIÓN (Tarjeta Blanca) --}}
            <div>
                <div class="pagination-custom text-gray-600">
                    {{ $contratos->links('pagination::tailwind') }}
                </div>
            </div>

        </div>
    </div>

    {{-- Modal de Seguridad --}}
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="show=false"></div>

        <div class="relative z-10 w-full max-w-sm bg-[#111] border border-dorado-400/20 rounded-2xl shadow-2xl overflow-hidden">

            {{-- Franja superior dorada --}}
            <div class="h-1 w-full bg-linear-to-r from-dorado-400/0 via-dorado-400 to-dorado-400/0"></div>

            <div class="px-8 py-10">
                {{-- Icono + título --}}
                <div class="flex flex-col items-center mb-8 text-center">
                    <div class="w-12 h-12 rounded-full border border-dorado-400/30 flex items-center justify-center mb-4 bg-dorado-400/5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-dorado-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                        </svg>
                    </div>
                    <h2 class="text-white text-xl font-semibold tracking-wide">Confirmar identidad</h2>
                    <p class="text-white/40 text-xs mt-1 tracking-wider">Ingresa tu contraseña para continuar</p>
                </div>

                {{-- Input contraseña --}}
                <div class="mb-2">
                    <div class="relative">
                        <input type="password"
                               x-model="password"
                               @keydown.enter="checkPassword"
                               x-ref="passwordInput"
                               x-init="$watch('show', v => v && $nextTick(() => $refs.passwordInput.focus()))"
                               placeholder="Contraseña"
                               class="w-full bg-white/5 border border-white/10 focus:border-dorado-400/60 rounded-lg px-4 py-3 text-white placeholder-white/20 outline-none transition-all text-sm">
                    </div>
                </div>

                {{-- Error --}}
                <div class="h-6 mb-4">
                    <p x-show="error" x-text="error" x-transition
                       class="text-red-400 text-[11px] tracking-wide text-center"></p>
                </div>

                {{-- Botones --}}
                <div class="flex flex-col gap-3">
                    <button @click="checkPassword"
                            class="w-full bg-dorado-400 hover:bg-dorado-300 text-black text-xs font-bold tracking-[0.2em] uppercase py-3 rounded-lg transition-all duration-200 shadow-lg shadow-dorado-400/20">
                        Descargar documento
                    </button>
                    <button @click="show=false; password=''; error=''"
                            class="w-full text-white/30 hover:text-white/70 text-xs tracking-widest uppercase py-2 transition-all">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/checkContratos.js') }}"></script>
@endsection
