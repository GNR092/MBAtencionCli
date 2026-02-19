@extends('layouts.admin')

@section('content')

@if(session('success'))
<div class="bg-green-800/80 text-white p-4 mb-6 rounded-xl text-sm">{{ session('success') }}</div>
@endif

<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-[#d8c495] uppercase tracking-wider">Altas de Proyectos</h2>
    <a href="{{ route('proyectos.create') }}"
        class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] px-4 py-2 rounded-lg font-bold text-sm transition-all uppercase">
        + Nuevo Proyecto
    </a>
</div>

<div class="bg-[#112134]/60 backdrop-blur-md rounded-xl border border-[#d8c495]/20 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-[#d8c495]/20">
                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-widest text-[#d8c495]/70">ID</th>
                <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-widest text-[#d8c495]/70">Nombre del Proyecto</th>
                <th class="text-center px-6 py-4 text-xs font-bold uppercase tracking-widest text-[#d8c495]/70">Acciones</th>
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
                <td colspan="3" class="py-16 text-center text-white/30 text-xs uppercase tracking-widest font-bold">
                    No hay proyectos registrados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
