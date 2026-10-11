<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'customer_id',
        'name',
        'company',
        'rating',
        'title',
        'body',
        'image',
        'is_featured',
        'is_approved',
        'source',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_featured' => 'boolean',
            'is_approved' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function avatarUrl(): ?string
    {
        $image = content_image_path($this->image);
        if ($image && ! str_contains((string) $image, 'placeholder')) {
            return $image;
        }

        $file = match ($this->name) {
            'Anna Müller' => 'anna-mueller.webp',
            'Thomas Weber' => 'thomas-weber.webp',
            'Lisa Hoffmann' => 'lisa-hoffmann.webp',
            'Markus Schneider' => 'markus-schneider.webp',
            'Julia Becker' => 'julia-becker.webp',
            'Peter Klein' => 'peter-klein.webp',
            'Sarah Neumann' => 'sarah-neumann.webp',
            'Daniel Richter' => 'daniel-richter.webp',
            'Claudia Vogt' => 'claudia-vogt.webp',
            'Michael Braun' => 'michael-braun.webp',
            'Nina Krüger' => 'nina-krueger.webp',
            'Stefan Lange' => 'stefan-lange.webp',
            default => null,
        };

        if (! $file || ! is_file(public_path('images/reviews/'.$file))) {
            return null;
        }

        return '/images/reviews/'.$file;
    }
}
