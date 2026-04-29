@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-full mx-auto p-4 md:p-6">

        <header class="mb-10 px-2">
            <div class="flex items-center">
                <div class="flex items-baseline gap-4">
                    <span class="text-dorado-400 text-sm font-serif italic">|</span>
                    <h1 class="page-title">
                        Cumpleaños
                    </h1>
                </div>
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

    </div>
</div>
@endsection
