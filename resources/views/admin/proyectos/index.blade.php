@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-[#d8c495] uppercase tracking-wider">Altas de Proyectos</h2>
        <a href="{{ route('proyectos.create') }}" class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] px-4 py-2 rounded-lg font-bold text-sm transition-all uppercase">
            Nuevo Proyecto
        </a>
    </div>

    <div class="table-container fade-in-content">
        <table>
            <thead>
            <tr>
                <th>Código Proyecto</th>
                <th>Nombre del Proyecto</th>
                <th class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($proyectos as $proyecto)
                <tr>
                    <td>{{ $proyecto->id_proyecto }}</td>
                    <td>{{ $proyecto->nombre_proyecto }}</td>
                    <td class="flex justify-center gap-4">
                        <a href="{{ route('proyectos.edit', $proyecto->id_proyecto) }}" class="text-blue-400 hover:text-blue-300 transition-colors">Editar</a>
                        <form action="{{ route('proyectos.destroy', $proyecto->id_proyecto) }}" method="POST" onsubmit="return confirm('¿Eliminar este proyecto?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 transition-colors">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
