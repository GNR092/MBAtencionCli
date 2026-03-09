@extends('layouts.admin')

@section('content')
<div class="w-full p-4 md:p-6 animate-fadeInUp">
    <div class="max-w-full mx-auto p-4 md:p-6">

        {{-- HEADER GIGANTE (Igual a Facturas) --}}
        <header class="mb-10 px-2">
        <div class="flex items-baseline gap-4">
            <span class="text-dorado-400 text-sm font-serif italic">|</span>
            <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none uppercase">
                Usuarios
            </h1>
        </div>
    </header>

    {{-- BARRA DE ACCIONES Y MENSAJES --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 px-2 gap-4">

        {{-- Mensaje de éxito --}}
        <div class="flex-1">
            @if (session('success'))
            <div
                class="inline-flex items-center gap-2 px-4 py-2 rounded bg-green-900/30 text-green-400 border border-green-500/30 text-xs tracking-wider uppercase">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ session('success') }}
            </div>
            @endif
        </div>
    </div>

    {{-- TABLA DORADA --}}
    <div class="tabla-dorada-container">
        <div class="overflow-x-auto custom-scroll">
            <table class="tabla-dorada">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr id="user-{{ $user->id }}">
                        <td class="font-bold text-carbon-900 uppercase">
                            {{ $user->name }}
                        </td>
                        <td class="text-gray-500 font-medium">
                            {{ $user->email }}
                        </td>
                        <td>
                            <span
                                class="inline-block px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-[10px] font-bold uppercase tracking-wider border border-gray-300">
                                Usuario
                            </span>
                        </td>
                        <td>
                            <div class="flex justify-center gap-2">
                                @php
                                // Solo los datos que SÍ existen en tu registro
                                $userData = [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                // Asumiendo que guardas con '52' al inicio, lo quitamos para mostrar
                                'phone' => $user->phone ? (strlen($user->phone) > 10 ? substr($user->phone, 2) : $user->phone) : '',
                                'id_regimen' => $user->id_regimen,
                                'projects' => []
                                ];

                                // Estructura de proyectos y departamentos (Igual que antes)
                                foreach($user->userProyectos ?? [] as $up) {

                                $depts = [];
                                foreach($up->deptos ?? [] as $d) {

                                $depts[] = [
                                'nombre_depto' => $d->nombre,
                                'cuenta_numero' => $d->predial,
                                'importe' => $d->importe,
                                'cuenta_predial' => ($d->predial && $d->predial !== 'N/A')
                                ];
                                }
                                $userData['projects'][$up->id_proyecto] = $depts;
                                }
                                @endphp

                                <button onclick='openEditModal(@json($userData))'
                                    class="bg-blue-50 text-blue-600 border border-blue-200 px-3 py-1 rounded text-[10px] font-bold uppercase tracking-widest hover:bg-blue-100 transition">
                                    Editar
                                </button>

                                <button onclick="openDeleteModal('{{ $user->id }}')"
                                    class="bg-red-50 text-red-600 border border-red-200 px-3 py-1 rounded text-[10px] font-bold uppercase tracking-widest hover:bg-red-100 transition">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-10 text-carbon-900 font-medium text-center italic">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($users->hasPages())
        <div class="bg-gray-50 border-t border-carbon-200 p-4 flex justify-center">
            {{ $users->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
<div id="modalUsuario" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div onclick="event.stopPropagation()" class="bg-carbon-900 rounded-xl shadow-2xl w-full max-w-2xl border border-white/10 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center bg-black/20">
                <h2 class="text-xl font-light text-[#d8c495] tracking-widest uppercase">Nuevo Usuario</h2>
                <button onclick="cerrarModalUsuario()" class="text-white/50 hover:text-white">✕</button>
            </div>
            <div class="p-6 overflow-y-auto custom-scroll">
                <form action="{{ route('usuarios.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @include('usuarios._form', ['prefix' => 'crear', 'usuario' => null, 'roles' => $roles, 'areas' => $areas])
                    <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                        <button type="button" onclick="cerrarModalUsuario()" class="px-4 py-2 text-white/60 hover:text-white text-xs uppercase font-bold tracking-widest transition">Cancelar</button>
                        <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-6 py-2 rounded text-xs font-bold uppercase tracking-widest transition">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="confirmModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm items-center justify-center p-4 overflow-y-auto">
  <div class="flex min-h-full items-center justify-center">
    <div onclick="event.stopPropagation()" class="bg-carbon-900 rounded-xl shadow-2xl w-full max-w-4xl border border-white/10 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center bg-black/20">
            <h2 class="text-xl font-light text-[#d8c495] tracking-widest uppercase">Editar Usuario</h2>
            <button onclick="closeModal()" class="text-white/50 hover:text-white">✕</button>
        </div>

        <div class="p-6 overflow-y-auto center custom-scroll">
            @if ($errors->any())
            <div class="bg-red-900/30 border border-red-500/30 text-red-400 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form id="formEditarUsuario" action="{{ route('usuarios.actualizar') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="id" id="editUserIdInput" />

                {{-- Campos Idénticos al Registro --}}
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label
                            class="block text-xs font-bold text-[#d8c495] uppercase tracking-widest mb-1">Nombre</label>
                        <input type="text" name="name" id="edit_name" required
                            class="w-full bg-white/5 border border-white/10 rounded px-3 py-2 text-white focus:border-[#d8c495] outline-none">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-[#d8c495] uppercase tracking-widest mb-1">Email</label>
                        <input type="email" name="email" id="edit_email" required
                            class="w-full bg-white/5 border border-white/10 rounded px-3 py-2 text-white focus:border-[#d8c495] outline-none">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-[#d8c495] uppercase tracking-widest mb-1">Teléfono</label>
                        <div class="flex">
                            <span
                                class="inline-flex items-center px-3 border border-r-0 border-white/10 bg-white/5 text-gray-400 text-xs font-bold rounded-l">+52</span>
                            <input type="text" name="phone" id="edit_phone" required maxlength="10"
                                class="w-full bg-white/5 border border-white/10 rounded-r px-3 py-2 text-white focus:border-[#d8c495] outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#d8c495] uppercase tracking-widest mb-1">Régimen
                            Fiscal</label>
                        <select name="regimenFiscal" id="edit_regimen"
                            class="w-full bg-white/5 border border-white/10 rounded px-3 py-2 text-white focus:border-[#d8c495] outline-none">
                            <option value="" class="text-black">— Selecciona régimen —</option>
                            @foreach($regimenesFiscales ?? [] as $regimen)
                            <option value="{{ $regimen->id_regimen }}" class="text-black">{{ $regimen->nombre_regimen }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="border-white/10">

                {{-- Cambio de Contraseña --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#d8c495] uppercase tracking-widest mb-1">Nueva Contraseña (Opcional)</label>
                        <input type="password" name="password" id="edit_password" minlength="8"
                            class="w-full bg-white/5 border border-white/10 rounded px-3 py-2 text-white focus:border-[#d8c495] outline-none"
                            placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#d8c495] uppercase tracking-widest mb-1">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" id="edit_password_confirmation"
                            class="w-full bg-white/5 border border-white/10 rounded px-3 py-2 text-white focus:border-[#d8c495] outline-none"
                            placeholder="••••••••">
                    </div>
                </div>

                <hr class="border-white/10">

                {{-- Proyectos (Igual que antes) --}}
                <div>
                    <label class="block text-xs font-bold text-[#d8c495] uppercase tracking-widest mb-2">Asignación de
                        Proyectos</label>
                    <select name="proyect[]" id="edit_proyect_select" multiple
                        class="w-full bg-white/5 border border-white/10 rounded px-3 py-2 text-white h-32 focus:border-[#d8c495] outline-none custom-scroll">
                        @foreach($proyectos ?? [] as $proyecto)
                        <option value="{{ $proyecto->id_proyecto }}">{{ $proyecto->nombre_proyecto }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-500 mt-1">* Haz clic para seleccionar o deseleccionar.</p>
                    <p class="text-[10px] text-gray-500 mt-1">* Para poder deseleccionar debes eliminar todos los departamentos del proyecto.</p>
                </div>

                <div id="editDynamicProjectFields" class="space-y-4"></div>

                <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 text-white/60 hover:text-white text-xs uppercase font-bold tracking-widest transition">Cancelar</button>
                    <button type="submit"
                        class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-6 py-2 rounded text-xs font-bold uppercase tracking-widest transition">Actualizar
                        Datos</button>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>

<div id="deleteConfirmModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div onclick="event.stopPropagation()" class="bg-carbon-900 rounded-xl shadow-2xl w-full max-w-md border border-white/10 overflow-hidden">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-900/30 mb-4">
                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-white uppercase tracking-wider mb-2">Eliminar Usuario</h3>
                <p class="text-sm text-gray-400 mb-6">Esta acción es irreversible. Ingresa tu contraseña.</p>
                <form id="formEliminarUsuario" action="{{ route('usuarios.eliminar') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" id="deleteUserIdInput" />
                    <input type="password" name="password" id="delete_password" required placeholder="CONTRASEÑA..."
                        class="w-full bg-white/5 border border-white/10 rounded px-3 py-2 text-white text-center focus:border-red-500 outline-none mb-6">
                    <div class="flex justify-center gap-3">
                        <button type="button" onclick="closeDeleteModal()"
                            class="w-full px-4 py-2 border border-white/10 text-white/60 hover:bg-white/5 rounded text-xs uppercase font-bold tracking-widest">Cancelar</button>
                        <button type="submit"
                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded text-xs uppercase font-bold tracking-widest shadow-lg">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
// ==========================================
// 1. LÓGICA DEL MODAL DE CREAR (Nuevo Usuario)
// ==========================================
window.abrirModalUsuario = function() {
    document.getElementById('modalUsuario').classList.remove('hidden');
    document.getElementById('modalUsuario').classList.add('flex');
}
window.cerrarModalUsuario = function() {
    document.getElementById('modalUsuario').classList.add('hidden');
    document.getElementById('modalUsuario').classList.remove('flex');
}

// ==========================================
// 2. LÓGICA DEL MODAL DE EDITAR
// ==========================================

// Obtenemos la lista de proyectos para usar sus nombres en los encabezados
const projectOptions = @json($proyectos -> pluck('nombre_proyecto', 'id_proyecto'));

window.openEditModal = function(userData) {
    const modal = document.getElementById("confirmModal");
    const container = document.getElementById("editDynamicProjectFields");
    const select = document.getElementById("edit_proyect_select");

    // 1. Mostrar el Modal y rellenar campos básicos
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.getElementById("editUserIdInput").value = userData.id;
    document.getElementById("edit_name").value = userData.name;
    document.getElementById("edit_email").value = userData.email;
    document.getElementById("edit_phone").value = userData.phone ?? '';
    document.getElementById("edit_regimen").value = userData.id_regimen ?? '';

    // 2. Limpiar estado anterior y listeners
    container.innerHTML = '';
    select.onchange = null; // Clear previous listener

    // 3. Pre-seleccionar proyectos del usuario
    const existingProjectIds = Object.keys(userData.projects);
    Array.from(select.options).forEach(option => {
        option.selected = existingProjectIds.includes(option.value);
    });

    // 4. Inicializar los contenedores de proyectos y departamentos con los datos existentes
    refreshEditProjectContainers(select, container, userData.projects);

    // 5. Lógica de selección/deselección personalizada con bloqueo
    const lockedProjectIds = existingProjectIds.filter(id =>
        userData.projects[id] && userData.projects[id].length > 0
    );

    select.onchange = function() {
        const currentSelectedIds = Array.from(select.selectedOptions).map(o => o.value);
        let selectionChanged = false;

        // Revert deselection of locked projects
        lockedProjectIds.forEach(lockedId => {
            if (!currentSelectedIds.includes(lockedId)) {
                // If a locked project was deselected, re-select it
                Array.from(select.options).find(option => option.value === lockedId).selected = true;
                selectionChanged = true;
            }
        });

        if (selectionChanged) {
            alert('No puedes deseleccionar proyectos que tienen departamentos asignados.');
            // Re-run refreshEditProjectContainers with the corrected selection
            refreshEditProjectContainers(select, container);
        } else {
            // Only update dynamically if no locked projects were deselected or if new projects were added
            refreshEditProjectContainers(select, container);
        }
    };

    // 6. Asegurarse de que el scroll del modal esté arriba del todo
    const scrollableContent = modal.querySelector('.overflow-y-auto');
    if (scrollableContent) {
        setTimeout(() => { scrollableContent.scrollTop = 0; }, 50);
    }
}

window.closeModal = function() {
    const modal = document.getElementById("confirmModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// --- Funciones Auxiliares para la Edición ---

// Crea el bloque gris con el título del proyecto y el botón "+ Depto"
function createProjectContainer(projectId, mainContainer) {
    // Evitar duplicados
    if (document.getElementById(`edit_project_container_${projectId}`)) return;

    const projectName = projectOptions[projectId] || 'Proyecto';

    const div = document.createElement('div');
    div.id = `edit_project_container_${projectId}`;
    div.className = 'bg-white/5 p-4 rounded-xl border border-white/10 mb-4 fade-in-content';

    div.innerHTML = `
            <div class="flex justify-between items-center border-b border-white/10 pb-2 mb-3">
                <h3 class="text-sm font-bold text-[#d8c495] uppercase">${projectName}</h3>
                <button type="button" onclick="addEditDepartment('${projectId}')"
                        class="text-[10px] px-2 py-1 border border-[#d8c495] text-[#d8c495] rounded hover:bg-[#d8c495] hover:text-black transition">
                    + Depto
                </button>
            </div>
            <div id="edit_departments_list_${projectId}" class="space-y-3">
                </div>
        `;
    mainContainer.appendChild(div);
}

// Agrega una fila de departamento (Nombre, Importe, Predial)
window.addEditDepartment = function(projectId, data = null) {
    const list = document.getElementById(`edit_departments_list_${projectId}`);
    // Usamos timestamp para generar índices únicos y evitar conflictos al borrar/agregar
    const uniqueIndex = Date.now() + Math.floor(Math.random() * 1000);

    // Valores: si viene 'data' es edición, si no, es vacío
    const nombre = data ? data.nombre_depto : '';
    const importe = data ? data.importe : '';
    const predialNum = data ? data.cuenta_numero : '';
    const tienePredial = data ? data.cuenta_predial : false;

    const deptDiv = document.createElement('div');
    deptDiv.className = 'bg-black/20 p-3 rounded border border-white/5 relative fade-in-content';

    deptDiv.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-2">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-1">Nombre Depto</label>
                    <input type="text" name="project_details[${projectId}][${uniqueIndex}][nombre_depto]" value="${nombre}"
                           class="w-full bg-white/10 border border-white/10 rounded px-2 py-1 text-white text-xs focus:border-[#d8c495] outline-none" required>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-1">Importe</label>
                    <input type="number" step="0.01" name="project_details[${projectId}][${uniqueIndex}][importe]" value="${importe}"
                           class="w-full bg-white/10 border border-white/10 rounded px-2 py-1 text-white text-xs focus:border-[#d8c495] outline-none" required>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="edit_check_${uniqueIndex}"
                           name="project_details[${projectId}][${uniqueIndex}][cuenta_predial]"
                           onchange="toggleEditPredial(this, '${uniqueIndex}')"
                           class="form-checkbox h-3 w-3 text-[#d8c495] rounded border-gray-500 bg-transparent"
                           ${tienePredial ? 'checked' : ''}>
                    <label for="edit_check_${uniqueIndex}" class="text-xs text-gray-400">¿Cuenta Predial?</label>
                </div>

                <div id="edit_predial_div_${uniqueIndex}" class="${tienePredial ? '' : 'hidden'} ml-2 flex-1">
                    <input type="text" name="project_details[${projectId}][${uniqueIndex}][cuenta_numero]" value="${predialNum}"
                           placeholder="Núm. Cuenta"
                           class="w-full bg-white/10 border border-white/10 rounded px-2 py-1 text-white text-xs focus:border-[#d8c495] outline-none">
                </div>

                <button type="button" onclick="this.closest('.bg-black\\/20').remove()" class="text-red-500 text-xs ml-auto hover:text-red-400 transition">
                    ✕ Eliminar
                </button>
            </div>
        `;
    list.appendChild(deptDiv);
}

// Muestra u oculta el campo de número de cuenta predial
window.toggleEditPredial = function(checkbox, uniqueIndex) {
    const div = document.getElementById(`edit_predial_div_${uniqueIndex}`);
    if (checkbox.checked) {
        div.classList.remove('hidden');
    } else {
        div.classList.add('hidden');
        div.querySelector('input').value = ''; // Limpiar valor al ocultar
    }
}

// Maneja qué pasa cuando seleccionas/deseleccionas proyectos en el select múltiple
function refreshEditProjectContainers(select, container, initialProjectsData = null) {
    const selectedIds = Array.from(select.selectedOptions).map(o => o.value);

    // Clear existing containers if it's the initial load
    if (initialProjectsData) {
        container.innerHTML = '';
    }

    // 1. Add new containers if they don't exist, or repopulate for initial load
    selectedIds.forEach(id => {
        if (!document.getElementById(`edit_project_container_${id}`)) {
            createProjectContainer(id, container);
            // On initial load, populate with existing departments
            if (initialProjectsData && initialProjectsData[id] && initialProjectsData[id].length > 0) {
                initialProjectsData[id].forEach(dept => addEditDepartment(id, dept));
            } else {
                addEditDepartment(id); // Add one empty department for new selections or if no existing departments
            }
        } else if (initialProjectsData && !container.querySelector(`#edit_departments_list_${id} > div`)) {
            // If it's initial load and container exists but has no departments (e.g., project was selected but no dept data), add one
            addEditDepartment(id);
        }
    });

    // 2. Remove containers of projects that are no longer selected
    Array.from(container.children).forEach(child => {
        const id = child.id.replace('edit_project_container_', '');
        if (!selectedIds.includes(id)) {
            child.remove();
        }
    });
}


window.openDeleteModal = function(userId) {
    document.getElementById("deleteConfirmModal").classList.remove("hidden");
    document.getElementById("deleteConfirmModal").classList.add("flex");
    document.getElementById("deleteUserIdInput").value = userId;
}
window.closeDeleteModal = function() {
    document.getElementById("deleteConfirmModal").classList.add("hidden");
    document.getElementById("deleteConfirmModal").classList.remove("flex");
}
</script>

{{-- NUEVO: Script y estilos para scroll y highlight --}}
<style>
@keyframes highlight-animation {
    from {
        background-color: rgba(216, 196, 149, 0.4);
    }

    to {
        background-color: transparent;
    }
}

.highlight-row {
    animation: highlight-animation 2.5s ease-out;
}
</style>

@if (session('highlight_user'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    const userId = '{{ session('highlight_user') }}';
    const userRow = document.getElementById(`user-${userId}`);
    if (userRow) {
        setTimeout(() => {
            userRow.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            userRow.classList.add('highlight-row');
        }, 100);
    }
});
</script>
@endif

@if ($errors->any() && old('id'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById("confirmModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.getElementById("editUserIdInput").value = '{{ old('id') }}';
    document.getElementById("edit_name").value = '{{ old('name') }}';
    document.getElementById("edit_email").value = '{{ old('email') }}';
    document.getElementById("edit_phone").value = '{{ old('phone') }}';
    document.getElementById("edit_regimen").value = '{{ old('regimenFiscal') }}';
});
</script>
@endif
@endpush
