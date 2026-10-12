<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:15360'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_type' => ['nullable', 'string', 'max:50'],
            'minimum_quantity' => ['nullable', 'integer', 'min:1'],
            'minimum_persons' => ['nullable', 'integer', 'min:1'],
            'maximum_persons' => ['nullable', 'integer', 'min:1'],
            'ingredients' => ['nullable', 'string'],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['string', Rule::in(app(\App\Services\AllergenCatalog::class)->codes())],
            'calories' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'is_vegan' => ['sometimes', 'boolean'],
            'is_vegetarian' => ['sometimes', 'boolean'],
            'is_gluten_free' => ['sometimes', 'boolean'],
            'is_lactose_free' => ['sometimes', 'boolean'],
            'is_spicy' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_bestseller' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:15360'],
            'tiers' => ['nullable', 'array'],
            'tiers.*.min_persons' => ['required_with:tiers', 'integer', 'min:1'],
            'tiers.*.max_persons' => ['nullable', 'integer', 'min:1'],
            'tiers.*.price' => ['required_with:tiers', 'numeric', 'min:0'],
            'options' => ['nullable', 'array'],
            'options.*.name' => ['required_with:options', 'string', 'max:255'],
            'options.*.type' => ['nullable', 'string', 'max:50'],
            'options.*.price_modifier' => ['nullable', 'numeric'],
            'options.*.is_required' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'price' => $this->input('price') === null || $this->input('price') === '' ? 0 : $this->input('price'),
            'price_type' => $this->input('price_type') ?: 'person',
            'calories' => $this->input('calories') === '' ? null : $this->input('calories'),
            'is_vegan' => $this->boolean('is_vegan'),
            'is_vegetarian' => $this->boolean('is_vegetarian'),
            'is_gluten_free' => $this->boolean('is_gluten_free'),
            'is_lactose_free' => $this->boolean('is_lactose_free'),
            'is_spicy' => $this->boolean('is_spicy'),
            'is_featured' => $this->boolean('is_featured'),
            'is_bestseller' => $this->boolean('is_bestseller'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
