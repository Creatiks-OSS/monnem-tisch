<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class CartAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'persons' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'options' => ['nullable', 'array'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.name' => ['nullable', 'string', 'max:255'],
            'options.*.price_modifier' => ['nullable', 'numeric'],
            'extra_id' => ['nullable', 'integer', 'exists:extras,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'Produkt',
            'quantity' => 'Menge',
            'persons' => 'Personenanzahl',
            'options' => 'Optionen',
            'extra_id' => 'Extra',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Das Feld :attribute ist erforderlich.',
            'exists' => 'Die ausgewählte :attribute ist ungültig.',
            'integer' => 'Das Feld :attribute muss eine ganze Zahl sein.',
            'min' => 'Das Feld :attribute muss mindestens :min sein.',
            'max' => 'Das Feld :attribute darf maximal :max sein.',
            'array' => 'Das Feld :attribute muss ein Array sein.',
            'numeric' => 'Das Feld :attribute muss eine Zahl sein.',
        ];
    }
}
