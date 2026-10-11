<?php

namespace App\Http\Requests\Frontend;

use App\Services\AvailabilityService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class QuoteRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'event_type_id' => ['nullable', 'integer', 'exists:event_types,id'],
            'event_date' => ['nullable', 'date', 'after_or_equal:today'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'persons' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'catering_wunsch' => ['nullable', 'string', 'max:2000'],
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
            'first_name' => 'Vorname',
            'last_name' => 'Nachname',
            'email' => 'E-Mail-Adresse',
            'phone' => 'Telefonnummer',
            'company' => 'Firma',
            'event_type_id' => 'Event-Art',
            'event_date' => 'Eventdatum',
            'event_time' => 'Uhrzeit',
            'persons' => 'Personenanzahl',
            'location' => 'Veranstaltungsort',
            'budget' => 'Budget',
            'catering_wunsch' => 'Catering-Wunsch',
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
            'exists' => 'Die ausgewählte :attribute ist ungültig.',
            'min' => 'Das Feld :attribute muss mindestens :min sein.',
            'max' => 'Das Feld :attribute darf maximal :max Zeichen haben.',
            'numeric' => 'Das Feld :attribute muss eine Zahl sein.',
            'date_format' => 'Das Feld :attribute muss im Format HH:MM angegeben werden.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $date = $this->input('event_date');
            if (! is_string($date) || $date === '' || $validator->errors()->has('event_date')) {
                return;
            }

            if (! app(AvailabilityService::class)->isDateAvailable($date)) {
                $validator->errors()->add(
                    'event_date',
                    'Dieses Datum ist leider ausgebucht. Bitte wählen Sie einen anderen Tag.'
                );
            }
        });
    }
}
