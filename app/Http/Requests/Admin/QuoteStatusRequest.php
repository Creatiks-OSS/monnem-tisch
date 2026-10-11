<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    'neu', 'kontaktiert', 'angebot_erstellt', 'verhandlung', 'gewonnen', 'verloren',
                ]),
            ],
            'notes' => ['nullable', 'string'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
