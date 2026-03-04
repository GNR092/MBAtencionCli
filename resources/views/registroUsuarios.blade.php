@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <div class="flex items-baseline gap-4">
                <span class="text-dorado-400 text-sm font-serif italic">|</span>
                <h1 class="text-white text-5xl md:text-7xl font-extralight tracking-[-0.02em] leading-none uppercase">
                    Nuevos Usuarios
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
                    
                    @if(session('generated_password'))
                    <div class="bg-white/5 border border-[#d8c495]/20 rounded-xl p-4 mb-4">
                        <span class="block text-xs font-bold text-[#d8c495]/70 uppercase tracking-widest mb-2">Contraseña generada:</span>
                        <span class="text-2xl font-mono font-bold text-[#d8c495] select-all">{{ session('generated_password') }}</span>
                    </div>
                    <p class="text-white/40 text-xs mb-4 italic">Por favor, comparta esta contraseña con el usuario de forma segura.</p>
                    @endif

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
                    Registro de Inversionistas
                </h2>
                <p class="text-[10px] text-[#d8c495]/50 uppercase tracking-[0.3em] mt-1">
                    Complete los datos del nuevo usuario
                </p>
            </div>

            <form id="registroUsuarios" class="p-8 space-y-6" action="{{ route('usuarios.registro.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Nombre Completo
                        </label>
                        <input type="text" id="name" name="name"
                            class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                            value="{{ old('name') }}" required>
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Correo Electrónico
                        </label>
                        <input type="email" id="email" name="email"
                            class="w-full bg-white/5 border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors"
                            value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="bg-white/5 border border-[#d8c495]/20 rounded-xl p-5">
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Contraseña Generada
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="text" id="password" name="password"
                            class="flex-1 bg-transparent border-none text-[#d8c495] font-mono font-bold text-xl focus:ring-0 p-0"
                            value="{{ session('generated_password') }}" readonly>
                    </div>
                    <p class="text-[10px] text-white/40 mt-2">Se genera automáticamente al registrar.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                        Proyectos
                    </label>
                    <select name="proyect[]" id="proyect" multiple required
                        class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors h-32">
                        @foreach($proyectos as $proyecto)
                        <option value="{{ $proyecto->id_proyecto }}">{{ $proyecto->nombre_proyecto }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="dynamicProjectFields" class="space-y-4"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#d8c495]/70 mb-3">
                            Régimen Fiscal
                        </label>
                        <select name="regimenFiscal" id="regimenFiscal"
                            class="w-full bg-[#0d1f30] border border-[#d8c495]/30 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-[#d8c495] transition-colors">
                            @foreach($regimenesFiscales as $regimen)
                            <option value="{{ $regimen->id_regimen }}">{{ $regimen->nombre_regimen }}</option>
                            @endforeach
                        </select>
                    </div>

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
                                value="{{ old('phone') }}" required>
                        </div>
                        <p class="text-[10px] text-white/40 mt-2">10 dígitos</p>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-[#d8c495] text-[#112134] font-bold uppercase tracking-widest py-4 rounded-xl shadow-lg hover:bg-[#b8a374] transition-all duration-300 hover:shadow-xl">
                        Registrar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/multiselect.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const proyectSelect = document.getElementById('proyect');
    const dynamicProjectFields = document.getElementById('dynamicProjectFields');

    if (typeof multiselect === 'function') {
        multiselect(proyectSelect);
    }

    const projectOptions = @json($proyectos->pluck('nombre_proyecto', 'id_proyecto'));

    window.renderDynamicProjectFields = function() {
        const selectedProjectIds = Array.from(proyectSelect.selectedOptions).map(option => option.value);

        selectedProjectIds.forEach(projectId => {
            if (!document.getElementById(`project_container_${projectId}`)) {
                const projectName = projectOptions[projectId] || `Proyecto ${projectId}`;

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
                    <div id="departments_container_${projectId}" class="space-y-4 pt-2">
                    </div>
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
        deptDiv.className = 'bg-[#112134] p-4 rounded-lg border border-white/10 relative';
        deptDiv.innerHTML = `
            <div class="flex justify-between items-center mb-4">
                <span class="text-xs font-bold text-[#d8c495] uppercase tracking-wider">Departamento ${deptIndex + 1}</span>
                ${deptIndex > 0 ? `<button type="button" onclick="this.closest('[class*=\'bg-\']').remove()" class="text-red-400 text-xs hover:underline">Eliminar</button>` : ''}
            </div>

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

            <div class="flex items-center gap-3 mt-4">
                <input type="checkbox" id="predial_${projectId}_${deptIndex}"
                    name="project_details[${projectId}][${deptIndex}][cuenta_predial]"
                    onchange="togglePredial(this, '${projectId}', ${deptIndex})"
                    class="w-4 h-4 rounded border-white/20 bg-white/5 text-[#d8c495] focus:ring-[#d8c495]">
                <label for="predial_${projectId}_${deptIndex}" class="text-sm text-white/70">¿Cuenta Predial?</label>
            </div>

            <div id="predial_${projectId}_${deptIndex}" class="hidden mt-3">
                <label class="block text-xs font-bold text-white/70 mb-2">Número de Cuenta:</label>
                <input type="text" name="project_details[${projectId}][${deptIndex}][cuenta_numero]"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-[#d8c495] outline-none">
            </div>
        `;
        container.appendChild(deptDiv);
    };

    window.togglePredial = function(checkbox, projectId, index) {
        const div = document.getElementById(`predial_${projectId}_${index}`);
        div.classList.toggle('hidden', !checkbox.checked);
    };

    proyectSelect.addEventListener('change', window.renderDynamicProjectFields);
});
</script>
@endsection
