@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl mx-auto fade-in-content">
        <h2 class="text-2xl font-bold text-[#d8c495] uppercase tracking-wider mb-6">Editar Régimen Fiscal</h2>

        <form action="{{ route('regimen-fiscal.update', $regimen->id_regimen) }}" method="POST" class="bg-[#112134]/60 backdrop-blur-md p-8 rounded-xl border border-[#d8c495]/20">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                {{-- ID del Régimen --}}
                <div>
                    <label class="block text-xs uppercase text-[#d8c495] mb-1">ID del Régimen (Clave)</label>
                    {{-- Se muestra deshabilitado porque es la llave primaria manual y no debe cambiarse en la edición --}}
                    <input type="number" value="{{ $regimen->id_regimen }}" disabled
                           class="w-full p-2 text-gray-500 bg-gray-300/50 cursor-not-allowed rounded-md border border-white/10">
                    <p class="text-[10px] text-gray-400 mt-1 italic">* La clave del régimen no puede ser modificada.</p>
                </div>

                {{-- Nombre del Régimen --}}
                <div>
                    <label class="block text-xs uppercase text-[#d8c495] mb-1">Nombre del Régimen</label>
                    <input type="text" name="nombre_regimen" value="{{ old('nombre_regimen', $regimen->nombre_regimen) }}" required
                           class="w-full p-2 text-black bg-white rounded-md outline-none focus:ring-2 focus:ring-[#d8c495]">
                    @error('nombre_regimen')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Tasa de Retención --}}
                <div>
                    <label class="block text-xs uppercase text-[#d8c495] mb-1">Tasa de Retención (%)</label>
                    <input type="number" step="0.01" name="tasa_retencion" value="{{ old('tasa_retencion', $regimen->tasa_retencion) }}" required
                           class="w-full p-2 text-black bg-white rounded-md outline-none focus:ring-2 focus:ring-[#d8c495]">
                    @error('tasa_retencion')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] px-6 py-2 rounded font-bold uppercase text-sm transition-all active:scale-95">
                    Actualizar Cambios
                </button>
                <a href="{{ route('regimen-fiscal.index') }}" class="bg-white/10 hover:bg-white/20 text-white px-6 py-2 rounded font-bold uppercase text-sm transition-all text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
