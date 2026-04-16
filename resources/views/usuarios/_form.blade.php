@csrf
@if(isset($usuario))
    @method('PUT')
@endif

@php
    $prefix = $prefix ?? (isset($usuario) ? 'editar' : 'crear');
    $selectedProjects = collect(old('proyect', []))->map(fn ($id) => (string) $id)->toArray();
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

    <div class="space-y-2">
        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Teléfono</label>
        <div class="flex">
            <span class="inline-flex items-center px-3 border border-r-0 border-white/10 bg-white/5 text-gray-400 text-xs font-bold rounded-l">+52</span>
            <input type="text"
                   name="phone"
                   id="{{ $prefix }}_phone"
                   value="{{ old('phone', isset($usuario) ? (strlen($usuario->phone ?? '') > 10 ? substr($usuario->phone, 2) : ($usuario->phone ?? '')) : '') }}"
                   required
                   maxlength="10"
                   placeholder="10 dígitos"
                   class="w-full rounded-r-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-gray-500
                          focus:ring-2 focus:ring-[#d8c495] focus:border-transparent transition outline-none">
        </div>
    </div>

    <div class="space-y-2">
        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Régimen Fiscal</label>
        <div class="relative">
            <select name="regimenFiscal"
                    id="{{ $prefix }}_regimen"
                    required
                    class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white appearance-none
                           focus:ring-2 focus:ring-[#d8c495] focus:border-transparent transition outline-none">
                <option value="" class="bg-[#112134]">Selecciona un régimen</option>
                @foreach(($regimenesFiscales ?? []) as $regimen)
                    <option value="{{ $regimen->id_regimen }}" @selected((string) old('regimenFiscal') === (string) $regimen->id_regimen) class="bg-[#112134]">
                        {{ $regimen->nombre_regimen }}
                    </option>
                @endforeach
            </select>
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
                    size="6"
                    class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white appearance-none
                           focus:ring-2 focus:ring-[#d8c495] focus:border-transparent transition outline-none">
                @foreach(($proyectos ?? []) as $proyecto)
                    <option value="{{ $proyecto->id_proyecto }}" @selected(in_array((string) $proyecto->id_proyecto, $selectedProjects, true)) class="bg-[#112134]">
                        {{ $proyecto->nombre_proyecto }}@if($proyecto->razonSocial) - {{ $proyecto->razonSocial->nombre_razon_social }}@endif
                    </option>
                @endforeach
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
