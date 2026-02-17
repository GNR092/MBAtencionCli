@extends('layouts.user-simple')
@section('content')

<div class="max-w-5xl mx-auto py-8 px-4 space-y-6 text-center">
    <div class="bg-carbon-900 border border-red-500/20 rounded-xl p-8 space-y-4">
        <h1 class="text-2xl font-light text-red-400 tracking-widest uppercase">Acceso no permitido</h1>
        <p class="text-white/70">
            No se encontraron datos de factura para mostrar.
        </p>
        <p class="text-white/50 text-sm">
            Por favor, cargue un archivo XML de factura antes de acceder a esta página.
        </p>
        <div class="pt-4">
            <a href="{{ route('facturacion') }}"
               class="text-sm text-white/50 hover:text-white border border-white/10 rounded-lg px-6 py-3 transition-colors duration-300">
                &larr; Volver a la página de facturación
            </a>
        </div>
    </div>
</div>

@endsection
