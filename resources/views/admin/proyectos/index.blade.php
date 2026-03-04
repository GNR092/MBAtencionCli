@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-full mx-auto p-4 md:p-6">

        @if(session('success'))
        <div class="bg-green-800/80 text-white p-4 mb-6 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        {{-- HEADER GIGANTE --}}
        <header class="mb-10 px-2">
            <div class="flex items-baseline gap-4">
                <span class="text-dorado-400 text-sm font-serif italic">|</span>
                <h1 class="text-white text-6xl md:text-8xl font-extralight tracking-[-0.02em] leading-none uppercase">
                    Altas de proyectos
                </h1>
            </div>
        </header>

<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('proyectos.create') }}"
        class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] px-4 py-2 rounded-lg font-bold text-sm transition-all uppercase">
        + Nuevo Proyecto
    </a>
</div>

<div class="tabla-dorada-container">
    <div class="overflow-x-auto custom-scroll">
        <table class="tabla-dorada">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Proyecto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        <tbody>
            @forelse($proyectos as $proyecto)
            <tr class="border-b border-[#d8c495]/10 hover:bg-white/5 transition-colors">
                <td class="px-6 py-4 font-bold text-[#d8c495]/50">#{{ $proyecto->id_proyecto }}</td>
                <td class="px-4 py-4 font-medium text-white uppercase">{{ $proyecto->nombre_proyecto }}</td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('proyectos.edit', $proyecto->id_proyecto) }}"
                            class="text-[10px] tracking-widest uppercase font-bold text-[#d8c495] hover:text-[#112134] hover:bg-[#d8c495] border border-[#d8c495]/50 px-4 py-1.5 rounded-lg transition-all">
                            Editar
                        </a>
                        <form action="{{ route('proyectos.destroy', $proyecto->id_proyecto) }}" method="POST"
                            onsubmit="return confirm('¿Eliminar este proyecto?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="text-[10px] tracking-widest uppercase font-bold text-red-400 hover:text-white hover:bg-red-500 border border-red-400/50 px-4 py-1.5 rounded-lg transition-all">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="py-10 text-white/40 italic">
                    No hay proyectos registrados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
</div>
@endsection
