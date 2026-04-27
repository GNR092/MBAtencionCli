<?php

namespace Canva\HBD\Services;

use Canva\HBD\Models\HbdTemplate;

class HbdCanvasSaver
{
    public function save(array $data, ?int $templateId = null): HbdTemplate
    {
        $template = $templateId
            ? HbdTemplate::findOrFail($templateId)
            : new HbdTemplate();

        $template->name = $data['name'] ?? 'Sin nombre';
        $template->slug = \Illuminate\Support\Str::slug($data['name'] ?? 'template');
        $template->content = $data['content'] ?? HbdTemplate::getDefaultContent();
        $template->thumbnail = $data['thumbnail'] ?? null;

        if (isset($data['is_active']) && $data['is_active']) {
            HbdTemplate::where('is_active', true)->update(['is_active' => false]);
        }

        $template->is_active = $data['is_active'] ?? false;
        $template->save();

        return $template;
    }

    public function saveMedia($file): string
    {
        $path = config('hbd.uploads_path', 'hbd-uploads');
        $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($path, $filename, 'public');
    }
}
