@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-full mx-auto p-4 md:p-6">

        <header class="mb-10 px-2">
            <div class="flex items-center justify-between">
                <div class="flex items-baseline gap-4">
                    <span class="text-dorado-400 text-sm font-serif italic">|</span>
                    <h1 class="page-title">
                        Cumpleaños
                    </h1>
                </div>
                <button type="button" onclick="openPlantillaModal()" class="btn-dorado flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Plantilla de mensaje
                </button>
            </div>
        </header>

        <section class="mb-8 px-2">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('cumpleanios.template') }}" class="btn-dorado">Editor de plantilla</a>
                <a href="{{ route('cumpleanios.settings') }}" class="btn-dorado">Configuracion de envio</a>
                <a href="{{ route('cumpleanios.deliveries') }}" class="btn-dorado">Monitoreo de envios</a>
            </div>
        </section>

        {{-- Este mes --}}
        <section class="mb-10 px-2">
            <div class="flex items-center gap-3 mb-4">
                <svg class="w-6 h-6 text-[#d8c495]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 birthday-cake.svg120l-1.5 6.5A2 2 0 0117.164 22H6.836a2 2 0 01-1.994-1.834L3 12m6-6h12a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2v-6a2 2 0 012-2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V2m0 0c-1.5 0-3 1-3 3s1.5 3 3 3 3-1 3-3-1.5-3-3-3z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 6c0-1.5 1-3 2.5-3s2.5 1.5 2.5 3M13 6c0-1.5 1-3 2.5-3s2.5 1.5 2.5 3"/>
                </svg>
                <h2 class="text-[#d8c495] text-sm font-bold uppercase tracking-widest">Este mes</h2>
                <span class="text-white/30 text-xs">— {{ $hoy->isoFormat('MMMM YYYY') }}</span>
            </div>

            @if($esteMes->isEmpty())
                <p class="text-white/30 text-sm italic pl-2">Ningún inversionista cumple años este mes.</p>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
                @foreach($esteMes as $inv)
                @php
                    $cumpleDate = \Carbon\Carbon::parse($inv->fecha_nacimiento);
                    $esHoy = $cumpleDate->setYear($hoy->year)->isToday();
                @endphp
                <div class="relative flex items-center gap-4 p-4 rounded-xl border
                    {{ $esHoy
                        ? 'bg-[#d8c495]/15 border-[#d8c495]/60 shadow-lg shadow-[#d8c495]/10'
                        : 'bg-white/5 border-white/10' }}">
                    @if($esHoy)
                    <div class="absolute top-2 right-3 text-[10px] text-[#d8c495] font-bold uppercase tracking-widest animate-pulse">
                        ¡Hoy!
                    </div>
                    @endif
                    <div class="text-center min-w-[44px]">
                        <div class="text-[#d8c495] font-bold text-2xl leading-none">{{ \Carbon\Carbon::parse($inv->fecha_nacimiento)->format('d') }}</div>
                        <div class="text-white/40 text-[10px] uppercase tracking-wider">{{ \Carbon\Carbon::parse($inv->fecha_nacimiento)->isoFormat('MMM') }}</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-white text-sm font-medium truncate">{{ $inv->name }}</div>
                        <div class="text-white/40 text-[11px] mt-0.5">
                            {{ $inv->edad }} años
                            @if(!$esHoy)
                                · en {{ $inv->dias_para_cumple }} día{{ $inv->dias_para_cumple !== 1 ? 's' : '' }}
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </section>

        {{-- Próximo mes --}}
        <section class="mb-10 px-2">
            <div class="flex items-center gap-3 mb-4">
                <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-white/60 text-sm font-bold uppercase tracking-widest">Próximo mes</h2>
                <span class="text-white/30 text-xs">— {{ $hoy->copy()->addMonth()->isoFormat('MMMM') }}</span>
            </div>

            @if($proximoMes->isEmpty())
                <p class="text-white/30 text-sm italic pl-2">Ningún inversionista cumple años el próximo mes.</p>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
                @foreach($proximoMes as $inv)
                <div class="flex items-center gap-4 p-4 rounded-xl border bg-white/3 border-white/8">
                    <div class="text-center min-w-[44px]">
                        <div class="text-white/60 font-bold text-2xl leading-none">{{ \Carbon\Carbon::parse($inv->fecha_nacimiento)->format('d') }}</div>
                        <div class="text-white/30 text-[10px] uppercase tracking-wider">{{ \Carbon\Carbon::parse($inv->fecha_nacimiento)->isoFormat('MMM') }}</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-white/80 text-sm font-medium truncate">{{ $inv->name }}</div>
                        <div class="text-white/30 text-[11px] mt-0.5">{{ $inv->edad }} años · en {{ $inv->dias_para_cumple }} días</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </section>

        {{-- Resto del año --}}
        @if($restantes->isNotEmpty())
        <section class="px-2">
            <div class="flex items-center gap-3 mb-4">
                <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 4H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2zM16 2v4M8 2v4M3 10h18"/>
                </svg>
                <h2 class="text-white/40 text-sm font-bold uppercase tracking-widest">Resto del año</h2>
            </div>
            <div class="tabla-dorada-container">
                <div class="overflow-x-auto custom-scroll">
                    <table class="tabla-dorada">
                        <thead>
                            <tr>
                                <th>Inversionista</th>
                                <th>Fecha de nacimiento</th>
                                <th>Cumpleaños</th>
                                <th>Días restantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($restantes as $inv)
                            <tr>
                                <td class="font-medium">{{ $inv->name }}</td>
                                <td class="text-white/50">{{ \Carbon\Carbon::parse($inv->fecha_nacimiento)->isoFormat('D [de] MMMM [de] YYYY') }}</td>
                                <td class="text-white/70">{{ \Carbon\Carbon::parse($inv->fecha_nacimiento)->isoFormat('D [de] MMMM') }}</td>
                                <td>
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] bg-white/5 text-white/50 border border-white/10">
                                        {{ $inv->dias_para_cumple }} días
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        @endif

        {{-- Sin fecha registrada --}}
        @php
            $sinFecha = \App\Models\User::where('role', 'usuario')->whereNull('fecha_nacimiento')->count();
        @endphp
        @if($sinFecha > 0)
        <div class="mt-8 px-2">
            <p class="text-white/20 text-xs italic">
                {{ $sinFecha }} inversionista{{ $sinFecha !== 1 ? 's' : '' }} sin fecha de nacimiento registrada.
                Puedes agregarla desde <a href="{{ route('usuarios.index') }}" class="text-[#d8c495]/50 underline hover:text-[#d8c495]">Administrador de Usuarios</a>.
            </p>
        </div>
        @endif

        <!-- Modal Plantilla de Mensaje -->
        <div id="plantillaModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="bg-[#1a1a2e] border border-[#d8c495]/30 rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-5 border-b border-white/10">
                    <h3 class="text-[#d8c495] font-bold text-lg">Plantilla de Mensaje de Cumpleaños</h3>
                    <button type="button" onclick="closePlantillaModal()" class="text-white/50 hover:text-white text-2xl leading-none">&times;</button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-white/70 text-sm font-medium mb-2">Nombre del cumpleañero</label>
                        <input type="text" id="nombreCumpleaniero" placeholder="Ingresa el nombre" class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495]/50">
                    </div>
                    <div>
                        <label class="block text-white/70 text-sm font-medium mb-2">Mensaje</label>
                        <textarea id="mensajeCumpleanios" rows="8" class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/30 focus:outline-none focus:border-[#d8c495]/50 resize-none">¡Feliz cumpleaños, [NOMBRE]! 🎂

Te deseamos un día lleno de alegría, amor y bendiciones. Que este nuevo año de vida esté lleno de éxitos y momentos inolvidables.

¡Disfruta tu día! 🎉</textarea>
                    </div>
                </div>
                <div class="flex gap-3 p-5 border-t border-white/10">
                    <button type="button" onclick="copiarPlantilla()" class="flex-1 bg-[#d8c495]/20 hover:bg-[#d8c495]/30 text-[#d8c495] border border-[#d8c495]/40 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors">
                        Copiar al portapapeles
                    </button>
                    <button type="button" onclick="closePlantillaModal()" class="flex-1 bg-white/5 hover:bg-white/10 text-white/70 border border-white/20 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function openPlantillaModal() {
    document.getElementById('plantillaModal').classList.remove('hidden');
    document.getElementById('plantillaModal').style.display = 'flex';
}

function closePlantillaModal() {
    document.getElementById('plantillaModal').classList.add('hidden');
    document.getElementById('plantillaModal').style.display = 'none';
}

function copiarPlantilla() {
    const nombre = document.getElementById('nombreCumpleaniero').value.trim();
    let mensaje = document.getElementById('mensajeCumpleanios').value;
    
    if (nombre) {
        mensaje = mensaje.replace('[NOMBRE]', nombre);
    }
    
    navigator.clipboard.writeText(mensaje).then(() => {
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = '¡Copiado!';
        btn.classList.add('bg-green-500/30', 'border-green-500/50', 'text-green-400');
        setTimeout(() => {
            btn.textContent = originalText;
            btn.classList.remove('bg-green-500/30', 'border-green-500/50', 'text-green-400');
        }, 2000);
    }).catch(err => {
        console.error('Error al copiar:', err);
    });
}
</script>
@endpush
