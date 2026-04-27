<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cumpleaños</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-900 min-h-screen">

<div class="max-w-full mx-auto p-4 md:p-6">

    <header class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-white text-2xl font-bold">🎂 Cumpleaños</h1>
            <p class="text-gray-400 text-sm mt-1">Gestión de correos de cumpleaños</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('hbd.settings') }}"
                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition">
                ⚙️ Configuración
            </a>
            <a href="{{ route('hbd.canvas') }}"
                class="px-4 py-2 bg-[#d8c495] hover:bg-[#c4b07a] text-gray-900 font-semibold text-sm rounded-lg transition">
                🎨 Editar plantilla
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-500/20 border border-green-500 text-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($settings->is_active)
        <div class="mb-6 px-4 py-3 bg-[#d8c495]/10 border border-[#d8c495]/30 rounded-lg text-sm">
            <span class="text-[#d8c495] font-semibold">📧 Envío automático activo</span>
            <span class="text-gray-300 ml-2">
                {{ $settings->send_days_before == 0 ? 'El día del cumpleaños' : $settings->send_days_before . ' día(s) antes' }}
                a las {{ $settings->send_hour }}
            </span>
        </div>
    @else
        <div class="mb-6 px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-lg text-sm">
            <span class="text-red-400 font-semibold">⚠️ Envío automático desactivado</span>
        </div>
    @endif

    <section class="mb-10">
        <h2 class="text-[#d8c495] text-sm font-bold uppercase tracking-widest mb-4">🎁 Este mes</h2>
        @if($esteMes->isEmpty())
            <p class="text-gray-500 text-sm italic">Ningún cumpleañero este mes.</p>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach($esteMes as $user)
            <div class="relative flex items-center gap-4 p-4 rounded-xl border
                {{ $user->es_hoy ? 'bg-[#d8c495]/15 border-[#d8c495]/60 shadow-lg' : 'bg-white/5 border-white/10' }}">

                @if($user->es_hoy)
                <div class="absolute top-2 right-3 text-[10px] text-[#d8c495] font-bold uppercase tracking-widest animate-pulse">
                    ¡Hoy!
                </div>
                @endif

                <div class="text-center min-w-[44px]">
                    <div class="text-[#d8c495] font-bold text-2xl leading-none">
                        {{ \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d') }}
                    </div>
                    <div class="text-white/40 text-[10px] uppercase tracking-wider">
                        {{ \Carbon\Carbon::parse($user->fecha_nacimiento)->isoFormat('MMM') }}
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="text-white text-sm font-medium truncate">{{ $user->name }}</div>
                    <div class="text-white/40 text-[11px] mt-0.5">
                        {{ $user->edad }} años
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <button @click="enviar({{ $user->id }}, '{{ addslashes($user->name) }}')"
                        class="w-full py-1 px-2 bg-[#d8c495] hover:bg-[#c4b07a] text-gray-900 text-xs font-semibold rounded-lg transition"
                        :disabled="sending[{{ $user->id }}]">
                        <span x-text="sending[{{ $user->id }}] ? 'Enviando...' : '📧 Enviar'"></span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </section>

    <section class="mb-10">
        <h2 class="text-white/60 text-sm font-bold uppercase tracking-widest mb-4">📅 Próximo mes</h2>
        @if($proximoMes->isEmpty())
            <p class="text-gray-500 text-sm italic">Ningún cumpleañero el próximo mes.</p>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach($proximoMes as $user)
            <div class="flex items-center gap-4 p-4 rounded-xl border bg-white/3 border-white/8">
                <div class="text-center min-w-[44px]">
                    <div class="text-white/60 font-bold text-2xl leading-none">
                        {{ \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d') }}
                    </div>
                    <div class="text-white/30 text-[10px] uppercase tracking-wider">
                        {{ \Carbon\Carbon::parse($user->fecha_nacimiento)->isoFormat('MMM') }}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white/80 text-sm font-medium truncate">{{ $user->name }}</div>
                    <div class="text-white/30 text-[11px]">{{ $user->edad }} años · en {{ $user->dias_para_cumple }} días</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </section>

    @if($restantes->isNotEmpty())
    <section>
        <h2 class="text-white/40 text-sm font-bold uppercase tracking-widest mb-4">📋 Resto del año</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-500 uppercase text-xs">
                        <th class="text-left py-2 px-3">Inversionista</th>
                        <th class="text-left py-2 px-3">Fecha</th>
                        <th class="text-left py-2 px-3">Días</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($restantes as $user)
                    <tr class="border-t border-white/5">
                        <td class="py-2 px-3 text-white">{{ $user->name }}</td>
                        <td class="py-2 px-3 text-white/50">{{ \Carbon\Carbon::parse($user->fecha_nacimiento)->isoFormat('D [de] MMMM') }}</td>
                        <td class="py-2 px-3 text-white/40">{{ $user->dias_para_cumple }} días</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif

    <div class="mt-8 text-center">
        <a href="{{ route('hbd.historial') }}"
            class="text-[#d8c495]/60 hover:text-[#d8c495] text-sm underline">
            📬 Ver historial de envíos
        </a>
    </div>
</div>

<div x-data="{ sending: {}, toast: { show: false, msg: '' } }" x-init="
    window.enviar = async function(id, name) {
        sending[id] = true;
        try {
            const res = await fetch('/hbd/enviar/' + id, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await res.json();
            toast.msg = data.success ? 'Correo enviado a ' + name : 'Error: ' + data.message;
            toast.show = true;
        } catch(e) {
            toast.msg = 'Error al enviar';
            toast.show = true;
        }
        sending[id] = false;
        setTimeout(() => toast.show = false, 3000);
    }
" @keydown.escape.window="toast.show = false">

    <div x-show="toast.show" x-cloak
        class="fixed bottom-6 right-6 px-4 py-3 bg-green-600 text-white rounded-xl shadow-lg">
        <span x-text="toast.msg"></span>
    </div>
</div>

</body>
</html>
