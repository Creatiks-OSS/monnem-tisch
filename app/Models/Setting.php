<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    public static function get(string $key, mixed $default = null, string $group = 'general'): mixed
    {
        $cacheKey = static::cacheKey($group, $key);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($key, $default, $group) {
            $setting = static::query()
                ->where('group', $group)
                ->where('key', $key)
                ->first();

            if (! $setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        $stored = static::serializeValue($value, $type);

        static::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $stored, 'type' => $type]
        );

        Cache::forget(static::cacheKey($group, $key));
        Cache::forget(static::groupCacheKey($group));
    }

    public static function group(string $group): array
    {
        return Cache::remember(static::groupCacheKey($group), now()->addDay(), function () use ($group) {
            return static::query()
                ->where('group', $group)
                ->get()
                ->mapWithKeys(fn (Setting $setting) => [
                    $setting->key => static::castValue($setting->value, $setting->type),
                ])
                ->all();
        });
    }

    protected static function cacheKey(string $group, string $key): string
    {
        return "settings.{$group}.{$key}";
    }

    protected static function groupCacheKey(string $group): string
    {
        return "settings.group.{$group}";
    }

    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $value,
            'float', 'double', 'decimal' => (float) $value,
            'array', 'json' => is_array($value) ? $value : (json_decode((string) $value, true) ?? []),
            default => $value,
        };
    }

    protected static function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean', 'bool' => $value ? '1' : '0',
            'array', 'json' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };
    }
}
