@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="page-title">
                Nueva Razón Social
            </h1>
        </div>
    </header>

    <div class="mb-4">
        <a href="{{ route('razones-sociales.index') }}"
            class="inline-flex items-center gap-2 text-sm text-dorado-400 hover:text-white transition-colors">
            ← Volver a razones sociales
        </a>
    </div>

    <div class="w-full max-w-full mx-auto bg-[#112134]/60 backdrop-blur-md rounded-xl border border-[#d8c495]/20 overflow-hidden">
        <div class="px-6 py-4 border-b border-[#d8c495]/20">
            <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">Datos de la Razón Social</h2>
        </div>

    <form action="{{ route('razones-sociales.store') }}" method="POST" class="p-8 space-y-6">
        @csrf

        @if($errors->any())
        <div class="bg-red-900/40 border border-red-400/40 text-red-300 text-xs px-4 py-3 rounded-lg">
            {{ $errors->first() }}
        </div>
        @endif

        <div>
            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                Nombre de la Razón Social *
            </label>
            <input type="text" name="nombre_razon_social" value="{{ old('nombre_razon_social') }}" required
                class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-colors">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                    RFC *
                </label>
                <input type="text" name="rfc" value="{{ old('rfc') }}" required maxlength="13"
                    class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-colors uppercase">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                    Teléfono
                </label>
                <input type="text" name="telefono" value="{{ old('telefono') }}" maxlength="20"
                    class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-colors">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                    Email
                </label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-colors">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">
                    Dirección
                </label>
                <input type="text" name="direccion" value="{{ old('direccion') }}"
                    class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-colors">
            </div>
        </div>

        <div class="flex gap-4 pt-2">
            <button type="submit"
                class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] text-xs font-bold tracking-[0.2em] uppercase px-8 py-3 rounded-lg transition-all">
                Guardar Razón Social
            </button>
            <a href="{{ route('razones-sociales.index') }}"
                class="border border-[#d8c495]/30 text-[#d8c495]/60 text-xs font-bold tracking-[0.2em] uppercase px-8 py-3 rounded-lg hover:border-[#d8c495] hover:text-[#d8c495] transition-all text-center">
                Cancelar
            </a>
        </div>
    </form>
</div>
</div>

@endsection
