<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MB Signature Properties')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        #bg-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            background-color: #112134; /* Color de respaldo */
        }
        #app-layout {
            position: relative;
            z-index: 1;
            background: transparent;
        }
    </style>
</head>
<body>

<div id="bg-container"></div>

<div id="app-layout">
    @yield('layout-content')
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/88/three.min.js"></script>

<script id="vertexShader" type="x-shader/x-vertex">
    void main() {
        gl_Position = vec4( position, 1.0 );
    }
</script>

<script id="fragmentShader" type="x-shader/x-fragment">
    uniform vec2 u_resolution;
    uniform vec2 u_mouse;
    uniform float u_time;
    uniform sampler2D u_noise;
    uniform sampler2D u_environment;

    vec2 movement;
    float scale = 7.0;

    vec2 hash2(vec2 p) {
        vec2 o = texture2D( u_noise, (p+0.5)/256.0, -100.0 ).xy;
        return o;
    }

    const int octaves = 5;

    float sinnoise(vec3 p){
        float s = (sin(u_time) * .5 + .5);
        float _c = cos(float(p.x * .1));
        float _s = sin(float(p.x) * .1);
        mat2 mat = mat2(_c, -_s, _s, _c);
        for (int i=0; i<octaves; i++){
            p += cos( p.yxz * 3. + vec3(0., u_time, 10.6)) * (.25 + s * .2);
            p += sin( p.yxz + vec3(u_time, .1, 0.)) * (.5 - s * .1) ;
            p *= 1. + s * .1;
            p.xy *= mat;
        }
        return length(p);
    }

    vec3 envMap(vec3 rd, vec3 sn){
        rd.xy -= u_time*.2;
        rd /= scale;
        vec3 col = texture2D(u_environment, rd.xy - .5).rgb;
        col *= normalize(col);
        return col;
    }

    float bumpMap(vec2 uv, float height) {
        float bump = sinnoise(vec3(uv, 1.));
        return bump * height;
    }

    vec4 renderPass(vec2 uv, vec2 uvoffset) {
        vec3 surfacePos = vec3(uv, 0.0);
        vec3 ray = normalize(vec3(uv - movement, 1.));
        vec3 lightPos = vec3(cos(u_time * .5 + 2.) * 2., 1. + sin(u_time * .5 + 2.) * 2., -3.) - vec3(movement, 0.);
        vec3 normal = vec3(0., 0., -1);
        vec2 sampleDistance = vec2(.001, -0.00);

        float fx = bumpMap(surfacePos.xy-sampleDistance.xy + uvoffset, 1.);
        float fy = bumpMap(surfacePos.xy-sampleDistance.yx + uvoffset, 1.);
        float f = bumpMap(surfacePos.xy + uvoffset, 1.);
        float freq = (f + fx + fy);
        freq = freq * freq;

        fx = (fx-f)/sampleDistance.x;
        fy = (fy-f)/sampleDistance.x;
        normal = normalize( normal + vec3(fx, fy, 0) * 0.2 );

        vec3 lightV = lightPos - surfacePos;
        float lightDist = max(length(lightV), 0.001);
        lightV /= lightDist;

        vec3 lightColour = vec3(.8, .8, 1.);
        float shininess = 0.5;
        float falloff = 0.1;
        float attenuation = 1./(1.0 + lightDist*lightDist*falloff);

        float diffuse = max(dot(normal, lightV), 0.);
        float specular = pow(max(dot( reflect(-lightV, normal), -ray), 0.), 52.) * shininess;

        // COLORES CORPORATIVOS MB SIGNATURE
        vec3 plasma = mix(vec3(0.07, 0.13, 0.20), vec3(0.85, 0.77, 0.58), smoothstep(80., 100., freq));

        vec2 n = hash2(uv * 200. + u_time * 5000.);
        plasma += hash2(n).x * 0.02;

        vec3 reflect_ray = reflect(vec3(uv - movement, 1.), normal * 1.);
        vec3 tex = envMap(reflect_ray, normal);

        vec3 texCol = (vec3(.5, .4, .2) + tex * 1.0) * .5;
        vec3 colour = (texCol * (diffuse*vec3(1, .97, .92)*2. + 0.5) + lightColour*specular * f * 2.)*attenuation*1.5;
        colour *= 2.;
        colour = mix(colour, plasma, 1. - smoothstep(80., 110., freq));

        return vec4(colour, 1.);
    }

    void main() {
        vec2 uv = (gl_FragCoord.xy - 0.5 * u_resolution.xy) / min(u_resolution.y, u_resolution.x);
        float dynamicScale = 4. + sin(u_time * .2) * 3.;
        uv *= dynamicScale;
        vec4 render = renderPass(uv, vec2(0.));
        render += render * render * .5;
        gl_FragColor = render;
    }
</script>

<script>
    let container, camera, scene, renderer, uniforms;
    let loader = new THREE.TextureLoader();
    loader.setCrossOrigin("anonymous");

    const noiseURL = 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/982762/noise.png';
    const envURL = 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/982762/env_lat-lon.png';

    const startApp = (noiseTex = null, envTex = null) => {
        init(noiseTex, envTex);
        animate(0);
    };

    loader.load(noiseURL,
        (nTex) => {
            nTex.wrapS = nTex.wrapT = THREE.RepeatWrapping;
            loader.load(envURL,
                (eTex) => {
                    eTex.wrapS = eTex.wrapT = THREE.RepeatWrapping;
                    startApp(nTex, eTex);
                },
                null,
                () => startApp(nTex, null)
            );
        },
        null,
        () => startApp(null, null)
    );

    function init(noise, env) {
        container = document.getElementById('bg-container');
        if(!container) return;

        camera = new THREE.Camera();
        camera.position.z = 1;
        scene = new THREE.Scene();

        uniforms = {
            u_time: { type: "f", value: 1.0 },
            u_resolution: { type: "v2", value: new THREE.Vector2() },
            u_noise: { type: "t", value: noise },
            u_environment: { type: "t", value: env },
            u_mouse: { type: "v2", value: new THREE.Vector2() }
        };

        let material = new THREE.ShaderMaterial({
            uniforms: uniforms,
            vertexShader: document.getElementById('vertexShader').textContent,
            fragmentShader: document.getElementById('fragmentShader').textContent
        });

        scene.add(new THREE.Mesh(new THREE.PlaneGeometry(2, 2), material));

        renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setPixelRatio(window.devicePixelRatio);

        container.innerHTML = '';
        container.appendChild(renderer.domElement);

        onWindowResize();
        window.addEventListener('resize', onWindowResize, false);
    }

    function onWindowResize() {
        if(renderer) {
            renderer.setSize(window.innerWidth, window.innerHeight);
            uniforms.u_resolution.value.x = renderer.domElement.width;
            uniforms.u_resolution.value.y = renderer.domElement.height;
        }
    }

    function animate(delta) {
        requestAnimationFrame(animate);
        if(uniforms) {
            uniforms.u_time.value = delta * 0.0005;
            renderer.render(scene, camera);
        }
    }
</script>

@stack('scripts')
</body>
</html>
