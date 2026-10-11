<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CateringPackage extends Model
{
    use HasFactory, HasSeo, SoftDeletes;

    protected $fillable = [
        'event_type_id',
        'name',
        'slug',
        'subtitle',
        'description',
        'seo_text',
        'image',
        'gallery',
        'base_price',
        'price_per_person',
        'compare_price',
        'discount_label',
        'badge',
        'highlights',
        'cta_label',
        'trust_line',
        'menu_group',
        'min_persons',
        'max_persons',
        'is_featured',
        'is_popular',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'price_per_person' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'min_persons' => 'integer',
            'max_persons' => 'integer',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'gallery' => 'array',
            'highlights' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CateringPackage $package) {
            if (empty($package->slug) && ! empty($package->name)) {
                $package->slug = Str::slug($package->name);
            }
        });
    }

    public const MENUS = [
        'buffets' => [
            'label' => 'Buffets',
            'path' => 'buffets',
            'photo' => 'buffet',
            'headline' => 'Buffet Catering Mannheim ab 10 Personen',
            'seo_title' => 'Buffet Catering Mannheim ab 10 Personen',
            'intro' => 'Buffet Catering in Mannheim ab 10 Personen – Festpreis, frei kombinierbar, unverbindlich anfragen.',
        ],
        'fingerfood' => [
            'label' => 'Fingerfood',
            'path' => 'fingerfood',
            'photo' => 'fingerfood',
            'headline' => 'Fingerfood Catering Mannheim für Firmen & Feiern',
            'seo_title' => 'Fingerfood Catering Mannheim für Firmen & Feiern',
            'intro' => 'Fingerfood Catering in Mannheim für Firmenfeiern, Empfänge und private Events – Häppchen zum Festpreis.',
        ],
        'fruehstueck' => [
            'label' => 'Frühstück',
            'path' => 'fruehstueck',
            'photo' => 'breakfast',
            'headline' => 'Frühstück Catering Mannheim fürs Büro',
            'seo_title' => 'Frühstück Catering Mannheim fürs Büro',
            'intro' => 'Frühstück Catering fürs Büro in Mannheim – frische Pakete, pünktliche Lieferung, Festpreis.',
        ],
        'grillcatering' => [
            'label' => 'Grillcatering',
            'path' => 'grillcatering',
            'photo' => 'grill',
            'headline' => 'Grillcatering Mannheim für Events',
            'seo_title' => 'Grillcatering Mannheim für Events',
            'intro' => 'Grillcatering in Mannheim für Firmenfeste und Events – live am Grill, frei kombinierbar.',
        ],
        'vegetarisch' => [
            'label' => 'Vegetarisch',
            'path' => 'vegetarisch',
            'photo' => 'vegetarisch',
            'headline' => 'Vegetarisches Catering Mannheim',
            'seo_title' => 'Vegetarisches Catering Mannheim – Buffet & Fingerfood',
            'intro' => 'Vegetarisches Catering in Mannheim aus der bestehenden Speisekarte – Festpreis, frei kombinierbar.',
        ],
        'vegan' => [
            'label' => 'Vegan',
            'path' => 'vegan',
            'photo' => 'vegan',
            'headline' => 'Veganes Catering Mannheim',
            'seo_title' => 'Veganes Catering Mannheim – pflanzliche Menüs',
            'intro' => 'Veganes Catering in Mannheim – nur pflanzliche Speisen aus der Karte, Festpreis, frei kombinierbar.',
        ],
    ];

    public static function menuKeys(): array
    {
        return array_keys(self::MENUS);
    }

    public static function menuMetaFor(?string $group): array
    {
        $group = $group && isset(self::MENUS[$group]) ? $group : 'buffets';

        return array_merge(['key' => $group], self::MENUS[$group]);
    }

    public function menuMeta(): array
    {
        return self::menuMetaFor($this->menu_group);
    }

    public function documentTitle(): string
    {
        $local = match ($this->menu_group) {
            'fingerfood' => 'Fingerfood Catering Mannheim',
            'fruehstueck' => 'Frühstück Catering Mannheim',
            'grillcatering' => 'Grillcatering Mannheim',
            'vegetarisch' => 'Vegetarisches Catering Mannheim',
            'vegan' => 'Veganes Catering Mannheim',
            default => 'Buffet Catering Mannheim',
        };

        return $this->name.' – '.$local;
    }

    public function publicUrl(): string
    {
        return route('packages.show', ['menu' => $this->menu_group ?: 'buffets', 'slug' => $this->slug]);
    }

    public function cartUrl(): string
    {
        return route('packages.cart', ['menu' => $this->menu_group ?: 'buffets', 'slug' => $this->slug]);
    }

    public function scopeForMenu(Builder $query, string $menu): Builder
    {
        return $query->where('menu_group', $menu);
    }

    public static function isMenu(string $key): bool
    {
        return isset(self::MENUS[$key]);
    }

    public static function menuKeyFromSlug(?string $slug): string
    {
        if ($slug && isset(self::MENUS[$slug])) {
            return $slug;
        }

        return match ($slug) {
            'getraenke', 'extras' => 'buffets',
            default => 'buffets',
        };
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(PackageSlot::class, 'package_id')->orderBy('sort_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackageItem::class, 'package_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'package_items', 'package_id', 'product_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->where('is_popular', true);
    }

    public function currentPrice(): float
    {
        return (float) $this->price_per_person;
    }

    public function hasDiscount(): bool
    {
        return $this->compare_price && (float) $this->compare_price > (float) $this->price_per_person;
    }

    public function discountPercent(): ?int
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return (int) round((1 - ((float) $this->price_per_person / (float) $this->compare_price)) * 100);
    }

    public function imageUrl(): string
    {
        $image = $this->image;
        if (! $image || str_contains((string) $image, 'placeholders')) {
            return velora_photo($this->menuMeta()['photo'] ?? 'buffet');
        }
        $image = content_image_path($image);
        if (str_starts_with($image, 'http') || str_starts_with($image, '/')) {
            return $image;
        }

        return public_media_url($image) ?: velora_photo($this->menuMeta()['photo'] ?? 'buffet');
    }

    public function galleryUrls(): array
    {
        $items = $this->gallery ?? [];
        $urls = [];
        foreach ($items as $path) {
            if (! $path || str_contains((string) $path, 'placeholders')) {
                continue;
            }
            $path = content_image_path($path);
            $urls[] = str_starts_with($path, 'http') || str_starts_with($path, '/')
                ? $path
                : (public_media_url($path) ?: $path);
        }

        if ($urls === []) {
            $urls = [velora_photo('salad'), velora_photo('pasta'), velora_photo('dessert')];
        }

        return $urls;
    }
}
