@extends('layouts.admin')

@section('content')
    <div class="w-full px-2 mb-20 flex justify-center">

        {{-- CONTENEDOR CENTRAL: Tarjeta Blanca (max-w-3xl) --}}
        <div class="w-full max-w-3xl bg-white rounded-2xl shadow-xl border border-[#c4c4c4] overflow-hidden">

            {{-- Encabezado del Formulario --}}
            <div class="bg-gray-50 px-8 py-8 border-b border-gray-100 text-center">
                <h1 class="text-xl text-[#1A1A1A] font-bold uppercase tracking-widest">
                    Registrar Incremento
                </h1>
                <p class="text-[10px] text-[#D4A017] font-bold uppercase tracking-[0.3em] mt-2 opacity-80">
                    Detalles del nuevo importe
                </p>
            </div>

            {{-- Cuerpo del Formulario --}}
            <div class="p-8 md:p-12">
                <form action="{{ route('incrementos.store') }}" method="POST" class="flex flex-col gap-8">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#1A1A1A] mb-3">
                            Contrato
                        </label>
                        <div class="relative">
                            <select name="id_contract" class="w-full bg-gray-50 border border-gray-300 rounded-lg py-4 pl-4 pr-10 text-lg text-[#1A1A1A] font-light focus:outline-none focus:border-[#D4A017] focus:ring-1 focus:ring-[#D4A017] appearance-none cursor-pointer transition-all">
                                <option value="">SELECCIONA UN CONTRATO</option>
                                @foreach($contract as $contract)
                                    <option value="{{ $contract->id }}">{{ $contract->nombre ?? 'CONTRATO '.$contract->id }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#1A1A1A] mb-3">
                            Importe Base ($)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 font-bold">$</span>
                            <input type="number" name="importe_base" step="0.01" placeholder="0.00" required
                                   class="w-full bg-gray-50 border border-gray-300 rounded-lg py-4 pl-10 pr-4 text-xl text-[#1A1A1A] font-light focus:outline-none focus:border-[#D4A017] focus:ring-1 focus:ring-[#D4A017] transition-all placeholder-gray-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#1A1A1A] mb-3">
                                Fecha de Inicio
                            </label>
                            <input type="date" name="fecha_inicio" required
                                   class="w-full bg-gray-50 border border-gray-300 rounded-lg py-4 px-4 text-lg text-[#1A1A1A] font-light focus:outline-none focus:border-[#D4A017] focus:ring-1 focus:ring-[#D4A017] transition-all uppercase">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#1A1A1A] mb-3">
                                Fecha de Fin
                            </label>
                            <input type="date" name="fecha_fin"
                                   class="w-full bg-gray-50 border border-gray-300 rounded-lg py-4 px-4 text-lg text-[#1A1A1A] font-light focus:outline-none focus:border-[#D4A017] focus:ring-1 focus:ring-[#D4A017] transition-all uppercase">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-[#1A1A1A] text-white text-sm tracking-[0.2em] uppercase font-bold px-8 py-5 rounded-lg hover:bg-[#D4A017] hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                            Guardar Incremento
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
