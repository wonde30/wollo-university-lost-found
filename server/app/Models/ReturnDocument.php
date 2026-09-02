<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $return_id
 * @property string      $document_type
 * @property string      $path
 * @property string|null $original_name
 * @property string|null $mime_type
 * @property int|null    $size_bytes
 * @property bool        $emailed_to_student
 * @property \Carbon\Carbon|null $emailed_at
 * @property \Carbon\Carbon $generated_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class ReturnDocument extends Model
{
    use HasFactory;

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

    protected function casts(): array
    {
        return [
            'return_id'          => 'integer',
            'size_bytes'         => 'integer',
            'emailed_to_student' => 'boolean',
            'emailed_at'         => 'datetime',
            'generated_at'       => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function returnRecord(): BelongsTo
    {
        return $this->belongsTo(ReturnRecord::class, 'return_id');
    }
}
