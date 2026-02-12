@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl mx-auto fade-in-content">
        <h2 class="text-2xl font-bold text-[#d8c495] uppercase tracking-wider mb-6">Nuevo Régimen Fiscal</h2>

        <form action="{{ route('regimen-fiscal.store') }}" method="POST" class="bg-[#112134]/60 backdrop-blur-md p-8 rounded-xl border border-[#d8c495]/20">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs uppercase text-[#d8c495] mb-1">ID del Régimen (Manual)</label>
                    <input type="number" name="id_regimen" required class="w-full p-2 text-black">
                </div>
                <div>
                    <label class="block text-xs uppercase text-[#d8c495] mb-1">Nombre del Régimen</label>
                    <input type="text" name="nombre_regimen" required class="w-full p-2 text-black">
                </div>
                <div>
                    <label class="block text-xs uppercase text-[#d8c495] mb-1">Tasa de Retención (%)</label>
                    <input type="number" step="0.01" name="tasa_retencion" required class="w-full p-2 text-black">
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-[#d8c495] text-[#112134] px-6 py-2 rounded font-bold uppercase text-sm">Guardar</button>
                <a href="{{ route('regimen-fiscal.index') }}" class="bg-white/10 text-white px-6 py-2 rounded font-bold uppercase text-sm">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
