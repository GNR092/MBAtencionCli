@extends('layouts.admin')
@section('content')
<header class="mb-10 px-2">
    <div class="flex items-baseline gap-4">
        <span class="text-dorado-400 text-sm font-serif italic">|</span>
        <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
            Registro de usuarios<span class="font-light text-dorado"></span><span
                class="text-dorado-400 animate-pulse">_</span>
        </h1>
    </div>
</header>


@if(session('success'))
<!-- Modal -->
<div id="successModal" class="fixed inset-0 bg-white/30 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96 text-center">
        <span class="text-xl font-bold text-green-700 mb-2">¡Éxito!</span>
        <p class="text-gray-700">{{ session('success') }}</p>

        <button onclick="document.getElementById('successModal').remove()"
            class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            Cerrar
        </button>
    </div>
</div>
@endif

{{-- Errores de validación --}}
@if($errors->any())
<div class="bg-red-100 text-red-700 p-3 rounded mb-4">
    <ul class="list-disc pl-5">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif


<div class="flex justify-center">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-carbon-200 w-full max-w-md mx-auto">

        <div class="bg-carbon-900 px-6 py-4 border-b-2 border-dorado">
            <h2 class="text-dorado-400 text-xl font-bold uppercase tracking-widest text-center">
                Registro de Inversionistas
            </h2>
        </div>

        <form id="registroUsuarios" class="p-6 space-y-5" action="{{ route('registroUsuarios.datos') }}" method="POST">
            @csrf

            <div>
                <label for="name" class="block text-sm font-bold text-carbon-900 mb-1">Nombre:</label>
                <input type="text" id="name" name="name"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors"
                    value="{{ old('name') }}" required>
            </div>

            <div>
                <label for="email" class="block text-sm font-bold text-carbon-900 mb-1">Correo Electrónico:</label>
                <input type="email" id="email" name="email"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors"
                    value="{{ old('email') }}" required>
            </div>

            <div class="bg-gray-50 p-3 rounded-lg border border-carbon/30">
                <label for="password"
                    class="block text-xs font-bold text-carbon-900 uppercase tracking-wider mb-1">Contraseña
                    generada:</label>
                <div class="flex items-center gap-2">
                    <input type="text" id="password" name="password"
                        class="block w-full bg-transparent border-none text-dorado-400 font-mono font-bold text-lg focus:ring-0 p-0"
                        value="{{ session('generated_password') }}" readonly>
                </div>
                <small class="text-xs text-gray-500 mt-1 block">Se genera automáticamente al registrar.</small>
            </div>

            <div>
                <label class="block text-sm font-bold text-carbon-900 mb-1">Proyectos</label>
                <select name="proyect[]" id="proyect" multiple required multiselect-hide-x="true"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors custom-scroll h-32">
                    @foreach($proyectos as $proyecto)
                    <option value="{{ $proyecto->id_proyecto }}">{{ $proyecto->nombre_proyecto }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Contenedor para campos dinámicos de proyectos --}}
            <div id="dynamicProjectFields" class="space-y-4"></div>

            <div>
                <label class="block text-sm font-bold text-carbon-900 mb-1">Régimen Fiscal</label>
                <select name="regimenFiscal" id="regimenFiscal"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 bg-white focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors">
                    @foreach($regimenesFiscales as $regimen)
                    <option value="{{ $regimen->id_regimen }}">{{ $regimen->nombre_regimen }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="phone" class="block text-sm font-bold text-carbon-900 mb-1">Número telefónico:</label>
                <div class="flex items-center">
                    <span
                        class="inline-flex items-center px-3 py-2 rounded-l-lg border border-r-0 border-gray-300 bg-gray-100 text-gray-500 text-sm font-bold">
                        +52
                    </span>
                    <input type="tel" id="phone" name="phone"
                        class="block w-full border border-gray-300 rounded-r-lg px-3 py-2 text-carbon-900 focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors"
                        maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                        value="{{ old('phone') }}" required>
                </div>
                <small class="text-xs text-gray-400 mt-1">Formato: 10 dígitos (ejemplo: 9999999999)</small>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full bg-dorado-400 text-white font-bold uppercase tracking-widest py-3 rounded-lg shadow-md hover:bg-dorado/90 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5">
                    Registrar Usuario
                </button>
            </div>
        </form>
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

        function renderDynamicProjectFields() {
            const selectedProjectIds = Array.from(proyectSelect.selectedOptions).map(option => option.value);

            // Mantener datos existentes para no borrarlos al seleccionar nuevos proyectos
            const currentFields = {};

            selectedProjectIds.forEach(projectId => {
                if (!document.getElementById(`project_container_${projectId}`)) {
                    const projectName = projectOptions[projectId] || `Proyecto ${projectId}`;

                    const projectContainer = document.createElement('div');
                    projectContainer.id = `project_container_${projectId}`;
                    projectContainer.className = 'bg-gray-100 p-4 rounded-xl border-2 border-dorado/20 mb-6 space-y-4';

                    projectContainer.innerHTML = `
                    <div class="flex justify-between items-center border-b border-dorado/30 pb-2">
                        <h3 class="text-lg font-bold text-carbon-900 uppercase">${projectName}</h3>
                        <button type="button" onclick="addDepartment('${projectId}')"
                                class="bg-carbon-900 text-dorado-400 text-xs px-3 py-1 rounded-md border border-dorado-400 hover:bg-dorado-400 hover:text-white transition-all">
                            + AGREGAR DEPARTAMENTO
                        </button>
                    </div>
                    <div id="departments_container_${projectId}" class="space-y-4 pt-2">
                        </div>
                `;
                    dynamicProjectFields.appendChild(projectContainer);
                    // Agregar el primer departamento por defecto
                    addDepartment(projectId);
                }
            });

            // Limpiar proyectos que se deseleccionaron
            Array.from(dynamicProjectFields.children).forEach(container => {
                const id = container.id.replace('project_container_', '');
                if (!selectedProjectIds.includes(id)) {
                    container.remove();
                }
            });
        }

        // Función global para que el botón pueda llamarla
        window.addDepartment = function(projectId) {
            const container = document.getElementById(`departments_container_${projectId}`);
            const deptIndex = container.children.length; // Índice para el arreglo

            const deptDiv = document.createElement('div');
            deptDiv.className = 'bg-white p-4 rounded-lg border border-gray-300 shadow-sm relative fade-in-content';
            deptDiv.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <span class="text-xs font-bold text-dorado-400 uppercase tracking-widest">Datos del Departamento</span>
                ${deptIndex > 0 ? `<button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-500 text-xs hover:underline">Eliminar</button>` : ''}
            </div>

            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="block text-sm font-bold text-carbon-900 mb-1">Nombre Depto:</label>
                    <input type="text" name="project_details[${projectId}][${deptIndex}][nombre_depto]"
                           class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 outline-none" required>
                </div>

                <div class="flex items-center gap-2 my-2">
                    <input type="checkbox" id="predial_${projectId}_${deptIndex}"
                           name="project_details[${projectId}][${deptIndex}][cuenta_predial]"
                           onchange="togglePredial(this, '${projectId}', ${deptIndex})"
                           class="form-checkbox h-4 w-4 text-dorado-400 rounded border-gray-300">
                    <label for="predial_${projectId}_${deptIndex}" class="text-sm font-bold text-carbon-900">¿Cuenta Predial?</label>
                </div>

                <div id="div_predial_${projectId}_${deptIndex}" class="hidden">
                    <label class="block text-sm font-bold text-carbon-900 mb-1">Número de Cuenta:</label>
                    <input type="text" name="project_details[${projectId}][${deptIndex}][cuenta_numero]"
                           class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-bold text-carbon-900 mb-1">Importe:</label>
                    <input type="number" step="0.01" name="project_details[${projectId}][${deptIndex}][importe]"
                           class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 outline-none" required>
                </div>
            </div>
        `;
            container.appendChild(deptDiv);
        };

        window.togglePredial = function(checkbox, projectId, index) {
            const div = document.getElementById(`div_predial_${projectId}_${index}`);
            div.classList.toggle('hidden', !checkbox.checked);
        };

        proyectSelect.addEventListener('change', renderDynamicProjectFields);
    });
</script>

@endsection
