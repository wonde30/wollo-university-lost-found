<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnDocument extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'return_id',
        'document_type',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'emailed_to_student',
        'emailed_at',
        'generated_at',
    ];

    protected $casts = [
        'emailed_to_student' => 'boolean',
        'emailed_at' => 'datetime',
        'generated_at' => 'datetime',
    ];

    public function returnRecord(): BelongsTo
    {
        return $this->belongsTo(ReturnRecord::class, 'return_id');
    }
}
