@extends('layouts.user-simple')
@section('content')

<div class="max-w-5xl mx-auto py-8 px-4 space-y-6 text-center">
    <div class="bg-carbon-900 border border-green-500/20 rounded-xl p-8 space-y-4">
        <h1 class="text-2xl font-light text-green-400 tracking-widest uppercase">¡Éxito!</h1>
        <p class="text-white/70">
            Todas las facturas han sido procesadas y confirmadas correctamente.
        </p>
        <div class="pt-4">
            <a href="{{ route('user.facturacion') }}"
               class="text-sm text-white/50 hover:text-white border border-white/10 rounded-lg px-6 py-3 transition-colors duration-300">
                &larr; Volver a la página de facturación
            </a>
        </div>
    </div>
</div>

@endsection
