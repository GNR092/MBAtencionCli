@extends('layouts.admin')
@section('content')
<header class="mb-10 px-2">
    <div class="flex items-baseline gap-4">
        <span class="text-dorado text-sm font-serif italic">|</span>
        <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
            Registro de usuarios<span class="font-light text-dorado"></span><span
                class="text-dorado animate-pulse">_</span>
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
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-carbon w-full max-w-md mx-auto">

        <div class="bg-gris-carbon px-6 py-4 border-b-2 border-dorado">
            <h2 class="text-dorado text-xl font-bold uppercase tracking-widest text-center">
                Registro de Inversionistas
            </h2>
        </div>

        <form id="registroUsuarios" class="p-6 space-y-5" action="{{ route('registroUsuarios.datos') }}" method="POST">
            @csrf

            <div>
                <label for="name" class="block text-sm font-bold text-gris-carbon mb-1">Nombre:</label>
                <input type="text" id="name" name="name"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-gris-carbon focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors"
                    value="{{ old('name') }}" required>
            </div>

            <div>
                <label for="email" class="block text-sm font-bold text-gris-carbon mb-1">Correo Electrónico:</label>
                <input type="email" id="email" name="email"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-gris-carbon focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors"
                    value="{{ old('email') }}" required>
            </div>

            <div class="bg-gray-50 p-3 rounded-lg border border-carbon/30">
                <label for="password"
                    class="block text-xs font-bold text-gris-carbon uppercase tracking-wider mb-1">Contraseña
                    generada:</label>
                <div class="flex items-center gap-2">
                    <input type="text" id="password" name="password"
                        class="block w-full bg-transparent border-none text-dorado font-mono font-bold text-lg focus:ring-0 p-0"
                        value="{{ session('generated_password') }}" readonly>
                </div>
                <small class="text-xs text-gray-500 mt-1 block">Se genera automáticamente al registrar.</small>
            </div>

            <div>
                <label class="block text-sm font-bold text-gris-carbon mb-1">Proyectos</label>
                <select name="proyect[]" id="proyect" multiple required multiselect-hide-x="true"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-gris-carbon focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors custom-scroll h-32">
                    @foreach($proyectos as $proyecto)
                    <option value="{{ $proyecto->id_proyecto }}">{{ $proyecto->nombre_proyecto }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Contenedor para campos dinámicos de proyectos --}}
            <div id="dynamicProjectFields" class="space-y-4"></div>

            <div>
                <label class="block text-sm font-bold text-gris-carbon mb-1">Régimen Fiscal</label>
                <select name="regimenFiscal" id="regimenFiscal"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-gris-carbon bg-white focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors">
                    @foreach($regimenesFiscales as $regimen)
                    <option value="{{ $regimen->id_regimen }}">{{ $regimen->nombre_regimen }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="phone" class="block text-sm font-bold text-gris-carbon mb-1">Número telefónico:</label>
                <div class="flex items-center">
                    <span
                        class="inline-flex items-center px-3 py-2 rounded-l-lg border border-r-0 border-gray-300 bg-gray-100 text-gray-500 text-sm font-bold">
                        +52
                    </span>
                    <input type="tel" id="phone" name="phone"
                        class="block w-full border border-gray-300 rounded-r-lg px-3 py-2 text-gris-carbon focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors"
                        maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                        value="{{ old('phone') }}" required>
                </div>
                <small class="text-xs text-gray-400 mt-1">Formato: 10 dígitos (ejemplo: 9999999999)</small>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full bg-dorado text-white font-bold uppercase tracking-widest py-3 rounded-lg shadow-md hover:bg-dorado/90 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5">
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

        // Initialize multiselect if not already done by multiselect.js
        if (typeof multiselect === 'function') {
            multiselect(proyectSelect);
        }

        const projectOptions = @json($proyectos->pluck('nombre_proyecto', 'id_proyecto'));
        const oldProjectDetails = @json(old('project_details', []));

        function renderDynamicProjectFields() {
            dynamicProjectFields.innerHTML = ''; // Clear previous fields
            const selectedProjectIds = Array.from(proyectSelect.selectedOptions).map(option => option.value);

            selectedProjectIds.forEach(projectId => {
                const projectName = projectOptions[projectId] || `Proyecto ${projectId}`;
                const oldDetails = oldProjectDetails[projectId] || {};

                const projectDiv = document.createElement('div');
                projectDiv.className = 'bg-gray-50 p-4 rounded-lg border border-carbon/30 space-y-3';
                projectDiv.innerHTML = `
                    <h3 class="text-md font-bold text-gris-carbon border-b pb-2 mb-3">${projectName}</h3>

                    <div>
                        <label for="project_details_${projectId}_nombre_depto" class="block text-sm font-bold text-gris-carbon mb-1">Nombre Depto:</label>
                        <input type="text" id="project_details_${projectId}_nombre_depto" name="project_details[${projectId}][nombre_depto]"
                               class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-gris-carbon focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors"
                               value="${oldDetails.nombre_depto || ''}" required>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="project_details_${projectId}_cuenta_predial" name="project_details[${projectId}][cuenta_predial]"
                               class="form-checkbox h-4 w-4 text-dorado rounded border-gray-300 focus:ring-dorado"
                               ${oldDetails.cuenta_predial ? 'checked' : ''}>
                        <label for="project_details_${projectId}_cuenta_predial" class="text-sm font-bold text-gris-carbon">¿Cuenta Predial?</label>
                    </div>

                    <div id="agregarCuentaDiv_${projectId}" class="${oldDetails.cuenta_predial ? '' : 'hidden'} space-y-3">
                        <div>
                            <label for="project_details_${projectId}_cuenta_numero" class="block text-sm font-bold text-gris-carbon mb-1">Número de Cuenta:</label>
                            <input type="text" id="project_details_${projectId}_cuenta_numero" name="project_details[${projectId}][cuenta_numero]"
                                   class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-gris-carbon focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors"
                                   value="${oldDetails.cuenta_numero || ''}">
                        </div>
                    </div>

                    <div>
                        <label for="project_details_${projectId}_importe" class="block text-sm font-bold text-gris-carbon mb-1">Importe:</label>
                        <input type="number" step="0.01" id="project_details_${projectId}_importe" name="project_details[${projectId}][importe]"
                               class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-gris-carbon focus:outline-none focus:border-dorado focus:ring-1 focus:ring-dorado transition-colors"
                               value="${oldDetails.importe || ''}" required>
                    </div>
                `;

                dynamicProjectFields.appendChild(projectDiv);

                // Add event listener for Cuenta Predial checkbox
                const cuentaPredialCheckbox = projectDiv.querySelector(`#project_details_${projectId}_cuenta_predial`);
                const agregarCuentaDiv = projectDiv.querySelector(`#agregarCuentaDiv_${projectId}`);

                cuentaPredialCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        agregarCuentaDiv.classList.remove('hidden');
                    } else {
                        agregarCuentaDiv.classList.add('hidden');
                        agregarCuentaDiv.querySelector('input').value = ''; // Clear input if hidden
                    }
                });
            });
        }

        // Initial render
        renderDynamicProjectFields();

        // Attach event listener for multiselect changes
        proyectSelect.addEventListener('change', renderDynamicProjectFields);
    });
</script>

@endsection