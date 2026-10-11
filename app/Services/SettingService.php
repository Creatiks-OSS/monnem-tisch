<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public function get(string $key, mixed $default = null, string $group = 'general'): mixed
    {
        return Setting::get($key, $default, $group);
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        Setting::set($key, $value, $group, $type);
    }

    public function string(string $key, string $default = '', string $group = 'general'): string
    {
        return (string) $this->get($key, $default, $group);
    }

    public function bool(string $key, bool $default = false, string $group = 'general'): bool
    {
        $value = $this->get($key, $default, $group);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function int(string $key, int $default = 0, string $group = 'general'): int
    {
        return (int) $this->get($key, $default, $group);
    }

    public function float(string $key, float $default = 0.0, string $group = 'general'): float
    {
        return (float) $this->get($key, $default, $group);
    }

    public function json(string $key, array $default = [], string $group = 'general'): array
    {
        $value = $this->get($key, $default, $group);

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : $default;
        }

        return $default;
    }

    public function color(string $key, string $default = '#000000', string $group = 'theme'): string
    {
        $value = trim((string) $this->get($key, $default, $group));

        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $value)) {
            return $value;
        }

        return $default;
    }

    /**
     * @return array<string, string>
     */
    public function colorTheme(): array
    {
        return [
            'primary' => $this->color('primary', '#1a3a2a', 'theme'),
            'secondary' => $this->color('secondary', '#c4a35a', 'theme'),
            'accent' => $this->color('accent', '#8b4513', 'theme'),
            'background' => $this->color('background', '#f7f4ef', 'theme'),
            'text' => $this->color('text', '#1a1a1a', 'theme'),
            'muted' => $this->color('muted', '#6b6b6b', 'theme'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function group(string $group): array
    {
        if (method_exists(Setting::class, 'group')) {
            return Setting::group($group);
        }

        return Setting::query()
            ->where('group', $group)
            ->pluck('value', 'key')
            ->all();
    }

    public function forgetCache(?string $group = null): void
    {
        if ($group) {
            Cache::forget("settings.group.{$group}");
            Setting::query()
                ->where('group', $group)
                ->pluck('key')
                ->each(fn ($key) => Cache::forget("settings.{$group}.{$key}"));

            return;
        }

        Cache::flush();
    }
}
