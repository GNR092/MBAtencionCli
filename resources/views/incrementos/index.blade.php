@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl mx-auto p-6">

        {{-- HEADER GIGANTE (Igual a Facturas) --}}
        <header class="mb-10 px-2">
            <div class="flex items-baseline gap-4">
                <span class="text-dorado-400 text-sm font-serif italic">|</span>
                <h1 class="text-white text-6xl md:text-8xl font-extralight tracking-[-0.02em] leading-none">
                    Incrementos<span class="font-light text-dorado"></span><span class="text-dorado-400 animate-pulse">_</span>
                </h1>
            </div>
        </header>

        {{-- BARRA DE ACCIONES --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end mb-8 px-2">
            <a href="{{ route('incrementos.create') }}"
               class="bg-[#d8c495] hover:bg-[#c9a143] text-black text-sm font-bold px-6 py-3 rounded shadow-md transition-all hover:scale-105">
                + NUEVO INCREMENTO
            </a>
        </div>

        {{-- TABLA DORADA --}}
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
                    @foreach($incrementos as $inc)
                        <tr>
                            <td class="font-bold text-carbon-900">
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
                                    <button class="bg-red-50 text-red-600 border border-red-200 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest hover:bg-red-100 transition"
                                            onclick="return confirm('¿Eliminar incremento?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
