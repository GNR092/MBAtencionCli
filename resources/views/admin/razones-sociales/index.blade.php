@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-full mx-auto p-4 md:p-6">

        @if(session('success'))
        <div class="bg-green-800/80 text-white p-4 mb-6 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <header class="mb-10 px-2">
            <div class="flex items-baseline gap-4">
                <span class="text-dorado-400 text-sm font-serif italic">|</span>
                <h1 class="page-title">
                    Razones Sociales
                </h1>
            </div>
        </header>

        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('razones-sociales.create') }}"
                class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] px-4 py-2 rounded-lg font-bold text-sm transition-all uppercase">
                + Nueva Razón Social
            </a>
        </div>

        <div class="tabla-dorada-container">
            <div class="overflow-x-auto custom-scroll">
                <table class="tabla-dorada">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>RFC</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Proyecto Asignado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($razonesSociales as $rs)
                        <tr class="border-b border-[#d8c495]/10 hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 font-bold text-[#d8c495]/50">#{{ $rs->id_razon_social }}</td>
                            <td class="px-4 py-4 text-white">{{ $rs->nombre_razon_social }}</td>
                            <td class="px-4 py-4 text-white/70 font-mono">{{ $rs->rfc }}</td>
                            <td class="px-4 py-4 text-white/70">{{ $rs->telefono ?? '-' }}</td>
                            <td class="px-4 py-4 text-white/70">{{ $rs->email ?? '-' }}</td>
                            <td class="px-4 py-4 text-white/70">
                                @if($rs->proyectos->count() > 0)
                                    @foreach($rs->proyectos as $proyecto)
                                        <span class="block">{{ $proyecto->nombre_proyecto }}</span>
                                    @endforeach
                                @else
                                    <span class="text-white/40 italic">Sin proyectos</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('razones-sociales.edit', $rs->id_razon_social) }}"
                                        class="text-[10px] tracking-widest uppercase font-bold text-[#d8c495] hover:text-[#112134] hover:bg-[#d8c495] border border-[#d8c495]/50 px-4 py-1.5 rounded-lg transition-all">
                                        Editar
                                    </a>
                                    <form action="{{ route('razones-sociales.destroy', $rs->id_razon_social) }}" method="POST"
                                        onsubmit="return confirm('¿Eliminar esta razón social?')">
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
                            <td colspan="7" class="py-10 text-white/40 italic text-center">
                                No hay razones sociales registradas
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
