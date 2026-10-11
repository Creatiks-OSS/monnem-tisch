<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductImageMatcher
{
    /**
     * @var array<string, string>
     */
    protected array $aliases = [
        'mini-cheese-burger' => 'mini-cheeseburger',
        'ceasar-salad-im-glas' => 'caesar-salad-im-glas',
        'lach-ei-wrap' => 'lachs-ei-wrap',
        'lachs-brokkoli-auflauf' => 'lachs-brokkoli-auflauf',
        'ciabatta-pastrami-kase' => 'ciabatta-pastrami-kase',
        'ciabatta-tomate-mozzarella' => 'ciabatta-tomate-mozzarella',
        'erdbeer-tiramisu' => 'erdbeer-tiramisu',
        'himbeer-mascarponecreme' => 'himbeer-mascarponecreme',
        'grosse-kaseplatte' => 'grosse-kaseplatte',
        'babas-knusperplatte' => 'babas-knusperplatte',
    ];

    /**
     * @return array{copied: int, matched: int, products: int, unmatched_files: list<string>}
     */
    public function sync(?string $sourceDir = null, ?string $publicDir = null): array
    {
        $sourceDir = $sourceDir ?: base_path('images');
        $publicDir = $publicDir ?: public_path('images/dishes');

        File::ensureDirectoryExists($publicDir);

        $byKey = [];
        $copied = 0;
        $unmatched = [];

        foreach ($this->files($sourceDir) as $path) {
            $filename = pathinfo($path, PATHINFO_FILENAME);
            if ($this->shouldSkip($filename)) {
                continue;
            }

            $key = $this->canonicalKey($filename);
            if ($key === '') {
                continue;
            }

            $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
            $destName = $key.'.'.($ext === 'jpeg' ? 'jpg' : $ext);
            $destPath = $publicDir.DIRECTORY_SEPARATOR.$destName;
            $webpName = $key.'.webp';
            $webpPath = $publicDir.DIRECTORY_SEPARATOR.$webpName;

            if ($ext !== 'webp' && ! is_file($webpPath)) {
                if (! is_file($destPath) || filesize($destPath) !== filesize($path)) {
                    File::copy($path, $destPath);
                    $copied++;
                }
            }

            $url = is_file($webpPath)
                ? '/images/dishes/'.$webpName
                : '/images/dishes/'.$destName;
            if (isset($byKey[$key]) && str_ends_with($byKey[$key], '.webp') && ! str_ends_with($url, '.webp')) {
                continue;
            }
            $byKey[$key] = $url;
        }

        $matched = 0;
        $products = Product::query()->withTrashed()->get();

        foreach ($products as $product) {
            $key = $this->canonicalKey($product->name);
            $url = $byKey[$key] ?? null;
            if ($url === null) {
                continue;
            }

            if ($product->image !== $url) {
                $product->image = $url;
                $product->saveQuietly();
            }
            $matched++;
        }

        foreach ($byKey as $key => $url) {
            $used = $products->contains(fn (Product $product) => $this->canonicalKey($product->name) === $key);
            if (! $used) {
                $unmatched[] = $key;
            }
        }

        return [
            'copied' => $copied,
            'matched' => $matched,
            'products' => $products->count(),
            'unmatched_files' => $unmatched,
        ];
    }

    public function canonicalKey(string $value): string
    {
        $value = pathinfo($value, PATHINFO_FILENAME);
        $value = preg_replace('/_2K_\d{8,14}$/u', '', $value) ?? $value;
        $value = preg_replace('/_\d{12,14}$/u', '', $value) ?? $value;
        $value = str_replace(['_', '′', '’', "'", '´'], [' ', '', '', '', ''], $value);
        $value = strtr($value, [
            'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue',
            'Ä' => 'ae', 'Ö' => 'oe', 'Ü' => 'ue',
            'ß' => 'ss', 'é' => 'e', 'è' => 'e',
        ]);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s*-\s*/u', '-', trim($value)) ?? $value;
        $slug = Str::slug($value);
        $slug = str_replace('mini-cheese-burger', 'mini-cheeseburger', $slug);

        return $this->aliases[$slug] ?? $slug;
    }

    /**
     * @return list<string>
     */
    protected function files(string $sourceDir): array
    {
        if (! is_dir($sourceDir)) {
            return [];
        }

        $out = [];
        foreach (File::files($sourceDir) as $file) {
            if (in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
                $out[] = $file->getPathname();
            }
        }

        return $out;
    }

    protected function shouldSkip(string $filename): bool
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-/i', $filename)) {
            return true;
        }

        $lower = mb_strtolower($filename);

        foreach ([
            'chatgpt',
            'coca_cola',
            'sprite_',
            'fanta_',
            'bionade',
            'apfelsaft',
            'kaffee_',
            'kakao_',
            'tee 3l',
            'sadece_',
            'einweg',
            'mega saucen',
            'pauschale',
            'ketchup',
            'warme saucen',
        ] as $skip) {
            if (str_contains($lower, $skip)) {
                return true;
            }
        }

        return false;
    }
}
