<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderStatusRequest extends FormRequest
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
                    'neu', 'bestätigt', 'in_bearbeitung', 'vorbereitung',
                    'unterwegs', 'geliefert', 'abgeschlossen', 'storniert',
                ]),
            ],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}
