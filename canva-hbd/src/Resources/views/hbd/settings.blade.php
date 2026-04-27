<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configuración - Cumpleaños</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-900 min-h-screen">

<div class="max-w-2xl mx-auto p-6">

    <header class="mb-8 flex items-center gap-4">
        <a href="{{ route('hbd.index') }}"
            class="text-gray-400 hover:text-white transition text-sm">← Volver</a>
        <h1 class="text-white text-2xl font-bold">⚙️ Configuración</h1>
    </header>

    <form @submit.prevent="save()" x-data="{
        auto_send: {{ $settings->auto_send ? 'true' : 'false' }},
        send_days_before: {{ $settings->send_days_before }},
        send_hour: '{{ $settings->send_hour }}',
        subject_template: '{{ $settings->subject_template }}',
        is_active: {{ $settings->is_active ? 'true' : 'false' }},
        saving: false,
        toast: { show: false, msg: '', type: 'success' },

        async save() {
            this.saving = true;
            try {
                const res = await fetch('{{ route('hbd.settings.save') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        auto_send: this.auto_send,
                        send_days_before: parseInt(this.send_days_before),
                        send_hour: this.send_hour,
                        subject_template: this.subject_template,
                        is_active: this.is_active
                    })
                });
                const data = await res.json();
                this.toast = { show: true, msg: data.message || 'Guardado', type: 'success' };
            } catch(e) {
                this.toast = { show: true, msg: 'Error al guardar', type: 'error' };
            }
            this.saving = false;
            setTimeout(() => this.toast.show = false, 3000);
        }
    }">

        <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-white font-semibold">Sistema activo</h3>
                    <p class="text-gray-400 text-sm">Activa o desactiva el envío de cumpleaños</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="is_active" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#d8c495]"></div>
                </label>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-white font-semibold">Envío automático</h3>
                    <p class="text-gray-400 text-sm">Enviar correos automáticamente según la configuración</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="auto_send" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#d8c495]"></div>
                </label>
            </div>

            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wider mb-2">Días antes del cumpleaños</label>
                <input type="number" min="0" max="30" x-model="send_days_before"
                    class="w-full bg-gray-700 text-white text-sm px-4 py-3 rounded-xl border border-gray-600 focus:border-[#d8c495] focus:outline-none">
                <p class="text-gray-500 text-xs mt-1">0 = el mismo día del cumpleaños</p>
            </div>

            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wider mb-2">Hora de envío</label>
                <input type="time" x-model="send_hour"
                    class="w-full bg-gray-700 text-white text-sm px-4 py-3 rounded-xl border border-gray-600 focus:border-[#d8c495] focus:outline-none">
            </div>

            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wider mb-2">Plantilla del asunto</label>
                <input type="text" x-model="subject_template"
                    class="w-full bg-gray-700 text-white text-sm px-4 py-3 rounded-xl border border-gray-600 focus:border-[#d8c495] focus:outline-none"
                    placeholder="¡Feliz cumpleaños, {nombre}!">
                <p class="text-gray-500 text-xs mt-1">Usa <code class="text-[#d8c495]">{nombre}</code> para insertar el nombre del cumpleañero</p>
            </div>

        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit"
                class="px-6 py-3 bg-[#d8c495] hover:bg-[#c4b07a] text-gray-900 font-semibold rounded-xl transition"
                :disabled="saving">
                <span x-text="saving ? 'Guardando...' : '💾 Guardar configuración'"></span>
            </button>
        </div>

    </form>

    <div class="mt-8 bg-gray-800 rounded-2xl p-6 border border-gray-700">
        <h3 class="text-white font-semibold mb-4">📋 Comando Artisan</h3>
        <div class="bg-gray-900 rounded-xl p-4 font-mono text-sm text-green-400">
            <p class="mb-2"># Envío automático (verificado por el scheduler)</p>
            <p class="mb-4">php artisan hbd:send</p>
            <p class="mb-2"># Simular envío sin enviar realmente</p>
            <p>php artisan hbd:send --dry-run</p>
        </div>
        <p class="text-gray-400 text-sm mt-3">El scheduler de Laravel ejecuta <code>hbd:send</code> diariamente a la hora configurada.</p>
    </div>

</div>

<div x-data="{ toast: { show: false, msg: '', type: 'success' } }"
    x-show="toast.show" x-cloak
    class="fixed bottom-6 right-6 px-4 py-3 bg-green-600 text-white rounded-xl shadow-lg">
    <span x-text="toast.msg"></span>
</div>

</body>
</html>
