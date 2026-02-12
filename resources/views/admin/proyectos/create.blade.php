@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl mx-auto fade-in-content">
        <h2 class="text-2xl font-bold text-[#d8c495] uppercase tracking-wider mb-6">Nuevo Proyecto</h2>

        <form action="{{ route('proyectos.store') }}" method="POST" class="bg-[#112134]/60 backdrop-blur-md p-8 rounded-xl border border-[#d8c495]/20">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs uppercase text-[#d8c495] mb-1">ID / Código del Proyecto</label>
                    <input type="text" name="id_proyecto" value="{{ old('id_proyecto') }}" required class="w-full p-2 text-black bg-white rounded-md">
                </div>
                <div>
                    <label class="block text-xs uppercase text-[#d8c495] mb-1">Nombre del Proyecto</label>
                    <input type="text" name="nombre_proyecto" value="{{ old('nombre_proyecto') }}" required class="w-full p-2 text-black bg-white rounded-md">
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-[#d8c495] text-[#112134] px-6 py-2 rounded font-bold uppercase text-sm">Guardar Proyecto</button>
                <a href="{{ route('proyectos.index') }}" class="bg-white/10 text-white px-6 py-2 rounded font-bold uppercase text-sm text-center">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
