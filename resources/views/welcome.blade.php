<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title class="text-XL">MB Signature Properties | Inversiones</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<figure class="relative h-screen w-screen overflow-hidden">
    <!-- <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/uploads/background.jpg')"></div> -->
    <div class="absolute inset-0 bg-cover bg-center bg-[#3c3c3c]" ></div>
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative flex flex-col items-center justify-between h-full py-16 text-white">

        <div class="text-center">
                <span class="uppercase tracking-[0.3em] text-sm font-medium opacity-90 border-b border-white/30 pb-2">
                    Rentas Garantizadas
                </span>
        </div>

        <div class="flex flex-col items-center gap-6">
            <img src="/uploads/Logo-Png.svg" alt="logo" class="w-64 h-auto drop-shadow-2xl">

            <div class="mt-4">
                <a href="/login" class="inline-block py-3 px-12 text-sm tracking-widest font-semibold text-white bg-black/80 hover:bg-black border border-white/10 rounded-full transition-all duration-300">
                    INGRESAR
                </a>
            </div>
        </div>

        <div class="w-full max-w-5xl px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center bg-white/10 backdrop-blur-md rounded-2xl py-10 border border-white/10">

                <div class="flex flex-col items-center border-r border-white/10 last:border-none">
                    <span class="text-4xl md:text-5xl font-bold text-[#FFBF00] count-up" data-target="15">0</span>
                    <p class="text-[11px] uppercase tracking-wider text-white/70 mt-2">Proyectos realizados</p>
                </div>

                <div class="flex flex-col items-center border-r border-white/10 last:border-none">
                    <div class="text-4xl md:text-5xl font-bold text-[#FFBF00] flex">
                        <span>+</span>
                        <span class="count-up" data-target="1200">0</span>
                    </div>
                    <p class="text-[11px] uppercase tracking-wider text-white/70 mt-2">Propiedades vendidas</p>
                </div>

                <div class="flex flex-col items-center last:border-none">
                    <div class="text-4xl md:text-5xl font-bold text-[#FFBF00] flex items-baseline">
                        <span>+</span>
                        <span class="count-up" data-target="12">0</span>
                        <span class="ml-1 text-2xl uppercase">Años</span>
                    </div>
                    <p class="text-[11px] uppercase tracking-wider text-white/70 mt-2">De experiencia</p>
                </div>

            </div>
        </div>
    </div>
</figure>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const counters = document.querySelectorAll('.count-up');

        const formatNumber = (num) => {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        };

        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            const duration = 5000; // 2 segundos de animación
            const increment = target / (duration / 16); // 60fps aprox

            let currentCount = 0;

            const updateCounter = () => {
                currentCount += increment;
                if (currentCount < target) {
                    counter.innerText = formatNumber(Math.ceil(currentCount));
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.innerText = formatNumber(target);
                }
            };

            updateCounter();
        });
    });
</script>
</body>
</html>
