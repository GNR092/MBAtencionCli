<?php

namespace Canva\HBD\Services;

use Canva\HBD\Models\HbdTemplate;

class HbdTemplateRenderer
{
    public function render(HbdTemplate $template, string $nombre, string $device = 'desktop'): string
    {
        $content = $template->content ?? [];
        $sections = $content[$device] ?? $content['desktop'] ?? [];

        if (empty($sections)) {
            return $this->renderFallback($nombre);
        }

        $html = '';
        foreach ($sections as $section) {
            $html .= $this->renderSection($section, $nombre);
        }

        return $html;
    }

    public function renderForUser(HbdTemplate $template, string $nombre): string
    {
        $html = $this->render($template, $nombre);

        $nombreEscaped = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $html = str_replace('{nombre}', '<strong>' . $nombreEscaped . '</strong>', $html);

        return $this->wrapInEmailLayout($html);
    }

    private function renderSection(array $section, string $nombre): string
    {
        $bgType = $section['bgType'] ?? 'color';
        $bgValue = $section['bgValue'] ?? '#1a1a2e';
        $height = $section['height'] ?? 400;

        $bgStyle = $this->getBackgroundStyle($bgType, $bgValue);
        $style = "{$bgStyle} min-height: {$height}px; position: relative; overflow: hidden;";

        $html = "<div style='{$style}'>";
        $html .= "<div style='position: relative; width: 100%; height: 100%;'>";

        $components = $section['components'] ?? [];
        foreach ($components as $comp) {
            $html .= $this->renderComponent($comp, $nombre);
        }

        $html .= "</div></div>";
        return $html;
    }

    private function getBackgroundStyle(string $type, string $value): string
    {
        return match ($type) {
            'image' => "background-image: url('{$value}'); background-size: cover; background-position: center;",
            'gradient' => "background: {$value};",
            default => "background: {$value};",
        };
    }

    private function renderComponent(array $comp, string $nombre): string
    {
        $type = $comp['type'] ?? 'text';
        $top = $comp['top'] ?? 0;
        $left = $comp['left'] ?? 0;
        $width = $comp['width'] ?? 100;

        $style = "position: absolute; top: {$top}%; left: {$left}%; width: {$width}%;";

        return match ($type) {
            'text' => $this->renderTextComponent($comp, $style, $nombre),
            'image' => $this->renderImageComponent($comp, $style),
            'button' => $this->renderButtonComponent($comp, $style),
            'shape' => $this->renderShapeComponent($comp, $style),
            default => '',
        };
    }

    private function renderTextComponent(array $comp, string $baseStyle, string $nombre): string
    {
        $content = $comp['content'] ?? '';
        $content = str_replace('{nombre}', $nombre, $content);

        $color = $comp['color'] ?? '#ffffff';
        $fontSize = $comp['fontSize'] ?? 16;
        $fontWeight = $comp['fontWeight'] ?? 'normal';
        $textAlign = $comp['textAlign'] ?? 'left';
        $fontFamily = $comp['fontFamily'] ?? 'Arial, sans-serif';
        $subtype = $comp['subtype'] ?? 'body';

        $tag = $subtype === 'heading' ? 'h1' : 'p';
        $lineHeight = $subtype === 'heading' ? 1.2 : 1.5;
        $margin = $subtype === 'heading' ? '0 0 10px 0' : '0 0 5px 0';

        $style = "{$baseStyle} color: {$color}; font-size: {$fontSize}px; font-weight: {$fontWeight}; text-align: {$textAlign}; font-family: {$fontFamily}; line-height: {$lineHeight}; margin: {$margin};";

        return "<{$tag} style='{$style}'>{$content}</{$tag}>";
    }

    private function renderImageComponent(array $comp, string $baseStyle): string
    {
        $url = $comp['url'] ?? '';
        $height = $comp['height'] ?? 'auto';

        if (empty($url)) {
            return '';
        }

        $style = "{$baseStyle} height: {$height}%; object-fit: contain; display: block;";

        return "<img src='{$url}' style='{$style}' alt='image' loading='lazy' />";
    }

    private function renderButtonComponent(array $comp, string $baseStyle): string
    {
        $text = $comp['text'] ?? 'Click aquí';
        $bgColor = $comp['bgColor'] ?? '#d8c495';
        $textColor = $comp['textColor'] ?? '#1a1a2e';
        $borderRadius = $comp['borderRadius'] ?? 8;
        $padding = $comp['padding'] ?? '12px 24px';
        $href = $comp['href'] ?? '#';

        $style = "{$baseStyle} display: inline-block; background: {$bgColor}; color: {$textColor}; text-decoration: none; border-radius: {$borderRadius}px; padding: {$padding}; font-weight: bold; text-align: center;";

        return "<a href='{$href}' style='{$style}'>{$text}</a>";
    }

    private function renderShapeComponent(array $comp, string $baseStyle): string
    {
        $shapeType = $comp['shapeType'] ?? 'rectangle';
        $fill = $comp['fill'] ?? '#d8c495';
        $stroke = $comp['stroke'] ?? 'none';
        $strokeWidth = $comp['strokeWidth'] ?? 0;

        $style = "{$baseStyle} background: {$fill}; border: {$strokeWidth}px solid {$stroke};";

        return match ($shapeType) {
            'circle' => "<div style='{$style} border-radius: 50%;'></div>",
            'rounded' => "<div style='{$style} border-radius: 16px;'></div>",
            default => "<div style='{$style}'></div>",
        };
    }

    private function renderFallback(string $nombre): string
    {
        return "
            <div style='background: #1a1a2e; min-height: 400px; text-align: center; padding: 40px;'>
                <h1 style='color: #ffffff; font-size: 48px; margin: 0;'>
                    ¡Feliz cumpleaños, <strong>{$nombre}</strong>!
                </h1>
                <p style='color: #d8c495; font-size: 20px; margin-top: 20px;'>
                    Te deseamos un día lleno de alegría y bendiciones.
                </p>
            </div>
        ";
    }

    private function wrapInEmailLayout(string $body): string
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1'>
            </head>
            <body style='margin: 0; padding: 0; background: #f0f0f0;'>
                <table role='presentation' style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 20px 0;'>
                            <table role='presentation' style='width: 600px; max-width: 100%; margin: 0 auto; background: #ffffff; border-collapse: collapse;'>
                                <tr>
                                    <td style='padding: 0;'>
                                        {$body}
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding: 20px; text-align: center; color: #999; font-size: 12px;'>
                                        <p style='margin: 0;'>Este es un correo automático de MBSignature.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
        ";
    }
}
