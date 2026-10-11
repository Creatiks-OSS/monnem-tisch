<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'event_type' => ['nullable', 'string', 'max:100'],
            'event_date' => ['nullable', 'date', 'after_or_equal:today'],
            'message' => ['required', 'string', 'max:5000'],
            'gdpr_consent' => ['accepted'],
            'gclid' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'email' => 'E-Mail-Adresse',
            'phone' => 'Telefonnummer',
            'subject' => 'Betreff',
            'event_type' => 'Event-Art',
            'event_date' => 'Eventdatum',
            'message' => 'Nachricht',
            'gdpr_consent' => 'Datenschutzeinwilligung',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Das Feld :attribute ist erforderlich.',
            'email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
            'accepted' => 'Sie müssen der :attribute zustimmen.',
            'after_or_equal' => 'Das :attribute muss heute oder in der Zukunft liegen.',
            'max' => 'Das Feld :attribute darf maximal :max Zeichen haben.',
        ];
    }
}
