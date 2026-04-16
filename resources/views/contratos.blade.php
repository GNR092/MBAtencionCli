@extends('layouts.user-simple')

@section('content')
<div class="relative w-full flex flex-col min-h-screen overflow-hidden" x-data="{ show: false, docId: null, password: '', error: '' }">
    <div class="pointer-events-none absolute -top-28 -right-20 h-72 w-72 rounded-full bg-dorado-400/10 blur-3xl"></div>
    <div class="pointer-events-none absolute top-56 -left-24 h-80 w-80 rounded-full bg-[#0d1f30]/40 blur-3xl"></div>

    <nav class="relative z-10 flex flex-wrap justify-between items-center gap-4 mb-12 md:mb-16 px-2">
        <a href="{{ route('user.dashboard') }}"
            class="group flex items-center gap-4 text-[10px] tracking-[0.38em] uppercase text-white/40 hover:text-dorado-400 transition-all duration-500">
            <span class="text-lg group-hover:-translate-x-2 transition-transform duration-500">←</span>
            <span class="text-[#d8c495]">Volver al panel</span>
        </a>
        <div class="hidden md:block h-px flex-1 mx-6 bg-linear-to-r from-[#8B6B23]/50 to-transparent"></div>
        <span class="text-[9px] text-dorado-400 tracking-[0.45em] uppercase opacity-70">
            Zona de inversionistas
        </span>
    </nav>

    <header class="relative z-10 mb-10 md:mb-14 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm md:text-base font-serif italic">|</span>
            <h1 class="page-title">
                Contratos
            </h1>
        </div>
        <p class="text-white/55 text-[11px] md:text-xs tracking-[0.28em] uppercase mt-4 ml-6 md:ml-12">
            Seguimiento y descarga segura de documentos de inversion
        </p>
    </header>

    @php
        $totalContratos = method_exists($contratos, 'total') ? $contratos->total() : $contratos->count();
        $activosPagina = $contratos->where('estado', 'activo')->count();
    @endphp

    <div class="relative z-10 w-full px-2 pb-16 md:pb-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="rounded-2xl border border-dorado-400/20 bg-white/5 backdrop-blur-sm p-5">
                <p class="text-white/50 text-[10px] tracking-[0.2em] uppercase mb-2">Total de contratos</p>
                <p class="text-3xl font-light text-white">{{ $totalContratos }}</p>
            </div>
            <div class="rounded-2xl border border-dorado-400/20 bg-white/5 backdrop-blur-sm p-5">
                <p class="text-white/50 text-[10px] tracking-[0.2em] uppercase mb-2">Activos en pantalla</p>
                <p class="text-3xl font-light text-dorado-400">{{ $activosPagina }}</p>
            </div>
            <div class="rounded-2xl border border-dorado-400/20 bg-white/5 backdrop-blur-sm p-5">
                <p class="text-white/50 text-[10px] tracking-[0.2em] uppercase mb-2">Acceso protegido</p>
                <p class="text-sm text-white/80 leading-relaxed">Cada descarga solicita confirmacion de contrasena para proteger tu informacion.</p>
            </div>
        </div>

        <div class="flex flex-col gap-6 bg-transparent">
            <div class="rounded-3xl border border-dorado-400/20 bg-[#0d1f30]/75 backdrop-blur-md p-6 md:p-8 lg:p-10 shadow-xl shadow-black/20">
                <form method="post" action="{{ route('contratos.buscar') }}" class="flex flex-col lg:flex-row items-end gap-5 md:gap-6">
                    @csrf

                    <div class="flex-1 w-full">
                        <label class="block text-[11px] font-bold uppercase tracking-[0.2em] text-dorado-400 mb-3">
                            Buscar documento
                        </label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Folio o ID..."
                            class="w-full bg-black/25 border border-dorado-400/20 rounded-xl py-3.5 px-4 text-lg md:text-xl text-white font-light focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-all uppercase tracking-tight placeholder:text-white/35">
                    </div>

                    <div class="w-full lg:w-60">
                        <label class="block text-[11px] font-bold uppercase tracking-[0.2em] text-dorado-400 mb-3">
                            Categoria
                        </label>
                        <div class="relative">
                            <select name="categoria"
                                class="w-full bg-black/25 border border-dorado-400/20 rounded-xl py-3.5 pl-4 pr-10 text-base text-white font-light focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 appearance-none cursor-pointer transition-all">
                                <option value="id" {{ $categoria == 'id' ? 'selected' : '' }}>ID</option>
                                <option value="folio" {{ $categoria == 'folio' ? 'selected' : '' }}>Folio</option>
                                <option value="fecha" {{ $categoria == 'fecha' ? 'selected' : '' }}>Fecha</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-dorado-400/70">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 w-full lg:w-auto">
                        <button type="submit"
                            class="bg-carbon-900 text-white text-xs md:text-sm tracking-[0.2em] uppercase font-bold px-6 md:px-8 py-3.5 rounded-xl hover:bg-dorado-400 hover:text-carbon-900 hover:shadow-lg transition-all duration-300">
                            Buscar
                        </button>

                        <a href="{{ route('contratos.limpiar') }}"
                            class="flex items-center justify-center border border-dorado-400/25 text-white/70 text-xs md:text-sm tracking-[0.2em] uppercase font-bold px-6 md:px-8 py-3.5 rounded-xl hover:border-dorado-400 hover:text-dorado-400 transition-all duration-300">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div class="tabla-dorada-container rounded-3xl overflow-hidden">
                <div class="overflow-x-auto custom-scroll">
                    <table class="tabla-dorada min-w-[860px]">
                        <thead>
                            <tr>
                                <th class="text-left pl-6">ID</th>
                                <th class="text-left">Folio</th>
                                <th class="text-left">Proyecto</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center pr-6">Accion</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($contratos as $contrato)
                                <tr>
                                    <td class="text-left pl-6 font-bold text-white/40">#{{ $contrato->id }}</td>

                                    <td class="font-bold text-white uppercase">{{ $contrato->folio }}</td>

                                    <td class="text-xs font-semibold text-white/65 uppercase tracking-wide">{{ $contrato->proyecto ?? 'Sin proyecto' }}</td>

                                    <td class="text-center font-medium text-white/90">{{ \Carbon\Carbon::parse($contrato->fecha)->format('d/m/Y') }}</td>

                                    <td class="text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $contrato->estado === 'activo' ? 'bg-dorado/10 text-dorado-400 border-dorado/20' : 'bg-red-100 text-red-700 border-red-200' }}">
                                            {{ ucfirst($contrato->estado) }}
                                        </span>
                                    </td>

                                    <td class="text-center pr-6">
                                        <button
                                            class="bg-carbon-900 text-white px-5 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-dorado-400 hover:text-carbon-900 transition-all shadow-sm hover:shadow-md"
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

            <div class="rounded-2xl border border-dorado-400/20 bg-[#0d1f30]/65 backdrop-blur-sm p-4 md:p-5">
                <div class="pagination-custom text-white/80">{{ $contratos->links('pagination::tailwind') }}</div>
            </div>
        </div>
    </div>

    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>

        <div class="relative z-10 w-full max-w-sm bg-[#111] border border-dorado-400/20 rounded-2xl shadow-2xl overflow-hidden">
            <div class="h-1 w-full bg-linear-to-r from-dorado-400/0 via-dorado-400 to-dorado-400/0"></div>

            <div class="px-8 py-10">
                <div class="flex flex-col items-center mb-8 text-center">
                    <div class="w-12 h-12 rounded-full border border-dorado-400/30 flex items-center justify-center mb-4 bg-dorado-400/5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-dorado-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                        </svg>
                    </div>
                    <h2 class="text-white text-xl font-semibold tracking-wide">Confirmar identidad</h2>
                    <p class="text-white/40 text-xs mt-1 tracking-wider">Ingresa tu contrasena para continuar</p>
                </div>

                <div class="mb-2">
                    <div class="relative">
                        <input type="password"
                               x-model="password"
                               @keydown.enter="checkPassword"
                               x-ref="passwordInput"
                               x-init="$watch('show', v => v && $nextTick(() => $refs.passwordInput.focus()))"
                               placeholder="Contrasena"
                               class="w-full bg-white/5 border border-white/10 focus:border-dorado-400/60 rounded-lg px-4 py-3 text-white placeholder-white/20 outline-none transition-all text-sm">
                    </div>
                </div>

                <div class="h-6 mb-4">
                    <p x-show="error" x-text="error" x-transition class="text-red-400 text-[11px] tracking-wide text-center"></p>
                </div>

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
