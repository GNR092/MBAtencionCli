@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="page-title">Monitoreo de Envios de Cumpleanos</h1>
            <a href="{{ route('usuarios.cumpleanios') }}" class="btn-dorado">Volver</a>
        </div>

        <div class="tabla-dorada-container">
            <div class="overflow-x-auto custom-scroll">
                <table class="tabla-dorada">
                    <thead>
                        <tr>
                            <th>Inversionista</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Intentos</th>
                            <th>Error</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                            <tr>
                                <td>{{ $delivery->user->name ?? 'N/A' }}</td>
                                <td>{{ optional($delivery->birthday_date)->format('d/m/Y') }}</td>
                                <td>{{ strtoupper($delivery->status) }}</td>
                                <td>{{ $delivery->attempts }}</td>
                                <td class="text-xs text-white/60">{{ $delivery->error_message ? \Illuminate\Support\Str::limit($delivery->error_message, 80) : '-' }}</td>
                                <td>
                                    @if($delivery->status === 'failed')
                                        <form method="POST" action="{{ route('cumpleanios.deliveries.retry', $delivery) }}">
                                            @csrf
                                            <button class="btn-dorado" type="submit">Reintentar</button>
                                        </form>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-white/50 py-4">No hay envios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $deliveries->links() }}</div>
    </div>
</div>
@endsection
