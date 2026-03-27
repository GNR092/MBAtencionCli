@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <div class="flex items-baseline gap-4">
                <span class="text-dorado-400 text-sm font-serif italic">|</span>
                <h1 class="text-white text-5xl md:text-7xl font-extralight tracking-[-0.02em] leading-none uppercase">
                    Editar Usuario
                </h1>
            </div>
        </header>

        @if(session('success'))
        <div id="successModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-[#112134] border border-[#d8c495]/30 rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-[#d8c495] text-xl font-bold uppercase tracking-widest mb-2">¡Éxito!</h3>
                    <p class="text-white/70 mb-4">{{ session('success') }}</p>
                    <button onclick="document.getElementById('successModal').remove()"
                        class="w-full bg-[#d8c495] text-[#112134] px-6 py-3 rounded-xl font-bold hover:bg-[#b8a374] transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-500/10 border border-red-400/30 rounded-xl p-4 mb-6">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                <li class="text-red-300 text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-[#112134]/60 backdrop-blur-md rounded-2xl border border-[#d8c495]/20 shadow-2xl overflow-hidden">
            <div class="px-8 py-6 border-b border-[#d8c495]/20">
                <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">
                    Datos del Inversionista
                </h2>
                <p class="text-[10px] text-[#d8c495]/50 uppercase tracking-[0.3em] mt-1">
                    Modifique los datos del usuario
                </p>
            </div>


            <form id="editUsuario" class="p-8 space-y-6" action="{{ route('usuarios.actualizar') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $userToEdit->id }}">

                {{-- Nombre y Email --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Nombre Completo
                        </label>
                        <input type="text" id="name" name="name"
                            class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                            value="{{ old('name', $userToEdit->name) }}" required>
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Correo Electrónico
                        </label>
                        <input type="email" id="email" name="email"
                            class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                            value="{{ old('email', $userToEdit->email) }}" required>
                    </div>
                </div>

                {{-- Teléfono y Fecha de Nacimiento --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="phone" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Teléfono
                        </label>
                        <div class="flex">
                            <span class="inline-flex items-center px-4 py-3 rounded-l-lg border border-r-0 border-[#d8c495]/30 bg-white/5 text-white/50 text-sm font-bold">
                                +52
                            </span>
                            <input type="tel" id="phone" name="phone"
                                class="flex-1 bg-white/5 border border-[#d8c495]/30 rounded-r-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                                maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                value="{{ old('phone', preg_replace('/^52/', '', $userToEdit->phone)) }}" required>
                        </div>
                        <p class="text-[10px] text-white/40 mt-2">10 dígitos</p>
                    </div>

                    <div>
                        <label for="fecha_nacimiento" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Fecha de Nacimiento
                        </label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                            class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                            value="{{ old('fecha_nacimiento', $userToEdit->fecha_nacimiento ? \Carbon\Carbon::parse($userToEdit->fecha_nacimiento)->format('Y-m-d') : '') }}">
                    </div>
                </div>

                {{-- Régimen Fiscal y Método de Pago --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="regimenFiscal" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Régimen Fiscal
                        </label>
                        <select name="regimenFiscal" id="regimenFiscal"
                            class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors">
                            @foreach($regimenesFiscales as $regimen)
                            <option value="{{ $regimen->id_regimen }}" {{ old('regimenFiscal', $userToEdit->id_regimen) == $regimen->id_regimen ? 'selected' : '' }}>
                                {{ $regimen->nombre_regimen }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="metodo_pago" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Método de Pago
                        </label>
                        <select id="metodo_pago" name="metodo_pago"
                            class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors">
                            <option value="">Sin especificar</option>
                            <option value="efectivo" {{ old('metodo_pago', $userToEdit->metodo_pago) == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="transferencia" {{ old('metodo_pago', $userToEdit->metodo_pago) == 'transferencia' ? 'selected' : '' }}>Transferencia bancaria</option>
                        </select>
                    </div>
                </div>

                {{-- Contraseña --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Nueva Contraseña <span class="text-white/30 normal-case">(opcional)</span>
                        </label>
                        <input type="password" id="password" name="password"
                            class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                            autocomplete="new-password">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Confirmar Contraseña <span class="text-white/30 normal-case">(opcional)</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                            autocomplete="new-password">
                    </div>
                </div>

                {{-- Proyectos --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Proyectos
                    </label>
                    <select name="proyect[]" id="proyect" multiple
                        class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors h-32">
                        @foreach($proyectos as $proyecto)
                            <option value="{{ $proyecto->id_proyecto }}"
                                {{ in_array((string)$proyecto->id_proyecto, $selectedProjectIds) ? 'selected' : '' }}>
                                {{ $proyecto->nombre_proyecto }}@if($proyecto->razonSocial) - {{ $proyecto->razonSocial->nombre_razon_social }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Contenedores dinámicos de departamentos --}}
                <div id="dynamicProjectFields" class="space-y-4"></div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-[#d8c495] text-[#112134] font-bold uppercase tracking-widest py-4 rounded-xl shadow-lg hover:bg-[#b8a374] transition-all duration-300 hover:shadow-xl">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/multiselect.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const proyectSelect = document.getElementById('proyect');
    const dynamicProjectFields = document.getElementById('dynamicProjectFields');

    const projectOptions = @json($proyectos->map(fn($p) => [
        'id'          => $p->id_proyecto,
        'nombre'      => $p->nombre_proyecto,
        'razon_social'=> $p->razonSocial?->nombre_razon_social,
    ])->keyBy('id'));

    const existingProjectsData = @json($existingProjectsData);
    const initialSelectedIds   = @json($selectedProjectIds);

    // ─── Crea el contenedor de un proyecto (sin departamentos) ───────────
    function renderProjectContainer(projectId) {
        if (document.getElementById(`project_container_${projectId}`)) return;

        const project = projectOptions[projectId] || { nombre: `Proyecto ${projectId}`, razon_social: null };
        const projectName = project.nombre + (project.razon_social ? ` - ${project.razon_social}` : '');

        const projectContainer = document.createElement('div');
        projectContainer.id = `project_container_${projectId}`;
        projectContainer.className = 'bg-[#0d1f30]/50 rounded-xl border border-[#d8c495]/20 p-5 space-y-4';
        projectContainer.innerHTML = `
            <div class="flex justify-between items-center border-b border-[#d8c495]/20 pb-3">
                <h3 class="text-[#d8c495] font-bold uppercase tracking-wider">${projectName}</h3>
                <button type="button" onclick="addDepartment('${projectId}')"
                    class="bg-[#d8c495]/10 text-[#d8c495] text-xs px-4 py-2 rounded-lg border border-[#d8c495]/30 hover:bg-[#d8c495] hover:text-[#112134] transition-all font-bold uppercase">
                    + Departamento
                </button>
            </div>
            <div id="departments_container_${projectId}" class="space-y-4 pt-2"></div>
        `;
        dynamicProjectFields.appendChild(projectContainer);
    }

    // ─── Sincroniza contenedores con la selección actual ────────────────
    window.renderDynamicProjectFields = function () {
        const currentIds = Array.from(proyectSelect.selectedOptions).map(o => o.value);

        // Agregar nuevos proyectos con un departamento vacío
        currentIds.forEach(id => {
            if (!document.getElementById(`project_container_${id}`)) {
                renderProjectContainer(id);
                addDepartment(id);
            }
        });

        // Eliminar proyectos desmarcados
        Array.from(dynamicProjectFields.children).forEach(container => {
            const id = container.id.replace('project_container_', '');
            if (!currentIds.includes(id)) container.remove();
        });
    };

    // ─── Agrega un departamento (vacío o pre-rellenado) ─────────────────
    window.addDepartment = function (projectId, prefill = null) {
        const container = document.getElementById(`departments_container_${projectId}`);
        const deptIndex = container.children.length;
        const project = projectOptions[projectId] || { nombre: `Proyecto ${projectId}`, razon_social: null };
        const projectName = project.nombre + (project.razon_social ? ` - ${project.razon_social}` : '');

        const deptDiv = document.createElement('div');
        deptDiv.className = 'dept-item bg-[#112134] p-4 rounded-lg border border-white/10 relative';
        deptDiv.innerHTML = `
            <div class="flex justify-between items-center mb-4">
                <span class="text-xs font-bold text-[#d8c495] uppercase tracking-wider">Departamento ${deptIndex + 1}</span>
                <button type="button" onclick="this.closest('.dept-item').remove()" class="text-red-400 text-xs hover:underline">Eliminar</button>
            </div>

            <p class="text-[11px] text-white/50 mb-3">Proyecto asignado: <span class="text-[#d8c495]">${projectName}</span></p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-white/70 mb-2">Nombre Departamento:</label>
                    <input type="text" name="project_details[${projectId}][${deptIndex}][nombre_depto]"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-white/70 mb-2">Importe:</label>
                    <input type="number" step="0.01" name="project_details[${projectId}][${deptIndex}][importe]"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none" required>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-xs font-bold text-white/70 mb-2">Tipo:</label>
                <select name="project_details[${projectId}][${deptIndex}][tipo]"
                    class="w-full bg-[#0d1f30] border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none"
                    required>
                    <option value="">-- Seleccione tipo --</option>
                    <option value="Campus">Campus</option>
                    <option value="Condominios">Condominios</option>
                </select>
            </div>

            <div class="flex items-center gap-3 mt-4">
                <input type="checkbox" id="predial_chk_${projectId}_${deptIndex}"
                    name="project_details[${projectId}][${deptIndex}][cuenta_predial]"
                    onchange="togglePredial(this, '${projectId}', ${deptIndex})"
                    class="w-4 h-4 rounded border-white/20 bg-white/5 text-[#d8c495] focus:ring-[#d8c495]">
                <label for="predial_chk_${projectId}_${deptIndex}" class="text-sm text-white/70">¿Cuenta Predial?</label>
            </div>

            <div id="predial_div_${projectId}_${deptIndex}" class="hidden mt-3">
                <label class="block text-xs font-bold text-white/70 mb-2">Número de Cuenta:</label>
                <input type="text" name="project_details[${projectId}][${deptIndex}][cuenta_numero]"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none">
            </div>
        `;
        container.appendChild(deptDiv);

        // Pre-rellenar si se proporcionan datos
        if (prefill) {
            deptDiv.querySelector(`[name="project_details[${projectId}][${deptIndex}][nombre_depto]"]`).value = prefill.nombre || '';
            deptDiv.querySelector(`[name="project_details[${projectId}][${deptIndex}][importe]"]`).value = prefill.importe || '';
            deptDiv.querySelector(`[name="project_details[${projectId}][${deptIndex}][tipo]"]`).value = prefill.tipo || '';

            if (prefill.tiene_predial) {
                const chk = document.getElementById(`predial_chk_${projectId}_${deptIndex}`);
                if (chk) chk.checked = true;
                const predialDiv = document.getElementById(`predial_div_${projectId}_${deptIndex}`);
                if (predialDiv) predialDiv.classList.remove('hidden');
                deptDiv.querySelector(`[name="project_details[${projectId}][${deptIndex}][cuenta_numero]"]`).value = prefill.cuenta_numero || '';
            }
        }
    };

    window.togglePredial = function (checkbox, projectId, index) {
        const div = document.getElementById(`predial_div_${projectId}_${index}`);
        if (div) div.classList.toggle('hidden', !checkbox.checked);
    };

    // ─── Carga inicial: contenedores + departamentos pre-rellenados ──────
    initialSelectedIds.forEach(id => renderProjectContainer(id));

    Object.entries(existingProjectsData).forEach(([projectId, deptos]) => {
        deptos.forEach(dept => addDepartment(projectId, dept));
    });

    // Cambios posteriores del usuario
    proyectSelect.addEventListener('change', window.renderDynamicProjectFields);
});
</script>
@endsection
