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
                MB Signature Properties • ERP System
            </span>
        </nav>

        {{-- Hero Section --}}
        <header class="mb-20 px-2">
            <div class="flex items-baseline gap-4">
                <span class="text-[#D4A017] text-sm font-serif italic">02</span>
                <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                    Notifica<span class="font-light">ciones</span><span class="text-[#D4A017] animate-pulse">_</span>
                </h1>
            </div>
            <p class="text-white/20 text-xs tracking-[0.3em] uppercase mt-6 ml-12">
                Centro de mensajes y alertas del sistema
            </p>
        </header>

        {{-- Dashboard de Notificaciones --}}
        <div class="w-full px-2 mb-20">
            <div class="bg-[#1A1A1A]/80 backdrop-blur-3xl border border-white/5 p-12 md:p-20 shadow-2xl">

                {{-- Tabs en los Extremos - CORREGIDO: Parámetro de color y padding --}}
                <div class="flex justify-between items-center mb-16 border-b border-white/5 pb-4">
                    <button class="tablink text-[10px] tracking-[0.4em] uppercase font-bold transition-all duration-500 text-[#D4A017] px-8 py-3"
                            onclick="openPage('New', this, 'rgba(255, 255, 255, 0.1)')" id="defaultOpen">Nuevas</button>

                    <button class="tablink text-[10px] tracking-[0.4em] uppercase font-bold transition-all duration-500 text-white/30 hover:text-white px-8 py-3"
                            onclick="openPage('Before', this, 'rgba(255, 255, 255, 0.1)')">Anteriores</button>
                </div>

                {{-- Contenido: Nuevas --}}
                <div id="New" class="tabcontent">
                    <ul class="space-y-6">
                        @forelse($nuevas as $n)
                            <li class="group border border-white/5 bg-black/20 p-8 transition-all duration-700 hover:border-[#D4A017]/30">
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                    <div class="flex-1">
                                        <h3 class="text-white text-lg font-light tracking-tight mb-2 uppercase">{{ $n->data['asunto'] }}</h3>
                                        <p class="text-white/70 text-xs font-light mb-4 leading-relaxed">{{ $n->data['mensaje'] }}</p>
                                        <span class="text-[9px] text-[#D4A017] tracking-widest uppercase italic font-medium">{{ $n->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                                        @csrf
                                        <button type="submit" class="bg-white text-black text-[9px] tracking-[0.3em] uppercase font-bold px-8 py-4 hover:bg-[#D4A017] transition-all duration-700">
                                            Leer
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @empty
                            {{-- Espacio en blanco con letras negras para visualización --}}
                            <li class="bg-white/90 p-32 text-center rounded-sm border border-dashed border-gray-400">
                                <p class="text-black text-[10px] tracking-[0.4em] uppercase font-bold">
                                    Bandeja de entrada vacía
                                </p>
                            </li>
                        @endforelse
                    </ul>
                </div>

                {{-- Contenido: Anteriores --}}
                <div id="Before" class="tabcontent hidden">
                    <ul class="space-y-4">
                        @forelse($antiguas as $n)
                            <li class="border border-white/5 p-6 opacity-60 hover:opacity-100 transition-all duration-500 bg-black/10">
                                <div class="flex justify-between items-center text-white">
                                    <div>
                                        <h3 class="text-white text-sm font-light uppercase">{{ $n->data['asunto'] }}</h3>
                                        <span class="text-[9px] text-white/30 uppercase tracking-widest">{{ $n->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('notifications.delete', $n->id) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500/80 text-[9px] tracking-widest uppercase hover:text-red-400 transition-colors font-bold">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @empty
                            {{-- Espacio en blanco con letras negras para historial --}}
                            <li class="bg-white/90 p-20 text-center rounded-sm border border-dashed border-gray-400">
                                <p class="text-black text-[10px] tracking-[0.4em] uppercase font-bold">
                                    Historial sin registros
                                </p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/notviw.js"></script>
@endsection
