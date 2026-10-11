<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'folder',
        'disk',
        'path',
        'filename',
        'mime_type',
        'size',
        'width',
        'height',
        'alt',
        'title',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    protected function url(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (empty($this->path)) {
                return null;
            }

            if (Str::startsWith($this->path, ['http://', 'https://', '/'])) {
                return $this->path;
            }

            return Storage::disk($this->disk ?: 'public')->url($this->path);
        });
    }
}
