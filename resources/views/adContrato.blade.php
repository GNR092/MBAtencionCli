@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <a href="{{ route('admin.contratos.index') }}"
                class="inline-flex items-center gap-2 text-sm text-[#d8c495] hover:text-white transition-colors mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver a contratos
            </a>
            <div class="flex items-baseline gap-4">
                <span class="text-dorado-400 text-sm font-serif italic">|</span>
                <h1 class="text-white text-5xl md:text-7xl font-extralight tracking-[-0.02em] leading-none uppercase">
                {{ $contract ? 'Editar Contrato' : 'Crear Contrato' }}
            </h1>
            </div>
        </header>

        <div class="bg-[#112134]/60 backdrop-blur-md rounded-2xl border border-[#d8c495]/20 shadow-2xl overflow-hidden">
            <div class="px-8 py-6 border-b border-[#d8c495]/20">
                <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">
                    Información del Contrato
                </h2>
                <p class="text-[10px] text-[#d8c495]/50 uppercase tracking-[0.3em] mt-1">
                    Modifica los datos del contrato
                </p>
            </div>

            <form action="{{ $contract ? route('admin.contratos.actualizar', $contract->id) : route('admin.contratos.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                @if($contract)
                @method('PUT')
                @endif

                <!-- Usuario -->
                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Inversionista
                    </label>
                    @if($contract)
                    <div class="bg-white/5 border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white">
                        {{ $contract->user->name ?? 'Sin asignar' }}
                    </div>
                    @else
                    <select name="user_id" id="user_id" required
                        class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]">
                        <option value="" class="bg-[#0d1f30] text-white/40">Seleccionar inversionista</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" class="bg-[#0d1f30] text-white">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>

                <!-- Proyecto -->
                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Proyecto
                    </label>
                    @if($contract)
                    <div class="bg-white/5 border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white">
                        {{ $contract->userProyecto?->proyecto?->nombre_proyecto ?? 'Sin proyecto' }}
                    </div>
                    @else
                    <select name="proyect" id="proyect" required
                        class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]">
                        <option value="" class="bg-[#0d1f30] text-white/40">Selecciona primero un inversionista</option>
                    </select>
                    @endif
                </div>

                @if(!$contract)
                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Departamento
                    </label>
                    <select name="id_user_depto" id="id_user_depto"
                        class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]">
                        <option value="" class="bg-[#0d1f30] text-white/40">Mantener/seleccionar departamento existente</option>
                    </select>
                    <p id="id_user_depto_help" class="text-white/50 text-xs mt-2"></p>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="nuevo_depto_nombre" id="nuevo_depto_nombre"
                            placeholder="Si no aparece, escribe nuevo departamento"
                            class="w-full bg-[#0d1f30] border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]">
                        <input type="text" name="nuevo_depto_predial" id="nuevo_depto_predial"
                            placeholder="Predial opcional del nuevo departamento"
                            class="w-full bg-[#0d1f30] border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]">
                    </div>
                </div>
                @endif

                <!-- Fechas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Fecha de Inicio
                        </label>
                        <div class="relative">
                            <input type="date" 
                                name="fecha_inicio"
                                value="{{ $contract ? $contract->fecha_inicio : '' }}"
                                {{ $contract ? 'disabled' : '' }}
                                class="w-full bg-white/5 border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white/70 {{ $contract ? 'cursor-not-allowed' : '' }}">
                            @if($contract)
                            <input type="hidden" name="fecha_inicio" value="{{ $contract->fecha_inicio }}">
                            @endif
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#d8c495]/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Fecha de Terminación
                        </label>
                        <div class="relative">
                            <input type="date" 
                                name="fecha_terminacion"
                                value="{{ $contract ? $contract->fecha_terminacion : '' }}"
                                {{ $contract ? 'disabled' : '' }}
                                class="w-full bg-white/5 border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white/70 {{ $contract ? 'cursor-not-allowed' : '' }}">
                            @if($contract)
                            <input type="hidden" name="fecha_terminacion" value="{{ $contract->fecha_terminacion }}">
                            @endif
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#d8c495]/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Importe Bruto -->
                <div class="mb-8">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Importe Bruto de Renta *
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-[#d8c495]/50 font-bold">$</span>
                        <input type="text" name="importe_bruto_renta" id="importe_bruto_renta" 
                            placeholder="0.00"
                            value="{{ $contract ? number_format($contract->importe_bruto_renta, 2) : '' }}"
                            class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg pl-10 pr-4 py-3 text-xl text-white focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-all"
                            required>
                    </div>
                    <p class="text-[10px] text-white/40 mt-2">Ejemplo: $1,500,000.00</p>
                </div>

                <!-- Estado del contrato -->
                <div class="mb-8">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Estado del Contrato
                    </label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="activo" name="activo" value="1"
                                {{ $contract && $contract->estado === 'activo' ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-[#d8c495]/30 bg-white/10 text-[#d8c495] focus:ring-[#d8c495] focus:ring-offset-0">
                            <span class="text-white/80">Activo</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="inactivo" name="inactivo" value="1"
                                {{ $contract && $contract->estado === 'inactivo' ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-[#d8c495]/30 bg-white/10 text-[#d8c495] focus:ring-[#d8c495] focus:ring-offset-0">
                            <span class="text-white/80">Inactivo</span>
                        </label>
                    </div>
                    <div id="estado-error" class="hidden bg-red-500/20 border border-red-400/30 text-red-300 text-sm px-4 py-3 rounded-lg mt-3"></div>
                </div>

                <!-- Archivo -->
                <div class="mb-8">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Documento del Contrato (PDF)
                        @if(!$contract)
                        <span class="text-red-400">*</span>
                        @endif
                    </label>
                    <div class="border-2 border-dashed border-[#d8c495]/30 rounded-lg p-6 text-center hover:border-[#d8c495]/50 transition-colors">
                        <input type="file" name="archivo" accept=".pdf" 
                            class="hidden" id="archivoInput"
                            {{ !$contract ? 'required' : '' }}
                            onchange="document.getElementById('archivoLabel').textContent = this.files[0]?.name || 'Seleccionar archivo'">
                        <label for="archivoInput" class="cursor-pointer">
                            <svg class="w-10 h-10 mx-auto text-[#d8c495]/50 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span id="archivoLabel" class="text-white/70">{{ $contract->nombre ?? 'Seleccionar archivo' }}</span>
                            <p class="text-[10px] text-white/40 mt-1">PDF máximo 2MB</p>
                        </label>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-4 pt-4">
                    <button type="submit"
                        class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg transition-all flex-1">
                        {{ $contract ? 'Guardar Cambios' : 'Crear Contrato' }}
                    </button>
                    <a href="{{ route('admin.contratos.index') }}"
                        class="border border-[#d8c495]/30 text-[#d8c495]/60 text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg hover:border-[#d8c495] hover:text-[#d8c495] transition-all text-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
<div id="alert" class="fixed top-5 right-5 flex items-center gap-3 px-6 py-4 bg-green-500/20 border border-green-400/30 text-green-300 rounded-xl shadow-2xl z-50 animate-fade-in-down">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
    <span>{{ session('success') }}</span>
    <button onclick="document.getElementById('alert').remove()" class="ml-2 font-bold hover:text-white">✕</button>
</div>
@endif

<script>
document.getElementById('activo').addEventListener('change', function() {
    if (this.checked) document.getElementById('inactivo').checked = false;
});
document.getElementById('inactivo').addEventListener('change', function() {
    if (this.checked) document.getElementById('activo').checked = false;
});

@if(!$contract)
const userSelect = document.getElementById('user_id');
const proyectSelect = document.getElementById('proyect');
const deptoSelect = document.getElementById('id_user_depto');
const deptoHelp = document.getElementById('id_user_depto_help');

function renderDeptos(deptos) {
    if (!deptoSelect) return;
    deptoSelect.innerHTML = '<option value="" class="bg-[#0d1f30] text-white/40">Mantener/seleccionar departamento existente</option>';

    if (!Array.isArray(deptos) || deptos.length === 0) {
        deptoHelp.textContent = 'El proyecto no tiene departamentos cargados. Puedes capturar uno nuevo abajo.';
        return;
    }

    deptoHelp.textContent = '';
    deptos.forEach((d) => {
        const opt = document.createElement('option');
        opt.value = d.id_user_depto;
        opt.className = 'bg-[#0d1f30] text-white';
        opt.textContent = d.nombre + (d.predial ? ' - Predial: ' + d.predial : '');
        deptoSelect.appendChild(opt);
    });
}

function clearDeptos() {
    if (!deptoSelect) return;
    deptoSelect.innerHTML = '<option value="" class="bg-[#0d1f30] text-white/40">Selecciona primero usuario y proyecto</option>';
    deptoHelp.textContent = '';
}

async function loadDeptos(userId, projectId) {
    if (!userId || !projectId) {
        clearDeptos();
        return;
    }

    deptoSelect.innerHTML = '<option value="" class="bg-[#0d1f30] text-white/40">Cargando departamentos...</option>';

    try {
        const resp = await fetch(`/api/users/${userId}/projects/${projectId}/departments`);
        if (!resp.ok) {
            renderDeptos([]);
            return;
        }
        const deptos = await resp.json();
        renderDeptos(deptos);
    } catch (_) {
        renderDeptos([]);
    }
}

userSelect.addEventListener('change', function() {
    const userId = this.value;
    proyectSelect.innerHTML = '<option value="" class="bg-[#0d1f30] text-white/40">Cargando proyectos...</option>';
    proyectSelect.disabled = true;
    clearDeptos();

    if (!userId) {
        proyectSelect.innerHTML = '<option value="" class="bg-[#0d1f30] text-white/40">Selecciona primero un inversionista</option>';
        proyectSelect.disabled = false;
        return;
    }

    fetch(`/api/users/${userId}/projects`)
        .then(res => res.json())
        .then(proyectos => {
            proyectSelect.innerHTML = '<option value="" class="bg-[#0d1f30] text-white/40">Seleccionar proyecto</option>';
            if (proyectos.length === 0) {
                proyectSelect.innerHTML = '<option value="" class="bg-[#0d1f30] text-white/40">Sin proyectos asignados</option>';
            } else {
                proyectos.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id_proyecto;
                    opt.textContent = p.nombre_proyecto;
                    opt.className = 'bg-[#0d1f30] text-white';
                    proyectSelect.appendChild(opt);
                });
            }
            proyectSelect.disabled = false;
        })
        .catch(() => {
            proyectSelect.innerHTML = '<option value="" class="bg-[#0d1f30] text-white/40">Error al cargar proyectos</option>';
            proyectSelect.disabled = false;
        });
});

proyectSelect.addEventListener('change', function() {
    loadDeptos(userSelect.value, this.value);
});
@endif
</script>
@endsection
