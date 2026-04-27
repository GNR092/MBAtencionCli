<?php

namespace Canva\HBD\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HbdSent extends Model
{
    protected $table = 'hbd_sents';

    protected $fillable = [
        'user_id',
        'hbd_template_id',
        'sent_date',
        'recipient_email',
        'rendered_html',
    ];

    protected $casts = [
        'sent_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        $userClass = config('hbd.user_class', 'App\\Models\\User');
        return $this->belongsTo($userClass, 'user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(HbdTemplate::class, 'hbd_template_id');
    }

    public function scopeSentToday($query)
    {
        return $query->whereDate('sent_date', now()->toDateString());
    }

    public static function wasSentToday(int $userId): bool
    {
        return static::where('user_id', $userId)
            ->sentToday()
            ->exists();
    }
}
