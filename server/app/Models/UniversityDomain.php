<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * @property int         $id
 * @property string      $domain
 * @property string      $institution_name
 * @property int|null    $campus_id
 * @property bool        $is_active
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read Campus|null $campus
 */
class UniversityDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'institution_name',
        'campus_id',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'campus_id' => 'integer',
        ];
    }

    /**
     * Mutator to safely normalize domain strings to lowercase without whitespace or leading @ symbols.
     */
    public function setDomainAttribute(string $value): void
    {
        $this->attributes['domain'] = static::normalizeDomainString($value);
    }

    /**
     * Normalizes a domain or email domain portion.
     */
    public static function normalizeDomainString(string $domain): string
    {
        $clean = trim(strtolower($domain));
        $clean = ltrim($clean, '@');
        return rtrim($clean, '.');
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                             */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /* ------------------------------------------------------------------ */
    /*  Cached Active Domain Resolution                                    */
    /* ------------------------------------------------------------------ */

    public static function getActiveDomains(): array
    {
        return Cache::remember('university_domains.active_list', 3600, function () {
            return static::where('is_active', true)
                ->pluck('domain')
                ->map(fn ($d) => static::normalizeDomainString((string) $d))
                ->values()
                ->all();
        });
    }

    public static function flushDomainCache(): void
    {
        try {
            Cache::forget('university_domains.active_list');
            Cache::forget('university_domains.public_list');
            Cache::forget('public.university_domains');
        } catch (\Throwable) {}
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushDomainCache());
        static::deleted(fn () => static::flushDomainCache());
    }
}
