@csrf
@if(isset($usuario))
    @method('PUT')
@endif

@php
    $selectedRole = old('role', isset($usuario) ? ($usuario->roles->first()->name ?? '') : '');
    // Asignar prefijo si no se ha pasado, para retrocompatibilidad
    $prefix = $prefix ?? (isset($usuario) ? 'editar' : 'crear');
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Nombre --}}
    <div class="space-y-2">
        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Nombre Completo</label>
        <input type="text"
               name="name"
               id="{{ $prefix }}_nombre"
               value="{{ old('name', $usuario->name ?? '') }}"
               required
               placeholder="Ej. Juan Pérez"
               class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-gray-500
                      focus:ring-2 focus:ring-[#d8c495] focus:border-transparent transition outline-none">
    </div>

    {{-- Correo --}}
    <div class="space-y-2">
        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Correo Electrónico</label>
        <input type="email"
               name="email"
               id="{{ $prefix }}_email"
               value="{{ old('email', $usuario->email ?? '') }}"
               required
               placeholder="ejemplo@mbsignature.com"
               class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-gray-500
                      focus:ring-2 focus:ring-[#d8c495] focus:border-transparent transition outline-none">
    </div>

    {{-- Rol --}}
    <div class="space-y-2">
        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Rol de Sistema</label>
        <div class="relative">
            <select name="role"
                    id="{{ $prefix }}_rol"
                    required
                    class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white appearance-none
                           focus:ring-2 focus:ring-[#d8c495] focus:border-transparent transition outline-none">
                <option value="" class="bg-[#112134]">Selecciona un rol</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol }}" @selected($selectedRole === $rol) class="bg-[#112134]">
                        {{ ucfirst($rol) }}
                    </option>
                @endforeach
            </select>
            <!-- Icono flecha -->
            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>

    {{-- Proyectos --}}
    <div class="col-span-1 md:col-span-2 space-y-2">
        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Proyectos</label>
        <div class="relative">
            <select name="proyect[]"
                    id="{{ $prefix }}_proyect"
                    multiple
                    class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white appearance-none
                           focus:ring-2 focus:ring-[#d8c495] focus:border-transparent transition outline-none">
                {{-- Add project options here --}}
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>


    {{-- Contraseñas --}}
    <div class="col-span-1 md:col-span-2 border-t border-white/10 pt-4 mt-2">
        <h3 class="text-sm font-semibold text-[#d8c495] mb-4">Seguridad</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">
                    {{ isset($usuario) ? 'Nueva Contraseña (Opcional)' : 'Contraseña' }}
                </label>
                <input type="password"
                       name="password"
                       id="{{ $prefix }}_password"
                       minlength="6"
                       @if(!isset($usuario)) required @endif
                       placeholder="••••••••"
                       class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white
                              focus:ring-2 focus:ring-[#d8c495] focus:border-transparent transition outline-none">
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Confirmar Contraseña</label>
                <input type="password"
                       name="password_confirmation"
                       id="{{ $prefix }}_password_confirmation"
                       @if(!isset($usuario)) required @endif
                       placeholder="••••••••"
                       class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white
                              focus:ring-2 focus:ring-[#d8c495] focus:border-transparent transition outline-none">
            </div>
        </div>
    </div>
</div>
