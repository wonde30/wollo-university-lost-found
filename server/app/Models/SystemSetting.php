<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property string      $key
 * @property string|null $value
 * @property string      $type
 * @property string|null $display_name
 * @property string|null $description
 * @property bool        $is_public
 * @property bool        $is_editable
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'display_name',
        'description',
        'is_public',
        'is_editable',
    ];

    protected function casts(): array
    {
        return [
            'is_public'   => 'boolean',
            'is_editable' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  In-process cache for fast repeated lookups                         */
    /* ------------------------------------------------------------------ */

    protected static array $settingsCache = [];

    /**
     * Retrieve a typed setting value from the database, with in-process caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$settingsCache)) {
            return static::$settingsCache[$key];
        }

        try {
            $setting = static::where('key', $key)->first();
            if (! $setting) {
                return $default;
            }

            $value = match ($setting->type) {
                'integer', 'int'   => (int) $setting->value,
                'boolean', 'bool'  => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json', 'array'    => json_decode($setting->value, true),
                'float', 'double'  => (float) $setting->value,
                default            => $setting->value,
            };

            static::$settingsCache[$key] = $value;
            return $value;
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function flushCache(): void
    {
        static::$settingsCache = [];
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
