<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BirthdayDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'birthday_date',
        'scheduled_for',
        'attempts',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'birthday_date' => 'date',
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(BirthdayTemplate::class, 'template_id');
    }
}
