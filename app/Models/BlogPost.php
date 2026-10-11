<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory, HasSeo, SoftDeletes;

    protected $fillable = [
        'blog_category_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'is_published',
        'published_at',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'views' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BlogPost $post) {
            if (empty($post->slug) && ! empty($post->title)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blog_post_tag')
            ->withTimestamps();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function imageUrl(): ?string
    {
        $image = $this->featured_image;
        if (! $image) {
            return null;
        }
        $image = content_image_path($image);
        if (str_starts_with($image, 'http') || str_starts_with($image, '/')) {
            return $image;
        }

        return public_media_url($image);
    }

    public function imageAbsoluteUrl(): ?string
    {
        $url = $this->imageUrl();
        if (! $url) {
            return null;
        }

        return str_starts_with($url, 'http') ? $url : url($url);
    }

    public function authorName(): string
    {
        $name = trim((string) ($this->author?->name ?? ''));
        if ($name === '' || preg_match('/^(admin|test|user|administrator)$/i', $name)) {
            return 'Monnemtisch Küche';
        }

        return $name;
    }

    public function authorRole(): string
    {
        return 'Catering-Team Mannheim';
    }

    /**
     * @return array{key: string, label: string, url: string, intro: string}
     */
    public function relatedMenu(): array
    {
        $hay = Str::lower($this->slug.' '.$this->title.' '.$this->tags->pluck('name')->implode(' '));

        $key = 'buffets';
        if (str_contains($hay, 'grill') || str_contains($hay, 'bbq') || str_contains($hay, 'outdoor') || str_contains($hay, 'garten')) {
            $key = 'grillcatering';
        } elseif (str_contains($hay, 'frueh') || str_contains($hay, 'früh') || str_contains($hay, 'brunch') || str_contains($hay, 'coffee')) {
            $key = 'fruehstueck';
        } elseif (
            str_contains($hay, 'fingerfood')
            || str_contains($hay, 'häppchen')
            || str_contains($hay, 'haeppchen')
            || str_contains($hay, 'sektempfang')
            || str_contains($hay, 'messe')
            || str_contains($hay, 'empfang')
        ) {
            $key = 'fingerfood';
        }

        $meta = CateringPackage::menuMetaFor($key);

        return [
            'key' => $key,
            'label' => $meta['label'] ?? 'Buffets',
            'url' => route('packages.index', $key),
            'intro' => $meta['intro'] ?? 'Zum Festpreis, frei kombinierbar.',
        ];
    }

    public function wordCount(): int
    {
        return str_word_count(trim(strip_tags((string) $this->content)));
    }

    public function imageCaption(): string
    {
        return 'Originalfoto aus der Küche und vom Event: '.$this->title.' – Monnemtisch Catering Mannheim';
    }
}
