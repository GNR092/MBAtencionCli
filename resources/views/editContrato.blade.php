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
                <h1 class="page-title">
                    Editar Contrato
                </h1>
            </div>
        </header>

        <div class="bg-[#112134]/60 backdrop-blur-md rounded-2xl border border-[#d8c495]/20 shadow-2xl overflow-hidden">
            @if(session('error'))
            <div class="mx-8 mt-6 rounded-lg border border-red-400/30 bg-red-900/20 px-4 py-3 text-sm text-red-200">
                {{ session('error') }}
            </div>
            @endif

            @if($contratoBloqueado)
            <div class="mx-8 mt-6 rounded-lg border border-amber-400/30 bg-amber-900/20 px-4 py-3 text-sm text-amber-100">
                Este contrato tiene movimientos de pago. No se puede editar ni mover de proyecto. Solo puedes renovarlo con un nuevo PDF.
            </div>
            @endif

            <div class="px-8 py-6 border-b border-[#d8c495]/20">
                <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">
                    Información del Contrato
                </h2>
                <p class="text-[10px] text-[#d8c495]/50 uppercase tracking-[0.3em] mt-1">
                    Modifica los datos del contrato
                </p>
            </div>

            <div class="px-8 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 rounded-lg border border-[#d8c495]/20 bg-white/5 p-4">
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.2em] text-[#d8c495]/60">Proyecto ligado</p>
                        <p class="text-sm text-white mt-1">{{ $proyectoActual->nombre_proyecto ?? 'Sin proyecto ligado' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.2em] text-[#d8c495]/60">Departamento ligado</p>
                        <p class="text-sm text-white mt-1">{{ $contractToEdit->userDepto->nombre ?? 'Sin departamento ligado' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.2em] text-[#d8c495]/60">Predial ligado</p>
                        <p class="text-sm text-white mt-1">{{ $contractToEdit->userDepto->predial ?? 'Sin predial ligado' }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.contratos.actualizar', $contractToEdit->id) }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                @method('PUT')

                <!-- Usuario -->
                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Inversionista
                    </label>
                    <select name="user_id" id="user_id" {{ $contratoBloqueado ? 'disabled' : '' }}
                        class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]">
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ $contractToEdit->user_id == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Proyecto -->
                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Proyecto
                    </label>
                    <select name="proyect" id="proyect" {{ $contratoBloqueado ? 'disabled' : '' }}
                        class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]" required>
                        @foreach($proyectos as $proyecto)
                        <option value="{{ $proyecto->id_proyecto }}" {{ $currentProyectoId == $proyecto->id_proyecto ? 'selected' : '' }}>
                            {{ $proyecto->nombre_proyecto }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Departamento ligado
                    </label>
                    <select name="id_user_depto" id="id_user_depto" {{ $contratoBloqueado ? 'disabled' : '' }}
                        class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]"
                        >
                        @forelse($departamentosProyectoActual as $depto)
                        <option value="{{ $depto->id_user_depto }}" {{ (int) $contractToEdit->id_user_depto === (int) $depto->id_user_depto ? 'selected' : '' }}>
                            {{ $depto->nombre }} @if(!empty($depto->predial)) - Predial: {{ $depto->predial }} @endif
                        </option>
                        @empty
                        <option value="">Sin departamentos disponibles para este proyecto</option>
                        @endforelse
                    </select>
                    <p id="id_user_depto_help" class="text-white/50 text-xs mt-2"></p>
                    @if(!$contractToEdit->id_user_depto)
                    <p class="text-amber-300/90 text-xs mt-2">Este contrato no tiene departamento ligado. Selecciona uno para corregir la vinculación.</p>
                    @endif

                    @if(!$contratoBloqueado)
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="nuevo_depto_nombre" placeholder="Si no aparece, escribe nuevo departamento"
                            class="w-full bg-[#0d1f30] border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]">
                        <input type="text" name="nuevo_depto_predial" placeholder="Predial opcional del nuevo departamento"
                            class="w-full bg-[#0d1f30] border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]">
                    </div>
                    @endif
                </div>

                <!-- Fechas (solo lectura) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Fecha de Inicio
                        </label>
                        <div class="relative">
                            <input type="text" 
                                value="{{ \Carbon\Carbon::parse($contractToEdit->fecha_inicio)->format('d/m/Y') }}"
                                disabled
                                class="w-full bg-white/5 border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white/70 cursor-not-allowed">
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
                            <input type="text" 
                                value="{{ $contractToEdit->fecha_terminacion ? \Carbon\Carbon::parse($contractToEdit->fecha_terminacion)->format('d/m/Y') : 'No definida' }}"
                                disabled
                                class="w-full bg-white/5 border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white/70 cursor-not-allowed">
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
                            value="{{ number_format($contractToEdit->importe_bruto_renta, 2) }}"
                            {{ $contratoBloqueado ? 'disabled' : '' }}
                            class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg pl-10 pr-4 py-3 text-xl text-white focus:outline-none focus:border-[#d8c495] focus:ring-1 focus:ring-[#d8c495]/30 transition-all"
                            required>
                    </div>
                </div>

                <!-- Estado del contrato -->
                <div class="mb-8">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Estado del Contrato
                    </label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="activo" name="activo" value="1"
                                {{ $contractToEdit->estado === 'activo' ? 'checked' : '' }}
                                {{ $contratoBloqueado ? 'disabled' : '' }}
                                class="w-5 h-5 rounded border-[#d8c495]/30 bg-white/10 text-[#d8c495] focus:ring-[#d8c495] focus:ring-offset-0">
                            <span class="text-white/80">Activo</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="inactivo" name="inactivo" value="1"
                                {{ $contractToEdit->estado === 'inactivo' ? 'checked' : '' }}
                                {{ $contratoBloqueado ? 'disabled' : '' }}
                                class="w-5 h-5 rounded border-[#d8c495]/30 bg-white/10 text-[#d8c495] focus:ring-[#d8c495] focus:ring-offset-0">
                            <span class="text-white/80">Inactivo</span>
                        </label>
                    </div>
                </div>

                <!-- Archivo -->
                <div class="mb-8">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Documento del Contrato (PDF)
                    </label>
                    <div class="bg-white/5 border border-[#d8c495]/20 rounded-lg px-4 py-3 mb-3">
                        <p class="text-white/70 text-sm">
                            <span class="text-[#d8c495]">Archivo actual:</span> {{ $contractToEdit->nombre ?? 'Sin archivo' }}
                        </p>
                    </div>
                    <div class="border-2 border-dashed border-[#d8c495]/30 rounded-lg p-6 text-center hover:border-[#d8c495]/50 transition-colors">
                        <input type="file" name="archivo" accept=".pdf" 
                            class="hidden" id="archivoInput"
                            {{ $contratoBloqueado ? 'disabled' : '' }}
                            onchange="document.getElementById('archivoLabel').textContent = this.files[0]?.name || 'Seleccionar archivo'">
                        <label for="archivoInput" class="cursor-pointer">
                            <svg class="w-10 h-10 mx-auto text-[#d8c495]/50 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span id="archivoLabel" class="text-white/70">Cambiar archivo (opcional)</span>
                            <p class="text-[10px] text-white/40 mt-1">PDF máximo 2MB</p>
                        </label>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-4 pt-4">
                    <button type="submit"
                        {{ $contratoBloqueado ? 'disabled' : '' }}
                        class="bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg transition-all flex-1">
                        Guardar Cambios
                    </button>
                    <a href="{{ route('admin.contratos.index') }}"
                        class="border border-[#d8c495]/30 text-[#d8c495]/60 text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg hover:border-[#d8c495] hover:text-[#d8c495] transition-all text-center">
                        Cancelar
                    </a>
                </div>
            </form>

            @if($contratoBloqueado)
            <div class="border-t border-[#d8c495]/20 p-8">
                <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest mb-2">Renovar Contrato</h2>
                <p class="text-white/60 text-sm mb-6">La renovación crea un contrato nuevo, mantiene el histórico del contrato actual y requiere un PDF nuevo.</p>

                <form action="{{ route('admin.contratos.renovar', $contractToEdit->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $contractToEdit->user_id }}">

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">Proyecto nuevo</label>
                        <select name="proyect" id="proyect_renovar" class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]" required>
                            @foreach($proyectos as $proyecto)
                            <option value="{{ $proyecto->id_proyecto }}" {{ $currentProyectoId == $proyecto->id_proyecto ? 'selected' : '' }}>
                                {{ $proyecto->nombre_proyecto }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">Departamento nuevo</label>
                        <select name="id_user_depto" id="id_user_depto_renovar" class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]">
                            @forelse($departamentosProyectoActual as $depto)
                            <option value="{{ $depto->id_user_depto }}" {{ (int) $contractToEdit->id_user_depto === (int) $depto->id_user_depto ? 'selected' : '' }}>
                                {{ $depto->nombre }} @if(!empty($depto->predial)) - Predial: {{ $depto->predial }} @endif
                            </option>
                            @empty
                            <option value="">Sin departamentos disponibles</option>
                            @endforelse
                        </select>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" name="nuevo_depto_nombre" placeholder="Si no aparece, escribe nuevo departamento"
                                class="w-full bg-[#0d1f30] border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]">
                            <input type="text" name="nuevo_depto_predial" placeholder="Predial opcional del nuevo departamento"
                                class="w-full bg-[#0d1f30] border border-[#d8c495]/20 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">Nueva fecha de inicio</label>
                            <input type="date" name="fecha_inicio" class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">Nueva fecha de terminación</label>
                            <input type="date" name="fecha_terminacion" class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">Nuevo importe bruto de renta</label>
                        <input type="text" name="importe_bruto_renta" value="{{ number_format($contractToEdit->importe_bruto_renta, 2) }}" class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">PDF nuevo del contrato <span class="text-red-400">*</span></label>
                        <input type="file" name="archivo" accept=".pdf" class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white" required>
                    </div>

                    <button type="submit" class="w-full bg-[#d8c495] hover:bg-[#b8a374] text-[#112134] text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg transition-all">
                        Renovar Contrato
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

<input type="hidden" id="contract_user_id_for_deptos" value="{{ (int) $contractToEdit->user_id }}">
<input type="hidden" id="contract_depto_id_for_deptos" value="{{ (int) ($contractToEdit->id_user_depto ?? 0) }}">

<script>
document.getElementById('activo')?.addEventListener('change', function() {
    if (this.checked) document.getElementById('inactivo').checked = false;
});
document.getElementById('inactivo')?.addEventListener('change', function() {
    if (this.checked) document.getElementById('activo').checked = false;
});

function renderDeptos(selectId, deptos, selectedId = null) {
    const select = document.getElementById(selectId);
    const help = document.getElementById('id_user_depto_help');
    if (!select) return;

    select.innerHTML = '';

    const keepOption = document.createElement('option');
    keepOption.value = '';
    keepOption.textContent = 'Mantener departamento actual';
    select.appendChild(keepOption);

    if (!Array.isArray(deptos) || deptos.length === 0) {
        select.disabled = true;
        if (help && selectId === 'id_user_depto') {
            help.textContent = 'El proyecto seleccionado no tiene departamentos configurados. Se conservará el departamento actual; si no existe, el contrato quedará sin departamento hasta que lo captures.';
        }
        return;
    }

    select.disabled = false;
    if (help && selectId === 'id_user_depto') {
        help.textContent = '';
    }

    deptos.forEach((d) => {
        const opt = document.createElement('option');
        opt.value = d.id_user_depto;
        opt.textContent = d.nombre + (d.predial ? ' - Predial: ' + d.predial : '');
        if (selectedId && Number(selectedId) === Number(d.id_user_depto)) {
            opt.selected = true;
        }
        select.appendChild(opt);
    });

    if (!selectedId) {
        keepOption.selected = true;
    }
}

async function cargarDeptos(userId, proyectoId, selectId, selectedId = null) {
    if (!userId || !proyectoId) return;
    try {
        const resp = await fetch(`/api/users/${userId}/projects/${proyectoId}/departments`);
        if (!resp.ok) {
            renderDeptos(selectId, []);
            return;
        }
        const deptos = await resp.json();
        renderDeptos(selectId, deptos, selectedId);
    } catch (_) {
        renderDeptos(selectId, []);
    }
}

const userIdEdit = Number(document.getElementById('contract_user_id_for_deptos')?.value || 0);
const currentDeptoId = Number(document.getElementById('contract_depto_id_for_deptos')?.value || 0);

document.getElementById('proyect')?.addEventListener('change', function() {
    cargarDeptos(userIdEdit, this.value, 'id_user_depto', null);
});

document.getElementById('proyect_renovar')?.addEventListener('change', function() {
    cargarDeptos(userIdEdit, this.value, 'id_user_depto_renovar', null);
});

if (document.getElementById('proyect')) {
    cargarDeptos(userIdEdit, document.getElementById('proyect').value, 'id_user_depto', currentDeptoId);
}

if (document.getElementById('proyect_renovar')) {
    cargarDeptos(userIdEdit, document.getElementById('proyect_renovar').value, 'id_user_depto_renovar', currentDeptoId);
}
</script>
@endsection
