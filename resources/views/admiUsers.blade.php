@extends('layouts.admin')

@section('content')
    <div class="w-full px-2 mb-20">
        <div class="flex flex-col gap-8 bg-transparent">

            {{-- ISLA 1: HEADER Y ACCIONES --}}
            <div class="bg-white rounded-2xl shadow-xl border border-[#c4c4c4] p-8 md:p-10 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h1 class="text-2xl text-[#1A1A1A] font-bold uppercase tracking-widest">
                        Usuarios del Sistema
                    </h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-[0.2em] mt-1">
                        Gestión de accesos y roles
                    </p>
                </div>

                <button onclick="abrirModalUsuario()"
                        class="bg-[#1A1A1A] text-white text-sm tracking-[0.2em] uppercase font-bold px-8 py-4 rounded-lg hover:bg-[#D4A017] hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-inherit" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Usuario
                </button>
            </div>

            {{-- ALERTA DE ÉXITO (Si existe) --}}
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
            @endif

            {{-- ISLA 2: TABLA DE USUARIOS --}}
            <div class="tabla-dorada-container bg-white rounded-2xl shadow-xl border border-[#c4c4c4] overflow-hidden">
                <div class="overflow-x-auto custom-scroll">
                    <table class="tabla-dorada">
                        <thead>
                        <tr>
                            <th class="text-left pl-8">Nombre</th>
                            <th class="text-left">Correo Electrónico</th>
                            <th class="text-center">Rol</th>
                            <th class="text-center pr-8">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="text-left pl-8">
                                <span class="block font-bold text-[#1A1A1A] uppercase tracking-wide">
                                    {{ $user->name }}
                                </span>
                                </td>

                                <td class="text-left text-xs font-medium text-gray-500 tracking-wide">
                                    {{ $user->email }}
                                </td>

                                <td class="text-center">
                                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-600 border border-gray-200">
                                    Usuario
                                </span>
                                </td>

                                <td class="text-center pr-8">
                                    <div class="flex justify-center gap-3">
                                        <button onclick="openModal('{{ $user->id }}')"
                                                class="text-[#D4A017] hover:text-[#b58714] transition-colors"
                                                title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>

                                        <button onclick="openDeleteModal('{{ $user->id }}')"
                                                class="text-red-400 hover:text-red-600 transition-colors"
                                                title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-16 text-center text-gray-400 text-xs uppercase tracking-widest font-bold">
                                    No hay usuarios registrados
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- ISLA 3: PAGINACIÓN (Solo visible si hay páginas) --}}
            @if($users->hasPages())
                <div class="bg-white rounded-2xl shadow-xl border border-[#c4c4c4] p-8 flex justify-center">
                    <div class="pagination-custom text-gray-600">
                        {{ $users->links() }}
                    </div>
                </div>
            @endif

        </div>

        <div id="modalUsuario" class="hidden fixed inset-0 z-50 bg-[#1A1A1A]/90 backdrop-blur-sm items-center justify-center p-4" onclick="cerrarModalUsuario()">

            <div onclick="event.stopPropagation()" class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative border border-gray-200 overflow-hidden flex flex-col max-h-[90vh]">

                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-[#1A1A1A] uppercase tracking-widest">
                            Registrar Usuario
                        </h2>
                        <p class="text-[10px] text-[#D4A017] font-bold uppercase tracking-[0.2em] mt-1">
                            Alta de colaborador
                        </p>
                    </div>
                    <button onclick="cerrarModalUsuario()" class="text-gray-400 hover:text-red-500 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-8 overflow-y-auto custom-scroll">
                    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        {{-- Asumimos que _form se adapta o usamos estilos globales para inputs --}}
                        @include('usuarios._form', ['prefix' => 'crear', 'usuario' => null, 'roles' => $roles, 'areas' => $areas])

                        <div class="pt-6 border-t border-gray-100 flex justify-end gap-4 mt-4">
                            <button type="button" onclick="cerrarModalUsuario()"
                                    class="px-6 py-3 rounded-lg border border-gray-300 text-gray-500 text-xs font-bold uppercase tracking-widest hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-8 py-3 rounded-lg bg-[#1A1A1A] text-white text-xs font-bold uppercase tracking-widest hover:bg-[#D4A017] hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="confirmModal" class="hidden fixed inset-0 z-50 bg-[#1A1A1A]/90 backdrop-blur-sm items-center justify-center p-4" onclick="closeModal()">

            <div onclick="event.stopPropagation()" class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative border border-gray-200 overflow-hidden flex flex-col max-h-[90vh]">

                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-[#1A1A1A] uppercase tracking-widest">
                            Editar Usuario
                        </h2>
                        <p class="text-[10px] text-[#D4A017] font-bold uppercase tracking-[0.2em] mt-1">
                            Actualizar información
                        </p>
                    </div>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-8 overflow-y-auto custom-scroll">
                    <form id="formEditarUsuario" action="{{ route('users.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <input type="hidden" name="id" id="userIdInput"/>

                        @include('usuarios._form', ['prefix' => 'editar', 'usuario' => null, 'roles' => $roles, 'areas' => $areas])

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-[0.2em] text-[#1A1A1A] mb-3">
                                Contraseña Admin
                            </label>
                            <input type="password" name="password" required
                                   class="w-full bg-gray-50 border border-gray-300 rounded-lg py-3 px-4 text-[#1A1A1A] focus:outline-none focus:border-[#D4A017] focus:ring-1 focus:ring-[#D4A017] transition-all">
                        </div>

                        <div class="pt-6 border-t border-gray-100 flex justify-end gap-4 mt-4">
                            <button type="button" onclick="closeModal()"
                                    class="px-6 py-3 rounded-lg border border-gray-300 text-gray-500 text-xs font-bold uppercase tracking-widest hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-8 py-3 rounded-lg bg-[#1A1A1A] text-white text-xs font-bold uppercase tracking-widest hover:bg-[#D4A017] hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                                Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="deleteConfirmModal" class="hidden fixed inset-0 z-50 bg-[#1A1A1A]/90 backdrop-blur-sm items-center justify-center p-4" onclick="closeDeleteModal()">

            <div onclick="event.stopPropagation()" class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative border border-gray-200 overflow-hidden">

                <div class="p-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                        <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-[#1A1A1A] uppercase tracking-wide mb-2">
                        Confirmar Eliminación
                    </h3>
                    <p class="text-sm text-gray-500 mb-8">
                        Esta acción es irreversible. Por favor ingresa tu contraseña para continuar.
                    </p>

                    <form id="formEliminarUsuario" action="{{ route('users.eliminar') }}" method="POST" class="text-left">
                        @csrf
                        <input type="hidden" name="user_id" id="deleteUserIdInput"/>

                        <div class="mb-6">
                            <input type="password" name="password" id="delete_password" required placeholder="CONTRASEÑA..."
                                   class="w-full bg-gray-50 border border-gray-300 rounded-lg py-3 px-4 text-[#1A1A1A] text-center focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all">
                        </div>

                        <div class="flex justify-center gap-4">
                            <button type="button" onclick="closeDeleteModal()"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-500 text-xs font-bold uppercase tracking-widest hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="w-full px-4 py-3 rounded-lg bg-red-600 text-white text-xs font-bold uppercase tracking-widest hover:bg-red-700 shadow-md hover:shadow-lg transition-all">
                                Eliminar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.abrirModalUsuario = function () {
            document.getElementById('modalUsuario').classList.remove('hidden');
            document.getElementById('modalUsuario').classList.add('flex');
        }

        window.cerrarModalUsuario = function () {
            document.getElementById('modalUsuario').classList.add('hidden');
            document.getElementById('modalUsuario').classList.remove('flex');
        }

        function openModal(userId) {
            document.getElementById("confirmModal").classList.remove("hidden");
            document.getElementById("confirmModal").classList.add("flex");
            document.getElementById("userIdInput").value = userId;
        }

        function closeModal() {
            document.getElementById("confirmModal").classList.add("hidden");
            document.getElementById("confirmModal").classList.remove("flex");
        }

        function openDeleteModal(userId) {
            document.getElementById("deleteConfirmModal").classList.remove("hidden");
            document.getElementById("deleteConfirmModal").classList.add("flex");
            document.getElementById("deleteUserIdInput").value = userId;
        }

        function closeDeleteModal() {
            document.getElementById("deleteConfirmModal").classList.add("hidden");
            document.getElementById("deleteConfirmModal").classList.remove("flex");
        }
    </script>
@endpush
