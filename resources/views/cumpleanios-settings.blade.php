@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="page-title">Configuracion de Envio de Cumpleanos</h1>
            <a href="{{ route('usuarios.cumpleanios') }}" class="btn-dorado">Volver</a>
        </div>

        <form action="{{ route('cumpleanios.settings.save') }}" method="POST" class="bg-white/5 border border-white/10 rounded-xl p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-white/80 mb-1">Hora global de envio</label>
                <input type="time" name="send_time" value="{{ old('send_time', substr($settings->send_time, 0, 5)) }}" class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white" required>
            </div>

            <div>
                <label class="block text-sm text-white/80 mb-1">Timezone</label>
                <input name="timezone" value="{{ old('timezone', $settings->timezone) }}" class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white" required>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-white/80 mb-1">Maximo de intentos</label>
                    <input type="number" min="1" max="10" name="max_attempts" value="{{ old('max_attempts', $settings->max_attempts) }}" class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white" required>
                </div>
                <div>
                    <label class="block text-sm text-white/80 mb-1">Minutos entre reintentos</label>
                    <input type="number" min="1" max="180" name="retry_minutes" value="{{ old('retry_minutes', $settings->retry_minutes) }}" class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white" required>
                </div>
            </div>

            <button class="btn-dorado" type="submit">Guardar configuracion</button>
        </form>
    </div>
</div>
@endsection
