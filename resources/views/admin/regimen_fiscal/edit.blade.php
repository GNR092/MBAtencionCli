@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none uppercase">
                Editar Régimen Fiscal
            </h1>
        </div>
    </header>

    <div class="mb-4">
        <a href="{{ route('regimen-fiscal.index') }}"
            class="inline-flex items-center gap-2 text-sm text-[#d8c495]/70 hover:text-white transition-colors">
            ← Volver a regímenes
        </a>
    </div>

    <div class="w-full max-w-full mx-auto bg-[#112134]/60 backdrop-blur-md rounded-xl border border-[#d8c495]/20 overflow-hidden">
        <div class="px-6 py-4 border-b border-[#d8c495]/20">
            <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">Editar Régimen Fiscal</h2>
        </div>

    <form action="{{ route('regimen-fiscal.update', $regimen->id_regimen) }}" method="POST" class="p-8 space-y-6">
        @csrf
        @method('PUT')

        @if($errors->any())
        <div class="bg-red-900/40 border border-red-400/40 text-red-300 text-xs px-4 py-3 rounded-lg">
            {{ $errors->first() }}
        </div>
        @endif

        <div>
            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                ID del Régimen (Clave)
            </label>
            <input type="number" value="{{ $regimen->id_regimen }}" disabled
                class="w-full bg-white/5 border border-[#d8c495]/10 rounded-lg px-4 py-3 text-white/30 cursor-not-allowed">
            <p class="text-[10px] text-white/30 mt-1 italic">* La clave del régimen no puede ser modificada.</p>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                Nombre del Régimen
            </label>
            <input type="text" name="nombre_regimen"
                value="{{ old('nombre_regimen', $regimen->nombre_regimen) }}" required
                class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-colors">
            @error('nombre_regimen')
            <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                Tasa de Retención (%)
            </label>
            <input type="number" step="0.01" name="tasa_retencion"
                value="{{ old('tasa_retencion', $regimen->tasa_retencion) }}" required
                class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-colors">
            @error('tasa_retencion')
            <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-4 pt-2">
            <button type="submit"
                class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] text-xs font-bold tracking-[0.2em] uppercase px-8 py-3 rounded-lg transition-all">
                Actualizar Cambios
            </button>
            <a href="{{ route('regimen-fiscal.index') }}"
                class="border border-[#d8c495]/30 text-[#d8c495]/60 text-xs font-bold tracking-[0.2em] uppercase px-8 py-3 rounded-lg hover:border-[#d8c495] hover:text-[#d8c495] transition-all text-center">
                Cancelar
            </a>
        </div>
    </form>
</div>
</div>

@endsection
