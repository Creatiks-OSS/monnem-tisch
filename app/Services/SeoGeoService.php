<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class SeoGeoService
{
    public function save(Model $model, mixed $json): void
    {
        $value = $this->normalize($json);

        $model->seo()->updateOrCreate([], [
            'schema_json' => $value,
        ]);
    }

    public function normalize(mixed $json): ?string
    {
        $value = trim((string) $json);
        if ($value === '') {
            return null;
        }

        json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages([
                'schema_json' => 'SEO/GEO alanı geçerli bir JSON-LD olmalı.',
            ]);
        }

        return $value;
    }
}
