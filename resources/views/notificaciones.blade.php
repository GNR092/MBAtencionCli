@extends('layouts.user-simple')

@section('content')
<div class="w-full flex flex-col min-h-screen">

    {{-- Header Minimalista --}}
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

    {{-- Hero Section --}}
    <header class="mb-20 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">03</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none uppercase">
                Notificaciones
            </h1>
        </div>
        <p class="text-white/50 text-s tracking-[0.3em] uppercase mt-6 ml-12">
            Centro de mensajes y alertas del sistema
        </p>
    </header>

    {{-- Tabla --}}
    <div class="w-full px-2 mb-20">

        <div class="w-full bg-white rounded-2xl shadow-2xl border border-carbon-200 overflow-hidden flex flex-col">

            {{-- HEADER: Pestañas EXPANDIDAS (Ocupan el 100% exacto) --}}
            <div class="flex w-full bg-carbon-900 border-b-4 border-dorado">

                {{-- Botón Nuevas: flex-1 para crecer y ocupar el 50% --}}
                <button
                    class="tablink flex-1 text-[11px] tracking-[0.3em] uppercase font-bold transition-all duration-300 text-dorado-400 py-5 hover:bg-white/5 focus:outline-none border-r border-white/10 text-center"
                    onclick="openPage('New', this, 'rgba(255, 255, 255, 0.1)')" id="defaultOpen">
                    Nuevas
                </button>

                {{-- Botón Anteriores: flex-1 para crecer y ocupar el otro 50% --}}
                <button
                    class="tablink flex-1 text-[11px] tracking-[0.3em] uppercase font-bold transition-all duration-300 text-gray-400 hover:text-white py-5 hover:bg-white/5 focus:outline-none text-center"
                    onclick="openPage('Before', this, 'rgba(255, 255, 255, 0.1)')">
                    Anteriores
                </button>
            </div>

            {{-- AREA DE CONTENIDO --}}
            <div class="bg-white h-[580px] relative overflow-hidden">

                {{-- CONTENIDO: NUEVAS --}}
                <div id="New" class="tabcontent w-full h-full absolute inset-0 overflow-y-auto custom-scroll block">
                    <ul class="divide-y divide-gray-100 min-h-full">
                        @forelse($nuevas as $n)
                        <li class="group relative p-6 hover:bg-amber-50/40 transition-colors duration-200">
                            <span class="absolute left-0 top-4 bottom-4 w-[3px] rounded-r-full bg-dorado-400 opacity-80"></span>
                            <div class="pl-4 flex flex-col gap-3">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-carbon-900 text-[11px] font-black uppercase tracking-widest leading-tight flex-1">
                                        {{ $n->data['asunto'] }}
                                    </h3>
                                    <span class="shrink-0 text-[9px] text-dorado-400 tracking-widest uppercase font-semibold bg-dorado/10 border border-dorado/20 px-2 py-1 rounded-full whitespace-nowrap">
                                        {{ $n->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-gray-500 text-[11px] leading-relaxed">
                                    {{ $n->data['mensaje'] }}
                                </p>
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-[9px] text-gray-300 uppercase tracking-widest">
                                        {{ $n->created_at->format('d/m/Y · H:i') }}
                                    </span>
                                    <form method="POST" action="{{ route('notificaciones.leer', $n->id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="text-[9px] tracking-[0.2em] uppercase font-bold text-dorado-400 hover:text-white hover:bg-dorado-400 border border-dorado-400/50 px-4 py-1.5 rounded-lg transition-all duration-200">
                                            Marcar leída
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                        @empty
                        {{-- Estado Vacío: Centrado perfecto usando Flex en todo el alto --}}
                        <li
                            class="w-full h-full flex flex-col items-center justify-center text-center p-8 absolute inset-0">
                            <div class="mb-6 p-6 bg-gray-50 rounded-full border border-gray-100 shadow-inner">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                            </div>
                            <h4 class="text-carbon-900 text-xs tracking-[0.25em] uppercase font-bold mb-2">
                                Bandeja Vacía
                            </h4>
                            <p class="text-gray-400 text-[10px] uppercase tracking-wider">
                                No tienes nuevas notificaciones
                            </p>
                        </li>
                        @endforelse
                    </ul>
                </div>

                {{-- CONTENIDO: ANTERIORES --}}
                <div id="Before" class="tabcontent w-full h-full absolute inset-0 overflow-y-auto custom-scroll hidden">
                    <ul class="divide-y divide-gray-100 min-h-full">
                        @forelse($antiguas as $n)
                        <li class="group relative p-5 hover:bg-gray-50 transition-colors duration-200">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-gray-300 shrink-0"></span>
                                    <div class="min-w-0">
                                        <h3 class="text-carbon-900 text-[11px] font-bold uppercase tracking-wide leading-tight">
                                            {{ $n->data['asunto'] }}
                                        </h3>
                                        <p class="text-gray-400 text-[10px] leading-relaxed mt-1 line-clamp-2">
                                            {{ $n->data['mensaje'] }}
                                        </p>
                                        <span class="text-[9px] text-gray-300 uppercase tracking-widest mt-1 block">
                                            {{ $n->created_at->format('d/m/Y · H:i') }}
                                        </span>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('notificaciones.eliminar', $n->id) }}" class="shrink-0">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="opacity-0 group-hover:opacity-100 text-[9px] tracking-widest uppercase font-bold text-red-400 hover:text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-all border border-transparent hover:border-red-100">
                                        ✕
                                    </button>
                                </form>
                            </div>
                        </li>
                        @empty
                        {{-- Estado Vacío Historial --}}
                        <li
                            class="w-full h-full flex flex-col items-center justify-center text-center p-8 absolute inset-0">
                            <div class="mb-4 opacity-30">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-400 text-[10px] tracking-[0.2em] uppercase font-bold">
                                Historial sin registros
                            </p>
                        </li>
                        @endforelse
                    </ul>
                </div>

            </div>
        </div>
    </div>


</div>

<script src="/js/notviw.js"></script>
@endsection
