@extends('layouts.new_admin')

@section('content')
    <div class="max-w-7xl mx-auto mt-10">
        <h2 class="text-2xl font-bold text-white mb-6">Usuarios del sistema</h2>
        @if (session('success'))
            <div class="bg-green-600/20 text-green-200 p-4 rounded-lg shadow mb-4 border border-green-400/30">
                {{ session('success') }}
            </div>
        @endif
        <button onclick="abrirModalUsuario()"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#d8c495] text-[#112134] font-semibold shadow-md
               hover:shadow-lg hover:scale-[1.03] active:scale-95 transition-transform duration-200 ease-in-out mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#112134]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo usuario
        </button>

        <div class="overflow-y-auto max-h-[500px] bg-white/80 backdrop-blur-xl border border-[#eeeeee] shadow-lg rounded-2xl w-full">
            <table class="min-w-full table-auto text-sm text-[#112134]">
                <!-- Header -->
                <thead class="bg-[#f5f5f5] border-b border-[#eeeeee]">
                <tr>
                    <th class="px-4 py-3 font-semibold text-left">Nombre</th>
                    <th class="px-4 py-3 font-semibold text-left">Correo</th>
                    <th class="px-4 py-3 font-semibold text-left">Rol</th>
                    <th class="px-4 py-3 font-semibold text-left">Acciones</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-[#eeeeee]">
                @forelse($users as $user)
                    <tr class="odd:bg-white even:bg-[#f9f9f9] hover:bg-[#d8c495]/10 transition">
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-gray-600">Usuario</td>

                        <!-- Acciones -->
                        <td class="px-4 py-3">
                            <div class="flex gap-3 items-center">
                                <!-- Editar -->
                                <button onclick="openModal({{ $user->id }})"
                                        class="px-3 py-1.5 text-xs rounded-lg bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition"
                                        title="Editar">
                                    Editar
                                </button>

                                <!-- Eliminar -->
                                <form action="{{ route('users.eliminar') }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar este usuario?')" class="inline-flex">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs rounded-lg bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition"
                                            title="Eliminar">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500 py-6 italic">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: agregar nuevo usuario -->
    <div id="modalUsuario"
         class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
         onclick="cerrarModalUsuario()">

        <div onclick="event.stopPropagation()"
             class="bg-[#112134] rounded-3xl shadow-2xl w-full max-w-2xl relative border border-white/10"
             style="height: 80vh !important; display: flex !important; flex-direction: column !important; overflow: hidden !important;">

            <!-- Botón cerrar -->
            <button onclick="cerrarModalUsuario()"
                    class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-red-500 hover:text-white text-white/70 shadow transition z-10">
                ✕
            </button>

            <!-- Header -->
            <div class="px-8 py-6 border-b border-white/10 flex-none">
                <h2 class="text-2xl font-bold text-[#d8c495] text-center tracking-tight">
                    Registrar nuevo usuario
                </h2>
                <p class="text-gray-400 text-center text-sm mt-1">Completa la información para dar de alta al colaborador</p>
            </div>

            <!-- Formulario (Scrollable) -->
            <div class="p-8 overflow-y-auto custom-scroll flex-1" style="min-height: 0 !important;">
                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @include('usuarios._form', ['prefix' => 'crear', 'usuario' => null, 'roles' => $roles, 'areas' => $areas])

                    <div class="flex justify-end gap-3 pt-6 border-t border-white/10 mt-4">
                        <button type="button"
                                onclick="cerrarModalUsuario()"
                                class="px-6 py-2.5 rounded-xl bg-white/5 text-white hover:bg-white/10 transition font-medium">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-[#d8c495] hover:bg-[#c9b37e] text-[#112134] font-bold shadow-lg shadow-[#d8c495]/20 transition transform active:scale-95">
                            Registrar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- ================= MODAL EDITAR ================= -->
    <div id="confirmModal"
         class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
         onclick="closeModal()">

        <div onclick="event.stopPropagation()"
             class="bg-[#112134] rounded-3xl shadow-2xl w-full max-w-2xl relative border border-white/10"
             style="height: 80vh !important; display: flex !important; flex-direction: column !important; overflow: hidden !important;">

            <!-- Botón cerrar -->
            <button onclick="closeModal()"
                    class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-red-500 hover:text-white text-white/70 shadow transition z-10">
                ✕
            </button>

            <!-- Header -->
            <div class="px-8 py-6 border-b border-white/10 flex-none">
                <h2 class="text-2xl font-bold text-[#d8c495] text-center tracking-tight">
                    Editar usuario
                </h2>
                <p class="text-gray-400 text-center text-sm mt-1">Actualiza la información del colaborador</p>
            </div>

            <div class="p-8 overflow-y-auto custom-scroll flex-1" style="min-height: 0 !important;">
                <form id="formEditarUsuario" action="{{ route('users.confirmPassword') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="user_id" id="userIdInput"/>
                    @include('usuarios._form', ['prefix' => 'editar', 'usuario' => null, 'roles' => $roles, 'areas' => $areas])
                    <label for="password" class="block text-sm mb-2 text-white">Contraseña de administrador:</label>
                    <input type="password" name="password" required
                           class="w-full px-3 py-2 border border-gray-400 rounded mb-4">

                    <div class="flex justify-end gap-3 pt-6 border-t border-white/10 mt-4">
                        <button type="button" onclick="closeModal()"
                                class="px-6 py-2.5 rounded-xl bg-white/5 text-white hover:bg-white/10 transition font-medium">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold shadow-lg shadow-blue-500/20 transition transform active:scale-95">
                            Actualizar Datos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    window.abrirModalUsuario = function () {
        document.getElementById('modalUsuario').classList.remove('hidden');
    }

    window.cerrarModalUsuario = function () {
        document.getElementById('modalUsuario').classList.add('hidden');
    }

    function openModal(userId) {
        document.getElementById("confirmModal").classList.remove("hidden");
        document.getElementById("userIdInput").value = userId;
    }
    function closeModal() {
        document.getElementById("confirmModal").classList.add("hidden");
    }
</script>
@endpush
