@extends('layouts.admin')

@section('content')
    <div class="w-full px-2 mb-20 flex justify-center">

        <div class="w-full max-w-3xl bg-[#112134]/60 backdrop-blur-md rounded-xl border border-[#d8c495]/20 overflow-hidden">

            {{-- Encabezado del Formulario --}}
            <div class="px-8 py-6 border-b border-[#d8c495]/20">
                <h1 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">
                    Registrar Incremento
                </h1>
                <p class="text-[10px] text-[#d8c495]/50 uppercase tracking-[0.3em] mt-1">
                    Detalles del nuevo importe
                </p>
            </div>

            {{-- Cuerpo del Formulario --}}
            <div class="p-8 md:p-12">
                <form action="{{ route('incrementos.store') }}" method="POST" class="flex flex-col gap-8">
                    @csrf

                    @if($errors->any())
                    <div class="bg-red-900/40 border border-red-400/40 text-red-300 text-xs px-4 py-3 rounded-lg">
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Contrato
                        </label>
                        <div class="relative">
                            <select name="id_contract" class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg py-4 pl-4 pr-10 text-lg text-white focus:outline-none focus:border-[#d8c495] appearance-none cursor-pointer transition-all">
                                <option value="">SELECCIONA UN CONTRATO</option>
                                @foreach($contract as $contract)
                                    <option value="{{ $contract->id }}">{{ $contract->nombre ?? 'CONTRATO '.$contract->id }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-[#d8c495]/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Importe Base ($)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-[#d8c495]/50 font-bold">$</span>
                            <input type="number" name="importe_base" step="0.01" placeholder="0.00" required
                                   class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg py-4 pl-10 pr-4 text-xl text-white focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-all placeholder-white/20">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                                Fecha de Inicio
                            </label>
                            <input type="date" name="fecha_inicio" required
                                   class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg py-4 px-4 text-lg text-white focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                                Fecha de Fin
                            </label>
                            <input type="date" name="fecha_fin"
                                   class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg py-4 px-4 text-lg text-white focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-all">
                        </div>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button type="submit"
                            class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg transition-all">
                            Guardar Incremento
                        </button>
                        <a href="{{ route('incrementos.index') }}"
                            class="border border-[#d8c495]/30 text-[#d8c495]/60 text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg hover:border-[#d8c495] hover:text-[#d8c495] transition-all text-center">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
