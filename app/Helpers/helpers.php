<?php

use App\Models\Setting;
use App\Services\SettingService;

if (! function_exists('__t')) {
    function __t(string $key, ?string $fallback = null, array $replace = []): string
    {
        $translated = trans($key, $replace);

        if (! is_string($translated) || $translated === $key) {
            return $fallback ?? $key;
        }

        return $translated;
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null, string $group = 'general'): mixed
    {
        try {
            return Setting::get($key, $default, $group);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (! function_exists('setting_bool')) {
    function setting_bool(string $key, bool $default = false, string $group = 'general'): bool
    {
        return filter_var(setting($key, $default, $group), FILTER_VALIDATE_BOOLEAN);
    }
}

if (! function_exists('setting_int')) {
    function setting_int(string $key, int $default = 0, string $group = 'general'): int
    {
        return (int) setting($key, $default, $group);
    }
}

if (! function_exists('format_price')) {
    function format_price(float|int|string|null $amount): string
    {
        $value = (float) ($amount ?? 0);

        return number_format($value, 2, ',', '.').' €';
    }
}

if (! function_exists('public_media_normalize')) {
    function public_media_normalize(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        if (preg_match('#^https?://[^/]+(/.*)$#i', $path, $m)) {
            $path = $m[1];
        }
        $parsed = parse_url($path, PHP_URL_PATH);
        if (is_string($parsed) && $parsed !== '') {
            $path = $parsed;
        }
        $path = ltrim($path, '/');
        foreach (['public/storage/', 'storage/', 'media/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
                break;
            }
        }

        return ltrim($path, '/');
    }
}

if (! function_exists('public_media_candidates')) {
    /**
     * @return list<string>
     */
    function public_media_candidates(string $relative): array
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        $out = [$relative];
        if (preg_match('/\.(png|jpe?g|gif)$/i', $relative)) {
            $out[] = (string) preg_replace('/\.(png|jpe?g|gif)$/i', '.webp', $relative);
        } elseif (preg_match('/\.webp$/i', $relative)) {
            foreach (['png', 'jpg', 'jpeg', 'gif'] as $ext) {
                $out[] = (string) preg_replace('/\.webp$/i', '.'.$ext, $relative);
            }
        }

        $extra = [];
        foreach ($out as $candidate) {
            if (! str_starts_with($candidate, 'images/')) {
                $extra[] = 'images/'.$candidate;
            }
        }

        return array_values(array_unique([...$out, ...$extra]));
    }
}

if (! function_exists('public_media_file')) {
    function public_media_file(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '' || str_contains($path, '..')) {
            return null;
        }

        $relative = public_media_normalize($path);
        if ($relative === '' || str_contains($relative, '..')) {
            return null;
        }

        $roots = array_values(array_filter([
            realpath(storage_path('app/public')),
            realpath(public_path('storage')),
            realpath(public_path()),
        ]));

        foreach (public_media_candidates($relative) as $candidate) {
            foreach ([
                storage_path('app/public/'.$candidate),
                public_path('storage/'.$candidate),
                public_path($candidate),
            ] as $full) {
                $real = realpath($full);
                if ($real === false || ! is_file($real)) {
                    continue;
                }
                foreach ($roots as $root) {
                    $prefix = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
                    if ($real === $root || str_starts_with($real, $prefix)) {
                        return $real;
                    }
                }
            }
        }

        return null;
    }
}

if (! function_exists('public_media_url')) {
    function public_media_url(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            if (! str_contains($path, '/images/') && ! str_contains($path, '/storage/') && ! str_contains($path, '/media/')) {
                return $path;
            }
        }

        $relative = public_media_normalize($path);
        $file = public_media_file($path);
        $version = $file ? (string) filemtime($file) : null;

        if ($file !== null) {
            $storageRoot = realpath(storage_path('app/public'));
            $publicStorage = realpath(public_path('storage'));
            $publicRoot = realpath(public_path());
            $real = $file;
            $web = $relative;

            if ($storageRoot && str_starts_with($real, rtrim($storageRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR)) {
                $web = str_replace('\\', '/', substr($real, strlen($storageRoot) + 1));
            } elseif ($publicStorage && str_starts_with($real, rtrim($publicStorage, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR)) {
                $web = str_replace('\\', '/', substr($real, strlen($publicStorage) + 1));
            } elseif ($publicRoot && str_starts_with($real, rtrim($publicRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR)) {
                $web = str_replace('\\', '/', substr($real, strlen($publicRoot) + 1));
                if (str_starts_with($web, 'storage/')) {
                    $web = substr($web, strlen('storage/'));
                } else {
                    $encoded = implode('/', array_map('rawurlencode', explode('/', $web)));

                    return '/'.$encoded.($version ? '?v='.$version : '');
                }
            }

            $encoded = implode('/', array_map('rawurlencode', explode('/', $web)));

            return '/media/'.$encoded.($version ? '?v='.$version : '');
        }

        if (str_starts_with($relative, 'images/')) {
            return '/'.$relative;
        }

        return '/media/'.$relative;
    }
}

if (! function_exists('content_image_path')) {
    function content_image_path(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return $path;
        }
        if (preg_match('/logo|favicon|apple-touch-icon/i', $path)) {
            return $path;
        }
        if (str_contains($path, '://') && ! str_contains($path, '/images/') && ! str_contains($path, '/storage/') && ! str_contains($path, '/media/')) {
            return $path;
        }

        return public_media_url($path) ?? $path;
    }
}

if (! function_exists('extra_price_suffix')) {
    function extra_price_suffix(?string $type): string
    {
        return match ($type) {
            'person' => '/ Pers.',
            'stück' => '/ Stk.',
            'hour' => '/ Std.',
            default => '',
        };
    }
}

if (! function_exists('shop_tax_rate')) {
    function shop_tax_rate(): float
    {
        foreach (['shop', 'business', 'general'] as $group) {
            $value = setting('tax_rate', null, $group);
            if ($value !== null && $value !== '') {
                return (float) $value;
            }
        }

        return 7.0;
    }
}

if (! function_exists('shop_standard_tax_rate')) {
    function shop_standard_tax_rate(): float
    {
        foreach (['shop', 'business', 'general'] as $group) {
            $value = setting('tax_rate_standard', null, $group);
            if ($value !== null && $value !== '') {
                return (float) $value;
            }
        }

        return 19.0;
    }
}

if (! function_exists('format_mwst_percent')) {
    function format_mwst_percent(float|int|string|null $rate): string
    {
        return rtrim(rtrim(number_format((float) $rate, 2, ',', '.'), '0'), ',');
    }
}

if (! function_exists('tax_display_lines')) {
    /**
     * @param  object|array<string, mixed>  $source
     * @return list<array{rate: float, amount: float}>
     */
    function tax_display_lines(object|array $source): array
    {
        $get = function (string $key, mixed $default = 0) use ($source): mixed {
            if (is_array($source)) {
                return $source[$key] ?? $default;
            }

            return $source->{$key} ?? $default;
        };

        $reducedRate = (float) $get('tax_rate', shop_tax_rate());
        $reducedAmount = (float) $get('tax_amount', 0);
        $standardRate = (float) $get('tax_standard_rate', shop_standard_tax_rate());
        $standardAmount = (float) $get('tax_standard_amount', 0);

        $lines = [];
        if ($reducedAmount > 0 || $standardAmount <= 0) {
            $lines[] = ['rate' => $reducedRate, 'amount' => $reducedAmount];
        }
        if ($standardAmount > 0) {
            $lines[] = ['rate' => $standardRate, 'amount' => $standardAmount];
        }

        return $lines;
    }
}

if (! function_exists('setting_color')) {
    function setting_color(string $key, string $default = '#000000', string $group = 'theme'): string
    {
        try {
            return app(SettingService::class)->color($key, $default, $group);
        } catch (\Throwable) {
            $value = trim((string) setting($key, $default, $group));

            return preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $value)
                ? $value
                : $default;
        }
    }
}

if (! function_exists('setting_color_theme')) {
    /**
     * @return array{primary: string, secondary: string, accent: string, background: string, text: string, muted: string}
     */
    function setting_color_theme(): array
    {
        try {
            return app(SettingService::class)->colorTheme();
        } catch (\Throwable) {
            return [
                'primary' => setting_color('primary', '#1a3a2a', 'theme'),
                'secondary' => setting_color('secondary', '#c4a35a', 'theme'),
                'accent' => setting_color('accent', '#8b4513', 'theme'),
                'background' => setting_color('background', '#f7f4ef', 'theme'),
                'text' => setting_color('text', '#1a1a1a', 'theme'),
                'muted' => setting_color('muted', '#6b6b6b', 'theme'),
            ];
        }
    }
}

if (! function_exists('admin_label')) {
    function admin_label(?string $value): string
    {
        $map = [
            'neu' => 'Yeni',
            'bestätigt' => 'Onaylandı',
            'in_bearbeitung' => 'İşleniyor',
            'vorbereitung' => 'Hazırlık',
            'unterwegs' => 'Yolda',
            'geliefert' => 'Teslim edildi',
            'abgeschlossen' => 'Tamamlandı',
            'storniert' => 'İptal',
            'kontaktiert' => 'İletişime geçildi',
            'angebot_erstellt' => 'Teklif oluşturuldu',
            'verhandlung' => 'Görüşme',
            'gewonnen' => 'Kazanıldı',
            'verloren' => 'Kaybedildi',
            'offen' => 'Açık',
            'bezahlt' => 'Ödendi',
            'überfällig' => 'Vadesi geçti',
            'paid' => 'Ödendi',
            'failed' => 'Başarısız',
            'refunded' => 'İade',
            'pending' => 'Beklemede',
            'draft' => 'Taslak',
            'sent' => 'Gönderildi',
            'cancelled' => 'İptal',
            'approved' => 'Onaylı',
            'featured' => 'Öne çıkan',
        ];

        return $map[$value ?? ''] ?? (string) $value;
    }
}

if (! function_exists('velora_photo')) {
    function velora_photo(string $key = 'hero'): string
    {
        $photos = [
            'hero' => asset('images/hero-buffet-monnemtisch.webp'),
            'table' => asset('images/table-monnemtisch.webp'),
            'buffet' => asset('images/packages/package-buffet-mega-l.webp'),
            'fingerfood' => asset('images/packages/package-fingerfood-deluxe.webp'),
            'wedding' => 'https://images.unsplash.com/photo-1519225421980-715cb0211287?auto=format&fit=crop&w=1200&q=80',
            'grill' => asset('images/packages/package-grill-deluxe.webp'),
            'dessert' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=900&q=80',
            'salad' => asset('images/packages/package-vegetarisch.webp'),
            'vegetarisch' => asset('images/packages/package-vegetarisch.webp'),
            'vegan' => asset('images/packages/package-vegan.webp'),
            'pasta' => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?auto=format&fit=crop&w=900&q=80',
            'meat' => 'https://images.unsplash.com/photo-1432139555190-58524dae6a55?auto=format&fit=crop&w=900&q=80',
            'breakfast' => asset('images/packages/package-brunch-deluxe.webp'),
            'kitchen' => asset('images/hero-monnemtisch.webp'),
            'cheers' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=900&q=80',
            'plates' => 'https://images.unsplash.com/photo-1578500494198-246f594d3b3b?auto=format&fit=crop&w=400&q=80',
            'cutlery' => 'https://images.unsplash.com/photo-1590794056227-75ba52eac0b5?auto=format&fit=crop&w=400&q=80',
            'staff' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=400&q=80',
            'setup' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=400&q=80',
            'teardown' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=400&q=80',
            'decor' => 'https://images.unsplash.com/photo-1478144592103-25e1a86ff6e1?auto=format&fit=crop&w=400&q=80',
            'chafing' => asset('images/hero-buffet-monnemtisch.webp'),
            'chef' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=400&q=80',
            'glasses' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=400&q=80',
            'linen' => 'https://images.unsplash.com/photo-1533777857889-4be7c70b33f7?auto=format&fit=crop&w=400&q=80',
        ];

        return $photos[$key] ?? $photos['hero'];
    }
}

if (! function_exists('velora_drink_photo')) {
    function velora_drink_photo(?string $name): ?string
    {
        $key = strtolower((string) $name);
        $photos = [
            'wasser' => 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?auto=format&fit=crop&w=900&q=80',
            'zero' => 'https://images.unsplash.com/photo-1629203851127-3726b0c1f5c8?auto=format&fit=crop&w=900&q=80',
            'bergamotte' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?auto=format&fit=crop&w=900&q=80',
            'holunder' => 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=900&q=80',
            'apfel' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=900&q=80',
            'orange' => 'https://images.unsplash.com/photo-1587486913049-53fc88980cfc?auto=format&fit=crop&w=900&q=80',
            'sprite' => 'https://images.unsplash.com/photo-1625772299848-391b6a87d7b3?auto=format&fit=crop&w=900&q=80',
            'fanta' => 'https://images.unsplash.com/photo-1624517452488-04869289c4ca?auto=format&fit=crop&w=900&q=80',
            'cola' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&w=900&q=80',
        ];

        foreach ($photos as $needle => $url) {
            if (str_contains($key, $needle)) {
                return $url;
            }
        }

        return null;
    }
}

if (! function_exists('velora_photo_for')) {
    function velora_photo_for(?string $name, int $index = 0): string
    {
        $drink = velora_drink_photo($name);
        if ($drink) {
            return $drink;
        }

        $slug = strtolower((string) $name);
        $map = [
            'geschirr' => 'plates',
            'besteck' => 'cutlery',
            'personal' => 'staff',
            'servicekraft' => 'staff',
            'aufbau' => 'setup',
            'abbau' => 'teardown',
            'tischdekoration' => 'decor',
            'warmhalte' => 'chafing',
            'live cooking' => 'chef',
            'gläser' => 'glasses',
            'glaeser' => 'glasses',
            'buffetausstattung' => 'linen',
            'buffet' => 'buffet',
            'finger' => 'fingerfood',
            'hochzeit' => 'wedding',
            'grill' => 'grill',
            'dessert' => 'dessert',
            'frühstück' => 'breakfast',
            'frueh' => 'breakfast',
            'vegetar' => 'vegetarisch',
            'vegan' => 'vegan',
            'extra' => 'dessert',
            'getränk' => 'cheers',
        ];
        foreach ($map as $needle => $key) {
            if (str_contains($slug, $needle)) {
                return velora_photo($key);
            }
        }
        $cycle = ['table', 'salad', 'pasta', 'meat', 'dessert', 'grill'];

        return velora_photo($cycle[$index % count($cycle)]);
    }
}

if (! function_exists('velora_media_url')) {
    function velora_media_url(?string $path, ?string $name = null, int $id = 0): string
    {
        $path = trim((string) $path);
        if ($path !== '' && ! str_contains($path, 'placeholders')) {
            $url = public_media_url($path);
            if ($url) {
                return $url;
            }
        }

        return velora_photo_for($name, $id);
    }
}

if (! function_exists('site_logo_file')) {
    function site_logo_file(): ?string
    {
        $path = trim((string) setting('logo', '', 'general'));
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }

        $relative = ltrim(str_replace('\\', '/', $path), '/');
        if (str_contains($relative, '..')) {
            return null;
        }
        if (str_starts_with($relative, 'storage/')) {
            $relative = substr($relative, strlen('storage/'));
        }

        $roots = array_values(array_filter([
            realpath(storage_path('app/public')),
            realpath(public_path('storage')),
            realpath(public_path()),
        ]));

        foreach ([
            storage_path('app/public/'.$relative),
            public_path('storage/'.$relative),
            public_path($relative),
        ] as $full) {
            $real = realpath($full);
            if ($real === false || ! is_file($real)) {
                continue;
            }

            foreach ($roots as $root) {
                $prefix = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
                if ($real === $root || str_starts_with($real, $prefix)) {
                    return $real;
                }
            }
        }

        return null;
    }
}

if (! function_exists('site_logo_url')) {
    function site_logo_url(): ?string
    {
        $path = trim((string) setting('logo', '', 'general'));
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $file = site_logo_file();
        if ($file === null) {
            return null;
        }

        $version = (string) filemtime($file);

        try {
            if (! app()->runningInConsole() && app()->bound('request')) {
                $request = request();
                if ($request->getHost()) {
                    return rtrim($request->getSchemeAndHttpHost().$request->getBasePath(), '/')
                        .'/branding/logo?v='.$version;
                }
            }
        } catch (\Throwable) {
        }

        try {
            return route('branding.logo', ['v' => $version]);
        } catch (\Throwable) {
            return asset('storage/'.ltrim(str_replace('\\', '/', $path), '/')).'?v='.$version;
        }
    }
}

if (! function_exists('site_logo_mail_src')) {
    function site_logo_mail_src(mixed $message = null): ?string
    {
        $file = site_logo_file();
        if ($file === null) {
            return null;
        }

        $png = site_logo_mail_png($file);
        $bytes = $png ?? (string) file_get_contents($file);
        if ($bytes === '') {
            return null;
        }

        if (is_object($message) && method_exists($message, 'embedData')) {
            try {
                return $message->embedData($bytes, 'logo.png', 'image/png');
            } catch (\Throwable) {
                return null;
            }
        }

        return 'data:image/png;base64,'.base64_encode($bytes);
    }
}

if (! function_exists('site_logo_mail_png')) {
    function site_logo_mail_png(string $file): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $raw = @file_get_contents($file);
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $src = @imagecreatefromstring($raw);
        if ($src === false) {
            return null;
        }

        $width = imagesx($src);
        $height = imagesy($src);
        $max = 320;
        if ($width > $max && $width > 0) {
            $newHeight = max(1, (int) round($height * ($max / $width)));
            $dst = imagecreatetruecolor($max, $newHeight);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $max, $newHeight, $transparent);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $max, $newHeight, $width, $height);
            imagedestroy($src);
            $src = $dst;
        } else {
            imagealphablending($src, false);
            imagesavealpha($src, true);
        }

        ob_start();
        imagepng($src, null, 6);
        $out = ob_get_clean();
        imagedestroy($src);

        return is_string($out) && $out !== '' ? $out : null;
    }
}

if (! function_exists('mail_image_src')) {
    function mail_image_src(mixed $message, ?string $url, string $cidName = 'item.jpg'): ?string
    {
        $url = trim((string) $url);
        $file = mail_image_local_path($url);
        $bytes = $file ? mail_image_jpeg($file) : null;

        if (is_string($bytes) && $bytes !== '' && is_object($message) && method_exists($message, 'embedData')) {
            try {
                return $message->embedData($bytes, $cidName, 'image/jpeg');
            } catch (\Throwable) {
            }
        }

        if ($url !== '') {
            return mail_public_url($url);
        }

        return null;
    }
}

if (! function_exists('mail_image_local_path')) {
    function mail_image_local_path(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $relative = ltrim(str_replace('\\', '/', is_string($path) && $path !== '' ? $path : $url), '/');
        $relative = preg_replace('#^(public/)+#', '', $relative) ?: $relative;

        if ($relative === '' || str_contains($relative, '..')) {
            return null;
        }

        $storageRelative = preg_replace('#^storage/#', '', $relative) ?: $relative;

        foreach ([
            public_path($relative),
            public_path('storage/'.$storageRelative),
            storage_path('app/public/'.$storageRelative),
        ] as $full) {
            $real = realpath($full);
            if ($real !== false && is_file($real)) {
                return $real;
            }
        }

        return null;
    }
}

if (! function_exists('mail_image_jpeg')) {
    function mail_image_jpeg(string $file, int $size = 96): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return null;
        }

        $raw = @file_get_contents($file);
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $src = @imagecreatefromstring($raw);
        if ($src === false) {
            return null;
        }

        $width = imagesx($src);
        $height = imagesy($src);
        if ($width < 1 || $height < 1) {
            imagedestroy($src);

            return null;
        }

        $side = min($width, $height);
        $srcX = (int) floor(($width - $side) / 2);
        $srcY = (int) floor(($height - $side) / 2);
        $dst = imagecreatetruecolor($size, $size);
        $bg = imagecolorallocate($dst, 247, 243, 235);
        imagefilledrectangle($dst, 0, 0, $size, $size, $bg);
        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $size, $size, $side, $side);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, 82);
        $out = ob_get_clean();
        imagedestroy($dst);

        return is_string($out) && $out !== '' ? $out : null;
    }
}

if (! function_exists('mail_public_url')) {
    function mail_public_url(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return $url;
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        $host = parse_url($url, PHP_URL_HOST);
        if (is_string($host) && in_array($host, ['127.0.0.1', 'localhost', '::1'], true) && $appUrl !== '') {
            $path = parse_url($url, PHP_URL_PATH) ?: '/';
            $query = parse_url($url, PHP_URL_QUERY);

            return $appUrl.$path.($query ? '?'.$query : '');
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return $appUrl.'/'.ltrim($url, '/');
    }
}

if (! function_exists('site_logo_data_uri')) {
    function site_logo_data_uri(): ?string
    {
        $path = trim((string) setting('logo', '', 'general'));
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path !== '' ? $path : null;
        }

        $full = site_logo_file();
        if ($full === null) {
            return null;
        }

        $mime = @mime_content_type($full) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($full));
    }
}
