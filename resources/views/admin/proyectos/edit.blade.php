@extends('layouts.admin')

@section('content')

<div class="mb-4">
    <a href="{{ route('proyectos.index') }}"
        class="inline-flex items-center gap-2 text-sm text-dorado-400 hover:text-white transition-colors">
        ← Volver a proyectos
    </a>
</div>

<div class="w-full max-w-2xl bg-[#112134]/60 backdrop-blur-md rounded-xl border border-[#d8c495]/20 overflow-hidden">
    <div class="px-6 py-4 border-b border-[#d8c495]/20">
        <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">Editar Proyecto</h2>
    </div>

    <form action="{{ route('proyectos.update', $proyecto->id_proyecto) }}" method="POST" class="p-8 space-y-6">
        @csrf
        @method('PUT')

        @if($errors->any())
        <div class="bg-red-900/40 border border-red-400/40 text-red-300 text-xs px-4 py-3 rounded-lg">
            {{ $errors->first() }}
        </div>
        @endif

        <div>
            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                Nombre del Proyecto
            </label>
            <input type="text" name="nombre_proyecto"
                value="{{ old('nombre_proyecto', $proyecto->nombre_proyecto) }}" required
                class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-colors">
        </div>

        <div class="flex gap-4 pt-2">
            <button type="submit"
                class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] text-xs font-bold tracking-[0.2em] uppercase px-8 py-3 rounded-lg transition-all">
                Actualizar
            </button>
            <a href="{{ route('proyectos.index') }}"
                class="border border-[#d8c495]/30 text-[#d8c495]/60 text-xs font-bold tracking-[0.2em] uppercase px-8 py-3 rounded-lg hover:border-[#d8c495] hover:text-[#d8c495] transition-all text-center">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection
