@extends('layouts.admin')

@section('content')
<h1 class="text-center text-black text-3xl p-2">Incrementos de Importe</h1>
<div class="max-w-6xl mx-auto p-6 bg-[#0b0b0b] rounded-2xl shadow-lg">
    

    <a href="{{ route('incrementos.create') }}" class="bg-[#d8c495] hover:bg-[#c9a143] text-white px-4 py-2 rounded">+ Nuevo Incremento</a>

    <div class="overflow-x-auto bg-white rounded-lg shadow-md mt-4">

            <table class="w-full text-sm text-center border-collapse">
        <thead class="bg-[#2f2f2f] text-[#fff] uppercase text-xs tracking-wider">
            <tr>
                <th class="px-6 py-3 border-b">Contrato</th>
                <th class="px-6 py-3 border-b">Importe Base</th>
                <th class="px-6 py-3 border-b">Inicio</th>
                <th class="px-6 py-3 border-b">Fin</th>
                <th class="px-6 py-3 border-b">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incrementos as $inc)
            <tr>
                <td class="px-6 py-4">{{ $inc->contract->nombre ?? 'Contrato '.$inc->contract->id }}</td>
                <td class="px-6 py-4">${{ number_format($inc->importe_base, 2) }}</td>
                <td class="px-6 py-4">{{ $inc->fecha_inicio }}</td>
                <td class="px-6 py-4">{{ $inc->fecha_fin ?? 'Indefinido' }}</td>
                <td class="px-6 py-4">
                    <form action="{{ route('incrementos.destroy', $inc->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="bg-red-600 text-white px-2 py-1 rounded" onclick="return confirm('¿Eliminar incremento?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

</div>
@endsection
