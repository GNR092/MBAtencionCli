@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="page-title">
                Nuevo Proyecto
            </h1>
        </div>
    </header>

    <div class="mb-4">
        <a href="{{ route('proyectos.index') }}"
            class="inline-flex items-center gap-2 text-sm text-dorado-400 hover:text-white transition-colors">
            ← Volver a proyectos
        </a>
    </div>

    <div class="w-full max-w-full mx-auto bg-[#112134]/60 backdrop-blur-md rounded-xl border border-[#d8c495]/20 overflow-hidden">
        <div class="px-6 py-4 border-b border-[#d8c495]/20">
            <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">Nuevo Proyecto</h2>
        </div>

    <form action="{{ route('proyectos.store') }}" method="POST" class="p-8 space-y-6">
        @csrf

        @if($errors->any())
        <div class="bg-red-900/40 border border-red-400/40 text-red-300 text-xs px-4 py-3 rounded-lg">
            {{ $errors->first() }}
        </div>
        @endif

        <div>
            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                Nombre del Proyecto
            </label>
            <input type="text" name="nombre_proyecto" value="{{ old('nombre_proyecto') }}" required
                class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-colors">
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                Razón Social
            </label>
            <select name="id_razon_social"
                class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-colors">
                <option value="" class="bg-[#112134]">-- Seleccionar razón social --</option>
                @foreach($razonesSociales as $rs)
                    <option value="{{ $rs->id_razon_social }}" class="bg-[#112134]">
                        {{ $rs->nombre_razon_social }} ({{ $rs->rfc }})
                    </option>
                @endforeach
            </select>
            @if($razonesSociales->isEmpty())
                <p class="text-yellow-400 text-xs mt-2">No hay razones sociales disponibles. Puede crear una primero.</p>
            @endif
        </div>

        <div class="flex gap-4 pt-2">
            <button type="submit"
                class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] text-xs font-bold tracking-[0.2em] uppercase px-8 py-3 rounded-lg transition-all">
                Guardar Proyecto
            </button>
            <a href="{{ route('proyectos.index') }}"
                class="border border-[#d8c495]/30 text-[#d8c495]/60 text-xs font-bold tracking-[0.2em] uppercase px-8 py-3 rounded-lg hover:border-[#d8c495] hover:text-[#d8c495] transition-all text-center">
                Cancelar
            </a>
        </div>
    </form>
</div>
</div>

@endsection
