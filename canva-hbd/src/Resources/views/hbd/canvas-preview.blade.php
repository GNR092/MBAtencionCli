<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { margin: 0; padding: 0; background: #1a1a2e; }
    </style>
</head>
<body>

@if(!empty($sections))
    @foreach($sections as $section)
        <div style="
            position: relative;
            min-height: {{ $section['height'] ?? 400 }}px;
            width: 100%;
            @if(($section['bgType'] ?? 'color') === 'image')
                background-image: url('{{ $section['bgValue'] ?? '' }}');
                background-size: cover;
                background-position: center;
            @elseif(($section['bgType'] ?? 'color') === 'gradient')
                background: {{ $section['bgValue'] ?? '#1a1a2e' }};
            @else
                background: {{ $section['bgValue'] ?? '#1a1a2e' }};
            @endif
        ">
            @foreach($section['components'] ?? [] as $comp)
                @if($comp['type'] === 'text')
                    <div style="
                        position: absolute;
                        top: {{ $comp['top'] ?? 0 }}%;
                        left: {{ $comp['left'] ?? 0 }}%;
                        width: {{ $comp['width'] ?? 100 }}%;
                        color: {{ $comp['color'] ?? '#ffffff' }};
                        font-size: {{ $comp['fontSize'] ?? 16 }}px;
                        font-weight: {{ $comp['fontWeight'] ?? 'normal' }};
                        text-align: {{ $comp['textAlign'] ?? 'left' }};
                        font-family: {{ $comp['fontFamily'] ?? 'Arial, sans-serif' }};
                        line-height: 1.2;
                        margin: 0;
                        @if(($comp['subtype'] ?? 'body') === 'heading')
                            margin-bottom: 10px;
                        @endif
                    ">{{ str_replace('{nombre}', $nombre, $comp['content'] ?? '') }}</div>
                @elseif($comp['type'] === 'image')
                    <img src="{{ $comp['url'] ?? '' }}"
                        style="
                            position: absolute;
                            top: {{ $comp['top'] ?? 0 }}%;
                            left: {{ $comp['left'] ?? 0 }}%;
                            width: {{ $comp['width'] ?? 100 }}%;
                            height: {{ ($comp['height'] ?? 0) ? ($comp['height'] . '%') : 'auto' }};
                            object-fit: cover;
                        "
                        onerror="this.style.display='none'" />
                @elseif($comp['type'] === 'button')
                    <a href="{{ $comp['href'] ?? '#' }}"
                        style="
                            position: absolute;
                            top: {{ $comp['top'] ?? 0 }}%;
                            left: {{ $comp['left'] ?? 0 }}%;
                            width: {{ $comp['width'] ?? 100 }}%;
                            display: inline-block;
                            background: {{ $comp['bgColor'] ?? '#d8c495' }};
                            color: {{ $comp['textColor'] ?? '#1a1a2e' }};
                            padding: 10px 20px;
                            border-radius: 8px;
                            font-weight: bold;
                            text-align: center;
                            text-decoration: none;
                        ">{{ $comp['text'] ?? 'Click aquí' }}</a>
                @elseif($comp['type'] === 'shape')
                    <div style="
                        position: absolute;
                        top: {{ $comp['top'] ?? 0 }}%;
                        left: {{ $comp['left'] ?? 0 }}%;
                        width: {{ $comp['width'] ?? 20 }}%;
                        height: {{ ($comp['height'] ?? 0) ? ($comp['height'] . '%') : '20%' }};
                        background: {{ $comp['fill'] ?? '#d8c495' }};
                        @if(($comp['shapeType'] ?? 'rectangle') === 'circle')
                            border-radius: 50%;
                        @elseif(($comp['shapeType'] ?? 'rectangle') === 'rounded')
                            border-radius: 16px;
                        @endif
                    "></div>
                @endif
            @endforeach
        </div>
    @endforeach
@else
    <div style="background: #1a1a2e; min-height: 400px; text-align: center; padding: 40px;">
        <h1 style="color: #ffffff; font-size: 48px;">¡Feliz cumpleaños, <strong>{{ $nombre }}</strong>!</h1>
        <p style="color: #d8c495; font-size: 20px;">Te deseamos un día lleno de alegría y bendiciones.</p>
    </div>
@endif

</body>
</html>
