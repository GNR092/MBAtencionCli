<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de envíos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-gray-900 min-h-screen">

<div class="max-w-full mx-auto p-6">

    <header class="mb-8 flex items-center gap-4">
        <a href="{{ route('hbd.index') }}"
            class="text-gray-400 hover:text-white transition text-sm">← Volver</a>
        <h1 class="text-white text-2xl font-bold">📬 Historial de envíos</h1>
    </header>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 uppercase text-xs border-b border-gray-700">
                    <th class="text-left py-3 px-4">Fecha</th>
                    <th class="text-left py-3 px-4">Usuario</th>
                    <th class="text-left py-3 px-4">Email</th>
                    <th class="text-left py-3 px-4">Plantilla</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sents as $sent)
                <tr class="border-t border-white/5 hover:bg-white/5 transition">
                    <td class="py-3 px-4 text-white/70">{{ $sent->sent_date->isoFormat('D [de] MMM YYYY') }}</td>
                    <td class="py-3 px-4 text-white">{{ $sent->user->name ?? '—' }}</td>
                    <td class="py-3 px-4 text-white/50">{{ $sent->recipient_email }}</td>
                    <td class="py-3 px-4 text-[#d8c495]">{{ $sent->template->name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-8 text-center text-gray-500">No hay envíos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sents->hasPages())
    <div class="mt-6">
        {{ $sents->links() }}
    </div>
    @endif

</div>

</body>
</html>
