@php
    $template = $delivery->template;
    $zones = collect($template->zones_json ?? []);
    $nameZone = $zones->firstWhere('type', 'name');
    $messageZone = $zones->firstWhere('type', 'message');
    $overlayImages = collect($template->overlay_images ?? []);
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Feliz cumpleanos</title>
</head>
<body style="margin:0;padding:16px;background:#0f172a;font-family:Arial,Helvetica,sans-serif;color:#fff;">
    <div style="max-width:700px;margin:0 auto;background:#111827;border:1px solid #d8c49566;border-radius:14px;padding:16px;">
        <p style="margin:0 0 10px 0;color:#d8c495;font-size:14px;">MB Signature Properties</p>
        <div style="position:relative;width:100%;min-height:360px;overflow:hidden;border-radius:12px;background:#1f2937;">
            @if(!empty($template->background_path))
                <img src="{{ asset('storage/'.$template->background_path) }}" alt="Plantilla de cumpleanos" style="width:100%;height:auto;display:block;">
            @else
                <div style="height:360px;background:linear-gradient(140deg,#1f2937,#0f172a);"></div>
            @endif

            @foreach($overlayImages as $overlay)
                <img
                    src="{{ asset('storage/'.$overlay['path']) }}"
                    style="position:absolute;left:{{ (int) ($overlay['x'] ?? 0) }}px;top:{{ (int) ($overlay['y'] ?? 0) }}px;width:{{ (int) ($overlay['width'] ?? 200) }}px;height:{{ (int) ($overlay['height'] ?? 200) }}px;transform:rotate({{ (int) ($overlay['rotation'] ?? 0) }}deg);"
                >
            @endforeach

            @if($nameZone)
                <div style="position:absolute;left:{{ (int) ($nameZone['x'] ?? 40) }}px;top:{{ (int) ($nameZone['y'] ?? 60) }}px;font-size:{{ (int) ($nameZone['fontSize'] ?? 30) }}px;color:{{ $nameZone['color'] ?? '#ffffff' }};font-weight:bold;">
                    {{ $delivery->user->name }}
                </div>
            @endif

            @if($messageZone)
                <div style="position:absolute;left:{{ (int) ($messageZone['x'] ?? 40) }}px;top:{{ (int) ($messageZone['y'] ?? 120) }}px;font-size:{{ (int) ($messageZone['fontSize'] ?? 18) }}px;color:{{ $messageZone['color'] ?? '#ffffff' }};max-width:80%;line-height:1.45;white-space:pre-wrap;">
                    {{ str_replace('[NOMBRE]', $delivery->user->name, $template->default_message ?? '') }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>