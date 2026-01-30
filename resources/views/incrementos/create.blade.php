@extends('layouts.admin')

@section('content')
<h1 class="text-center text-black text-3xl p-2">Registrar Incremento de Importe</h1>
<div class="max-w-3xl mx-auto bg-[#2f2f2f] p-6 rounded shadow">


    <form action="{{ route('incrementos.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block font-semibold mb-1 text-white">Contrato:</label>
            <select name="id_contract" class="w-full border rounded p-2 border-gray-300 bg-gray-200 text-black">
                <option value="">Selecciona un contrato</option>
                @foreach($contract as $contract)
                    <option value="{{ $contract->id }}">{{ $contract->nombre ?? 'Contrato '.$contract->id }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1 text-white">Importe base ($):</label>
            <input type="number" name="importe_base" step="0.01" class="border-gray-300 bg-gray-200 w-full border rounded p-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1 text-white">Fecha de inicio:</label>
            <input type="date" name="fecha_inicio" class="border-gray-300 bg-gray-200 w-full border rounded p-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1 text-white">Fecha de fin:</label>
            <input type="date" name="fecha_fin" class="border-gray-300 bg-gray-200 w-full border rounded p-2">
        </div>

        <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-white px-4 py-2 rounded">Guardar</button>
    </form>
</div>
@endsection
