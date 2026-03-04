@extends('layouts.admin')

@section('content')
    <div class="w-full p-4 md:p-6 animate-fadeInUp">
        <div class="max-w-full mx-auto p-4 md:p-6">
            <header class="mb-10 px-2">
                <div class="flex items-baseline gap-4">
                    <span class="text-dorado-400 text-sm font-serif italic">|</span>
                    <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none uppercase">
                        Incrementos
                    </h1>
                </div>
            </header>

        {{-- BARRA DE ACCIONES --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end mb-8 px-2">
            <a href="{{ route('incrementos.create') }}"
               class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] text-sm font-bold px-6 py-3 rounded-lg transition-all uppercase tracking-wider">
                + NUEVO INCREMENTO
            </a>
        </div>

        {{-- TABLA --}}
        <div class="tabla-dorada-container">
            <div class="overflow-x-auto custom-scroll">
                <table class="tabla-dorada">
                    <thead>
                    <tr>
                        <th>Contrato</th>
                        <th>Importe Base</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($incrementos as $inc)
                        <tr>
                            <td class="font-bold">
                                {{ $inc->contract->nombre ?? 'Contrato '.$inc->contract->id }}
                            </td>
                            <td class="text-dorado-400 font-bold font-mono text-lg">
                                ${{ number_format($inc->importe_base, 2) }}
                            </td>
                            <td class="text-xs uppercase tracking-wider">
                                {{ $inc->fecha_inicio }}
                            </td>
                            <td class="text-xs uppercase tracking-wider">
                                {{ $inc->fecha_fin ?? 'Indefinido' }}
                            </td>
                            <td>
                                <form action="{{ route('incrementos.destroy', $inc->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-[10px] tracking-widest uppercase font-bold text-red-400 hover:text-white hover:bg-red-500 border border-red-400/50 px-4 py-1.5 rounded-lg transition-all"
                                            onclick="return confirm('¿Eliminar incremento?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center text-white/30 text-xs uppercase tracking-widest font-bold">
                                No hay incrementos registrados
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
