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
                            Regimen fiscal
                        </h1>
                    </div>
                </header>

<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('regimen-fiscal.create') }}"
        class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] px-4 py-2 rounded-lg font-bold text-sm transition-all uppercase">
        + Nuevo Régimen
    </a>
</div>

<div class="bg-[#112134]/60 backdrop-blur-md rounded-xl border border-[#d8c495]/20 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-[#d8c495]/20">
                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-widest text-[#d8c495]/70">ID (Clave)</th>
                <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-widest text-[#d8c495]/70">Nombre del Régimen</th>
                <th class="text-left px-4 py-4 text-xs font-bold uppercase tracking-widest text-[#d8c495]/70">Tasa de Retención</th>
                <th class="text-center px-6 py-4 text-xs font-bold uppercase tracking-widest text-[#d8c495]/70">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($regimenes as $regimen)
            <tr class="border-b border-[#d8c495]/10 hover:bg-white/5 transition-colors">
                <td class="px-6 py-4 font-bold text-[#d8c495]/50">{{ $regimen->id_regimen }}</td>
                <td class="px-4 py-4 font-medium text-white uppercase">{{ $regimen->nombre_regimen }}</td>
                <td class="px-4 py-4 text-white/70">{{ $regimen->tasa_retencion }}%</td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('regimen-fiscal.edit', $regimen->id_regimen) }}"
                            class="text-[10px] tracking-widest uppercase font-bold text-[#d8c495] hover:text-[#112134] hover:bg-[#d8c495] border border-[#d8c495]/50 px-4 py-1.5 rounded-lg transition-all">
                            Editar
                        </a>
                        <form action="{{ route('regimen-fiscal.destroy', $regimen->id_regimen) }}" method="POST"
                            onsubmit="return confirm('¿Desea eliminar este régimen?')">
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
                <td colspan="4" class="py-16 text-center text-white/30 text-xs uppercase tracking-widest font-bold">
                    No hay regímenes registrados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
</div>
@endsection
