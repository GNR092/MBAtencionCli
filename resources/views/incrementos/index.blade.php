@extends('layouts.admin')

@section('content')
    <div class="w-full px-2 mb-20">
        <div class="flex flex-col gap-8 bg-transparent">

            {{-- ISLA 1: HEADER Y ACCIONES --}}
            <div class="bg-white rounded-2xl shadow-xl border border-[#c4c4c4] p-8 md:p-10 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h1 class="text-2xl text-[#1A1A1A] font-bold uppercase tracking-widest">
                        Incrementos de Importe
                    </h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-[0.2em] mt-1">
                        Gestión de ajustes financieros
                    </p>
                </div>

                <a href="{{ route('incrementos.create') }}" class="bg-[#1A1A1A] text-white text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg hover:bg-[#D4A017] hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5">
                    + Nuevo Incremento
                </a>
            </div>

            {{-- ISLA 2: TABLA --}}
            <div class="tabla-dorada-container bg-white rounded-2xl shadow-xl border border-[#c4c4c4] overflow-hidden">
                <div class="overflow-x-auto custom-scroll">
                    <table class="tabla-dorada">
                        <thead>
                        <tr>
                            <th class="text-left pl-8">Contrato</th>
                            <th class="text-right">Importe Base</th>
                            <th class="text-center">Inicio</th>
                            <th class="text-center">Fin</th>
                            <th class="text-center pr-8">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($incrementos as $inc)
                            <tr>
                                <td class="text-left pl-8">
                                <span class="block font-bold text-[#1A1A1A] uppercase tracking-wide">
                                    {{ $inc->contract->nombre ?? 'Contrato '.$inc->contract->id }}
                                </span>
                                </td>

                                <td class="text-right font-bold text-[#D4A017] font-mono text-lg">
                                    ${{ number_format($inc->importe_base, 2) }}
                                </td>

                                <td class="text-center text-xs font-medium text-gray-500 uppercase tracking-wide">
                                    {{ \Carbon\Carbon::parse($inc->fecha_inicio)->format('d/m/Y') }}
                                </td>

                                <td class="text-center text-xs font-medium text-gray-500 uppercase tracking-wide">
                                    {{ $inc->fecha_fin ? \Carbon\Carbon::parse($inc->fecha_fin)->format('d/m/Y') : 'INDEFINIDO' }}
                                </td>

                                <td class="text-center pr-8">
                                    <form action="{{ route('incrementos.destroy', $inc->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('¿Eliminar incremento?')"
                                                class="text-red-500 text-[10px] tracking-widest uppercase font-bold hover:text-red-700 hover:bg-red-50 px-4 py-2 rounded-lg transition-all border border-transparent hover:border-red-100">
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
    </div>
@endsection
