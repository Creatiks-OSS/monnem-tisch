<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pageId = $this->route('page')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($pageId)],
            'type' => ['nullable', 'string', 'max:50'],
            'template' => ['nullable', 'string', 'max:100'],
            'content' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'parent_id' => ['nullable', 'exists:pages,id'],
            'sections' => ['nullable', 'array'],
            'sections.*.type' => ['required_with:sections', 'string', 'max:50'],
            'sections.*.title' => ['nullable', 'string', 'max:255'],
            'sections.*.subtitle' => ['nullable', 'string', 'max:255'],
            'sections.*.content' => ['nullable', 'string'],
            'sections.*.is_active' => ['sometimes', 'boolean'],
            'schema_json' => ['nullable', 'string', 'max:20000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
        ]);
    }
}
