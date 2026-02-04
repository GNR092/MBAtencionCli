<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MB Signature - Simple</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            width: 100%; height: 100%; overflow: hidden;
            background-color: #474747;
        }
        #bg-canvas-container {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 1; pointer-events: none;
        }
        svg { width: 100%; height: 100%; display: block; }
        #content-overlay {
            position: relative; z-index: 10; width: 100%; height: 100vh;
            background: transparent !important; padding: 2rem; overflow-y: auto;
        }
        .inner-wrapper { max-width: 1200px; margin: 0 auto; width: 100%; }
    </style>
</head>
<body>

<div id="bg-canvas-container">
    <svg id="wave-svg">
        <defs>
            <filter id="gold-black-contrast">
                <feComponentTransfer>
                    <feFuncR type="linear" slope="10" intercept="-5" />
                    <feFuncG type="linear" slope="10" intercept="-5" />
                    <feFuncB type="linear" slope="10" intercept="-5" />
                </feComponentTransfer>

                <feColorMatrix type="matrix"
                               values="0.85 0 0 0 0
                                0.75 0 0 0 0
                                0.35 0 0 0 0
                                0    0 0 1 0" />
            </filter>

            <pattern id="img1" patternUnits="userSpaceOnUse" width="100" height="200">
                <image xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAADICAIAAACRXtOWAAADQUlEQVR4nO3Uy3HrMBQEUb1cHADzX9EROKR5C7v8kWlpRiKJC6A7gltTB7is63qp18vLi+p1WZal9TIbrevaepmN/rWeZaNlWd7e3lpfcd3r62vrE7aqyariE1yWpfUsG9X82WFlB6sgWLnBKghWbrAKgpUbrIJg5QarIFi5wSoIVm6wCoKVG6yCYOUGqyBYucEqCFZusAqClRusgmDlBqsgWLnBKghWbrAKgpUbrIJg5QarIFi5wSoIVm6wCoKVG6yCYOUGqyBYucEqCFZusAqClRusgmDlBqsgWLnBKghWbrAKgpUbrIJg5QarIFi5wSoIVm6wCoKVG6yCYOUGqyBYucEqCFZusAqClRusgmDlBqsgWLnBKghWbrAKgpUbrIJg5QarIFi5wSoIVm6wCoKVG6yCYOUGqyBYucEqCFZusAqClRusgmAV1HqWjWqyWpal4lg1Wa3rWm6ssqwklRurLCtVG6syK1UbqzIrlRqrOCuVGqs4K9UZqz4r1RmrPisVGasLVioyVhesVGGsXlipwli9sFLzsTpipeZjdcRKbcfqi5XajtUXKzUcqztWajhWd6zUaqweWanVWD2yUpOxOmWlJmN1ykrnj9UvK50/Vr+sdPJYXbPSyWN1zUpnjtU7K505Vu+sdNpYA7DSaWMNwErnjDUGK50z1hisdMJYw7DSCWMNw0pHjzUSKx091kisdOhYg7HSoWMNxkrHjTUeKx031nisdNBYQ7LSQWMNyUpHjDUqKx0x1qistPtYA7PS7mMNzEr7jjU2K+071tistONYw7PSjmMNz0p7jTUDK+011gystMtYk7DSLmNNwkrPjzUPKz0/1jys9ORYU7HSk2NNxUrPjDUbKz0z1mys9PBYE7LSw2NNyEqPjTUnKz021pys9MBY07LSA2NNy0rpWDOzUjrWzKwUjTU5K0VjTc5K/liwkj8WrGSOBav3rLFg9d79sWD12f2xYPXZnbFg9b07Y8Hqe7fGgtVVt8aC1VV/jgWr3/05Fqx+tz0WrDbbHgtWm22MBau/2hgLVn91PRasbnQ9Fqxu9GMsWN3ux1iwut3XWLC629dYsLrbx1iwcvoYC1ZOF8HK7iJY2f0HmTYOVR+4YhkAAAAASUVORK5CYII="
                       x="0" y="0" width="100" height="200"
                       style="filter: url(#gold-black-contrast);" />
            </pattern>
        </defs>
        <path id="wave-path" fill="url(#img1)"></path>
    </svg>
</div>

<div id="content-overlay">
    <div class="inner-wrapper">
        @yield('content')
    </div>
</div>

<script>
    (function() {
        let width = window.innerWidth;
        let height = window.innerHeight;
        const waveHeight = 90;
        const frequency = 180;
        const speed = 2.5;
        let tick = 0;
        const path = document.getElementById('wave-path');

        function update() {
            let points = [];
            for (let x = 0; x <= width + 20; x += 10) {
                let y = (height * 0.75) + Math.sin((x + tick) / frequency) * waveHeight;
                points.push(`${x},${y}`);
            }
            let d = `M${points.join(' L')} L${width},${height} L0,${height} Z`;
            if (path) { path.setAttribute('d', d); }
            tick += speed;
            requestAnimationFrame(update);
        }
        window.addEventListener('resize', () => {
            width = window.innerWidth;
            height = window.innerHeight;
        });
        update();
    })();
</script>
</body>
</html>
