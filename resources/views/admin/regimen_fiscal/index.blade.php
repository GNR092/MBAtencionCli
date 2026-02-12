@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-[#d8c495] uppercase tracking-wider">Gestión de Régimen Fiscal</h2>
        <a href="{{ route('regimen-fiscal.create') }}" class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] px-4 py-2 rounded-lg font-bold text-sm transition-all uppercase">
            Nuevo Régimen
        </a>
    </div>

    <div class="table-container fade-in-content">
        <table>
            <thead>
            <tr>
                <th>ID (Clave)</th>
                <th>Nombre del Régimen</th>
                <th>Tasa de Retención</th>
                <th class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($regimenes as $regimen)
                <tr>
                    <td>{{ $regimen->id_regimen }}</td>
                    <td>{{ $regimen->nombre_regimen }}</td>
                    <td>{{ $regimen->tasa_retencion }}%</td>
                    <td class="flex justify-center gap-4">
                        <a href="{{ route('regimen-fiscal.edit', $regimen->id_regimen) }}" class="text-blue-400 hover:text-blue-300 transition-colors">
                            Editar
                        </a>
                        <form action="{{ route('regimen-fiscal.destroy', $regimen->id_regimen) }}" method="POST" onsubmit="return confirm('¿Desea eliminar este registro?')">
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
