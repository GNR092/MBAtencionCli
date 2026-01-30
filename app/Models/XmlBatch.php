<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmlBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'total_files',
        'valid_files',
        'uploaded_pdfs',
        'uuid_mapping',
        'user_email',
        'completed',
        'deadline'
    ];

    protected $casts = [
        'uuid_mapping' => 'array',
        'completed' => 'boolean',
        'deadline' => 'datetime'
    ];

    public function xmlFiles()
    {
        return $this->hasMany(XmlFile::class, 'batch_id');
    }

    public function validXmlFiles()
    {
        return $this->hasMany(XmlFile::class, 'batch_id')->where('is_valid', true);
    }

    public function isDeadlinePassed()
    {
        return $this->deadline && $this->deadline->isPast();
    }

    public function getRemainingPdfsCount()
    {
        return $this->valid_files - $this->uploaded_pdfs;
    }
}