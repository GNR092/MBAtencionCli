@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl mx-auto p-6">

        {{-- HEADER GIGANTE (Igual a Facturas) --}}
        <header class="mb-10 px-2">
            <div class="flex items-baseline gap-4">
                <span class="text-dorado text-sm font-serif italic">|</span>
                <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
                    Usuarios<span class="font-light text-dorado"></span><span class="text-dorado animate-pulse">_</span>
                </h1>
            </div>
        </header>

        {{-- BARRA DE ACCIONES Y MENSAJES --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 px-2 gap-4">

            {{-- Mensaje de éxito --}}
            <div class="flex-1">
                @if (session('success'))
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded bg-green-900/30 text-green-400 border border-green-500/30 text-xs tracking-wider uppercase">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
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
                        <tr>
                            <td class="font-bold text-gris-carbon uppercase">
                                {{ $user->name }}
                            </td>
                            <td class="text-gray-500 font-medium">
                                {{ $user->email }}
                            </td>
                            <td>
                            <span class="inline-block px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-[10px] font-bold uppercase tracking-wider border border-gray-300">
                                Usuario
                            </span>
                            </td>
                            <td>
                                <div class="flex justify-center gap-2">
                                    <button onclick="openModal('{{ $user->id }}')"
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
                            <td colspan="4" class="py-10 text-gris-carbon font-medium text-center italic">
                                No hay usuarios registrados.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($users->hasPages())
                <div class="bg-gray-50 border-t border-carbon p-4 flex justify-center">
                    {{ $users->links('pagination::tailwind') }}
                </div>
            @endif
        </div>

    </div>

    <div id="modalUsuario" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm items-center justify-center p-4" onclick="cerrarModalUsuario()">
        <div onclick="event.stopPropagation()" class="bg-[#1a1a1a] rounded-xl shadow-2xl w-full max-w-2xl border border-white/10 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center bg-black/20">
                <h2 class="text-xl font-light text-[#d8c495] tracking-widest uppercase">Nuevo Usuario</h2>
                <button onclick="cerrarModalUsuario()" class="text-white/50 hover:text-white">✕</button>
            </div>
            <div class="p-6 overflow-y-auto custom-scroll">
                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @include('usuarios._form', ['prefix' => 'crear', 'usuario' => null, 'roles' => $roles, 'areas' => $areas])
                    <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                        <button type="button" onclick="cerrarModalUsuario()" class="px-4 py-2 text-white/60 hover:text-white text-xs uppercase font-bold tracking-widest transition">Cancelar</button>
                        <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-6 py-2 rounded text-xs font-bold uppercase tracking-widest transition">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm items-center justify-center p-4" onclick="closeModal()">
        <div onclick="event.stopPropagation()" class="bg-[#1a1a1a] rounded-xl shadow-2xl w-full max-w-2xl border border-white/10 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center bg-black/20">
                <h2 class="text-xl font-light text-[#d8c495] tracking-widest uppercase">Editar Usuario</h2>
                <button onclick="closeModal()" class="text-white/50 hover:text-white">✕</button>
            </div>
            <div class="p-6 overflow-y-auto custom-scroll">
                <form id="formEditarUsuario" action="{{ route('users.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="id" id="userIdInput"/>
                    @include('usuarios._form', ['prefix' => 'editar', 'usuario' => null, 'roles' => $roles, 'areas' => $areas])
                    <div>
                        <label class="block text-xs font-bold text-[#d8c495] uppercase tracking-widest mb-2">Contraseña Admin</label>
                        <input type="password" name="password" required class="w-full bg-white/5 border border-white/10 rounded px-3 py-2 text-white focus:border-[#d8c495] outline-none transition">
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 text-white/60 hover:text-white text-xs uppercase font-bold tracking-widest transition">Cancelar</button>
                        <button type="submit" class="bg-[#d8c495] hover:bg-[#c9a143] text-black px-6 py-2 rounded text-xs font-bold uppercase tracking-widest transition">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="deleteConfirmModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm items-center justify-center p-4" onclick="closeDeleteModal()">
        <div onclick="event.stopPropagation()" class="bg-[#1a1a1a] rounded-xl shadow-2xl w-full max-w-md border border-white/10 overflow-hidden">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-900/30 mb-4">
                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-white uppercase tracking-wider mb-2">Eliminar Usuario</h3>
                <p class="text-sm text-gray-400 mb-6">Esta acción es irreversible. Ingresa tu contraseña.</p>
                <form id="formEliminarUsuario" action="{{ route('users.eliminar') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" id="deleteUserIdInput"/>
                    <input type="password" name="password" id="delete_password" required placeholder="CONTRASEÑA..." class="w-full bg-white/5 border border-white/10 rounded px-3 py-2 text-white text-center focus:border-red-500 outline-none mb-6">
                    <div class="flex justify-center gap-3">
                        <button type="button" onclick="closeDeleteModal()" class="w-full px-4 py-2 border border-white/10 text-white/60 hover:bg-white/5 rounded text-xs uppercase font-bold tracking-widest">Cancelar</button>
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded text-xs uppercase font-bold tracking-widest shadow-lg">Eliminar</button>
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
