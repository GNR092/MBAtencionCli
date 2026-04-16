@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-6xl mx-auto space-y-6">
        <header class="px-1">
            <div class="flex items-baseline gap-4">
                <span class="text-dorado-400 text-sm font-serif italic">|</span>
                <h1 class="text-white text-4xl md:text-6xl font-extralight tracking-[-0.02em] leading-none uppercase">
                    Registro de Inversionista
                </h1>
            </div>
            <p class="mt-3 text-xs md:text-sm text-white/55 tracking-wide">
                Alta integral con proyectos, departamentos y contrato por unidad.
            </p>
        </header>

        @if(session('success'))
        <div id="successModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-[#112134] border border-[#d8c495]/30 rounded-2xl shadow-2xl p-8 max-w-md w-full">
                <div class="text-center">
                    <div class="w-16 h-16 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-400/20">
                        <svg class="w-8 h-8 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-[#d8c495] text-xl font-bold uppercase tracking-widest mb-2">Usuario creado</h3>
                    <p class="text-white/70 mb-4">{{ session('success') }}</p>

                    @if(session('generated_password'))
                    <div class="bg-black/20 border border-[#d8c495]/20 rounded-xl p-4 mb-4">
                        <span class="block text-xs font-bold text-[#d8c495]/70 uppercase tracking-widest mb-2">Contrasena generada</span>
                        <span class="text-2xl font-mono font-bold text-[#d8c495] select-all">{{ session('generated_password') }}</span>
                    </div>
                    <p class="text-white/40 text-xs mb-4 italic">Comparte esta contrasena de forma segura con el usuario.</p>
                    @endif

                    <button type="button" onclick="document.getElementById('successModal').remove()"
                        class="w-full bg-[#d8c495] text-[#112134] px-6 py-3 rounded-xl font-bold hover:bg-[#c5b07f] transition-colors">
                        Entendido
                    </button>
                </div>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-500/10 border border-red-400/30 rounded-xl p-4">
            <p class="text-xs uppercase tracking-widest text-red-300 mb-2 font-bold">Errores de validacion</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                <li class="text-red-200 text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
            <aside class="xl:col-span-4 bg-[#112134]/55 backdrop-blur-md border border-[#d8c495]/15 rounded-2xl p-5 space-y-5 xl:sticky xl:top-6">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.35em] text-[#d8c495]/70 font-bold">Guia del proceso</p>
                    <h2 class="text-white text-lg font-semibold mt-2">Checklist de alta</h2>
                </div>

                <ol class="space-y-3 text-sm">
                    <li class="flex gap-3 items-start">
                        <span class="mt-0.5 w-6 h-6 rounded-full bg-[#d8c495]/15 text-[#d8c495] text-xs font-bold flex items-center justify-center">1</span>
                        <div>
                            <p class="text-white/85 font-medium">Datos del inversionista</p>
                            <p class="text-white/50 text-xs">Nombre, contacto y regimen fiscal.</p>
                        </div>
                    </li>
                    <li class="flex gap-3 items-start">
                        <span class="mt-0.5 w-6 h-6 rounded-full bg-[#d8c495]/15 text-[#d8c495] text-xs font-bold flex items-center justify-center">2</span>
                        <div>
                            <p class="text-white/85 font-medium">Asigna proyectos</p>
                            <p class="text-white/50 text-xs">Selecciona uno o varios proyectos.</p>
                        </div>
                    </li>
                    <li class="flex gap-3 items-start">
                        <span class="mt-0.5 w-6 h-6 rounded-full bg-[#d8c495]/15 text-[#d8c495] text-xs font-bold flex items-center justify-center">3</span>
                        <div>
                            <p class="text-white/85 font-medium">Define departamentos</p>
                            <p class="text-white/50 text-xs">Importe, tipo, predial y contrato PDF por unidad.</p>
                        </div>
                    </li>
                </ol>

                <div class="bg-black/20 border border-white/10 rounded-xl p-4 space-y-2">
                    <p class="text-[10px] uppercase tracking-[0.22em] text-[#d8c495]/70 font-bold">Resumen dinamico</p>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-white/60">Proyectos seleccionados</span>
                        <span id="projectCounter" class="text-[#d8c495] font-mono font-bold">0</span>
                    </div>
                    <p class="text-[11px] text-white/45">La contrasena final se genera automaticamente al guardar.</p>
                </div>
            </aside>

            <div class="xl:col-span-8 bg-[#112134]/60 backdrop-blur-md rounded-2xl border border-[#d8c495]/20 shadow-2xl overflow-hidden">
                <div class="px-8 py-6 border-b border-[#d8c495]/20">
                    <h2 class="text-[#d8c495] text-lg font-bold uppercase tracking-widest">Formulario de alta</h2>
                    <p class="text-[10px] text-[#d8c495]/55 uppercase tracking-[0.28em] mt-1">Completa cada bloque en orden</p>
                </div>

                <form id="registroUsuarios" class="p-6 md:p-8 space-y-7" action="{{ route('usuarios.registro.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <section class="space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full border border-[#d8c495]/40 text-[#d8c495] text-xs font-bold flex items-center justify-center">1</span>
                            <h3 class="text-sm font-bold uppercase tracking-[0.18em] text-[#d8c495]/80">Datos personales</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">Nombre Completo</label>
                                <input type="text" id="name" name="name"
                                    class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                                    value="{{ old('name') }}" required>
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">Correo Electronico</label>
                                <input type="email" id="email" name="email"
                                    class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                                    value="{{ old('email') }}" required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">Regimen Fiscal</label>
                                <select name="regimenFiscal" id="regimenFiscal"
                                    class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                                    required>
                                    <option value="" class="text-black">Seleccione regimen</option>
                                    @foreach($regimenesFiscales as $regimen)
                                    <option value="{{ $regimen->id_regimen }}" @selected((string) old('regimenFiscal') === (string) $regimen->id_regimen)>
                                        {{ $regimen->nombre_regimen }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="phone" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">Telefono</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-4 py-3 rounded-l-lg border border-r-0 border-white/10 bg-black/20 text-white/50 text-sm font-bold">+52</span>
                                    <input type="tel" id="phone" name="phone"
                                        class="flex-1 bg-black/20 border border-white/10 rounded-r-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                                        maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                        value="{{ old('phone') }}" required>
                                </div>
                                <p class="text-[10px] text-white/45 mt-1">10 digitos sin espacios.</p>
                            </div>

                            <div>
                                <label for="fecha_nacimiento" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">Fecha de Nacimiento</label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                                    class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                                    value="{{ old('fecha_nacimiento') }}">
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4 border-t border-white/10 pt-6">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full border border-[#d8c495]/40 text-[#d8c495] text-xs font-bold flex items-center justify-center">2</span>
                            <h3 class="text-sm font-bold uppercase tracking-[0.18em] text-[#d8c495]/80">Proyectos y metodo de pago</h3>
                        </div>

                        @php
                            $selectedProjects = collect(old('proyect', []))->map(fn ($id) => (string) $id)->toArray();
                        @endphp

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">Proyectos</label>
                            <select name="proyect[]" id="proyect" multiple required
                                class="w-full bg-[#0d1f30] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors h-36">
                                @foreach($proyectos as $proyecto)
                                <option value="{{ $proyecto->id_proyecto }}" {{ in_array((string) $proyecto->id_proyecto, $selectedProjects, true) ? 'selected' : '' }}>
                                    {{ $proyecto->nombre_proyecto }}@if($proyecto->razonSocial) - {{ $proyecto->razonSocial->nombre_razon_social }}@endif
                                </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-white/45 mt-1">Para seleccion multiple usa Ctrl/Cmd + clic.</p>
                        </div>
                    </section>

                    <section class="space-y-4 border-t border-white/10 pt-6">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full border border-[#d8c495]/40 text-[#d8c495] text-xs font-bold flex items-center justify-center">3</span>
                            <h3 class="text-sm font-bold uppercase tracking-[0.18em] text-[#d8c495]/80">Departamentos y contratos</h3>
                        </div>

                        <div id="dynamicProjectFields" class="space-y-4"></div>
                    </section>

                    <section class="border-t border-white/10 pt-6 space-y-4">
                        <div class="bg-black/20 border border-white/10 rounded-xl p-4">
                            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-2">Contrasena generada</label>
                            <input type="text" id="password"
                                class="w-full bg-transparent border-none text-[#d8c495] font-mono font-bold text-xl focus:ring-0 p-0"
                                value="{{ session('generated_password') }}" readonly>
                            <p class="text-[10px] text-white/45 mt-1">Se muestra despues de registrar al usuario.</p>
                        </div>

                        <button type="submit"
                            class="w-full bg-[#d8c495] text-[#112134] font-bold uppercase tracking-widest py-4 rounded-xl shadow-lg hover:bg-[#c5b07f] transition-all duration-300">
                            Registrar Usuario
                        </button>
                    </section>
                </form>
            </div>
        </div>
    </div>
</div>

@php
    $projectOptionsJson = $proyectos->map(function ($p) {
        return [
            'id' => $p->id_proyecto,
            'nombre' => $p->nombre_proyecto,
            'razon_social' => $p->razonSocial ? $p->razonSocial->nombre_razon_social : null,
        ];
    })->keyBy('id');
    $oldProjectPaymentMethodsJson = old('project_payment_methods', []);
@endphp

<script src="{{ asset('js/multiselect.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const proyectSelect = document.getElementById('proyect');
    const dynamicProjectFields = document.getElementById('dynamicProjectFields');
    const projectCounter = document.getElementById('projectCounter');

    if (typeof multiselect === 'function') {
        multiselect(proyectSelect);
    }

    const projectOptions = JSON.parse('{!! json_encode($projectOptionsJson, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}');
    const oldProjectPaymentMethods = JSON.parse('{!! json_encode($oldProjectPaymentMethodsJson, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}');

    const updateProjectCounter = () => {
        const selectedCount = Array.from(proyectSelect.selectedOptions).length;
        projectCounter.textContent = String(selectedCount);
    };

    window.renderDynamicProjectFields = function() {
        const selectedProjectIds = Array.from(proyectSelect.selectedOptions).map(option => option.value);
        updateProjectCounter();

        selectedProjectIds.forEach(projectId => {
            if (!document.getElementById(`project_container_${projectId}`)) {
                const project = projectOptions[projectId] || { nombre: `Proyecto ${projectId}`, razon_social: null };
                const projectName = project.nombre + (project.razon_social ? ` - ${project.razon_social}` : '');

                const projectContainer = document.createElement('div');
                projectContainer.id = `project_container_${projectId}`;
                projectContainer.className = 'bg-[#0d1f30]/55 rounded-xl border border-[#d8c495]/18 p-5 space-y-4';
                const selectedPaymentMethod = oldProjectPaymentMethods[projectId] || '';

                projectContainer.innerHTML = `
                    <div class="flex justify-between items-center border-b border-[#d8c495]/20 pb-3">
                        <h3 class="text-[#d8c495] font-bold uppercase tracking-wider text-sm">${projectName}</h3>
                        <button type="button" onclick="addDepartment('${projectId}')"
                            class="bg-[#d8c495]/10 text-[#d8c495] text-xs px-4 py-2 rounded-lg border border-[#d8c495]/30 hover:bg-[#d8c495] hover:text-[#112134] transition-all font-bold uppercase">
                            + Departamento
                        </button>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-white/70 mb-2 uppercase tracking-wide">Metodo de Pago del Proyecto</label>
                        <select name="project_payment_methods[${projectId}]"
                            class="w-full bg-[#0d1f30] border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none"
                            required>
                            <option value="" ${selectedPaymentMethod === '' ? 'selected' : ''}>-- Seleccione metodo --</option>
                            <option value="efectivo" ${selectedPaymentMethod === 'efectivo' ? 'selected' : ''}>Efectivo</option>
                            <option value="transferencia" ${selectedPaymentMethod === 'transferencia' ? 'selected' : ''}>Transferencia bancaria</option>
                        </select>
                    </div>
                    <div id="departments_container_${projectId}" class="space-y-4 pt-2"></div>
                `;

                dynamicProjectFields.appendChild(projectContainer);
                addDepartment(projectId);
            }
        });

        Array.from(dynamicProjectFields.children).forEach(container => {
            const id = container.id.replace('project_container_', '');
            if (!selectedProjectIds.includes(id)) {
                container.remove();
            }
        });
    };

    window.addDepartment = function(projectId) {
        const container = document.getElementById(`departments_container_${projectId}`);
        const deptIndex = container.children.length;

        const deptDiv = document.createElement('div');
        deptDiv.className = 'dept-item bg-[#112134] p-4 rounded-lg border border-white/10 relative';
        deptDiv.innerHTML = `
            <div class="flex justify-between items-center mb-4">
                <span class="text-xs font-bold text-[#d8c495] uppercase tracking-wider">Departamento ${deptIndex + 1}</span>
                ${deptIndex > 0 ? `<button type="button" onclick="this.closest('.dept-item').remove()" class="text-red-400 text-xs hover:underline">Eliminar</button>` : ''}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-white/70 mb-2">Nombre Departamento:</label>
                    <input type="text" name="project_details[${projectId}][${deptIndex}][nombre_depto]"
                        class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-white/70 mb-2">Importe:</label>
                    <input type="number" step="0.01" name="project_details[${projectId}][${deptIndex}][importe]"
                        class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none" required>
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
                <label for="predial_chk_${projectId}_${deptIndex}" class="text-sm text-white/70">Cuenta Predial</label>
            </div>

            <div id="predial_div_${projectId}_${deptIndex}" class="hidden mt-3">
                <label class="block text-xs font-bold text-white/70 mb-2">Numero de Cuenta:</label>
                <input type="text" name="project_details[${projectId}][${deptIndex}][cuenta_numero]"
                    class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none">
            </div>

            <div class="mt-5 border-t border-white/10 pt-4 space-y-4">
                <p class="text-xs font-bold text-[#d8c495] uppercase tracking-wider">Contrato del departamento</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-white/70 mb-2">Fecha inicio contrato:</label>
                        <input type="date" name="project_details[${projectId}][${deptIndex}][fecha_inicio_contrato]"
                            class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-white/70 mb-2">Fecha terminacion contrato:</label>
                        <input type="date" name="project_details[${projectId}][${deptIndex}][fecha_terminacion_contrato]"
                            class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-white/70 mb-2">Contrato PDF:</label>
                    <input type="file" name="project_details[${projectId}][${deptIndex}][contract_file]" accept=".pdf"
                        class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none file:mr-3 file:rounded file:border-0 file:bg-[#d8c495] file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-[#112134]"
                        required>
                </div>
            </div>
        `;

        container.appendChild(deptDiv);
    };

    window.togglePredial = function(checkbox, projectId, index) {
        const div = document.getElementById(`predial_div_${projectId}_${index}`);
        div.classList.toggle('hidden', !checkbox.checked);
    };

    proyectSelect.addEventListener('change', window.renderDynamicProjectFields);
    window.renderDynamicProjectFields();
    updateProjectCounter();
});
</script>
@endsection
