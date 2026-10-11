<?php

namespace App\Http\Requests\Frontend;

use App\Services\AvailabilityService;
use App\Services\GoogleDistanceService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $distance = app(GoogleDistanceService::class);

        $billingLine = trim((string) $this->input('billing_address', ''));
        if ($billingLine !== '') {
            $parsed = $distance->parseAddressLine($billingLine);
            $this->merge([
                'billing_street' => $this->filled('billing_street') ? $this->input('billing_street') : $parsed['street'],
                'billing_house_number' => $this->filled('billing_house_number') ? $this->input('billing_house_number') : $parsed['house_number'],
                'billing_postal_code' => $this->filled('billing_postal_code') ? $this->input('billing_postal_code') : ($parsed['postal_code'] ?: '00000'),
                'billing_city' => $this->filled('billing_city') ? $this->input('billing_city') : ($parsed['city'] ?: $billingLine),
            ]);
        }

        $shippingLine = trim((string) $this->input('shipping_address', ''));
        if ($shippingLine !== '') {
            $parsed = $distance->parseAddressLine($shippingLine);
            $this->merge([
                'shipping_street' => $this->filled('shipping_street') ? $this->input('shipping_street') : $parsed['street'],
                'shipping_house_number' => $this->filled('shipping_house_number') ? $this->input('shipping_house_number') : $parsed['house_number'],
                'shipping_postal_code' => $this->filled('shipping_postal_code') ? $this->input('shipping_postal_code') : ($parsed['postal_code'] ?: '00000'),
                'shipping_city' => $this->filled('shipping_city') ? $this->input('shipping_city') : ($parsed['city'] ?: $shippingLine),
            ]);
        }

        $eventTime = trim((string) $this->input('event_time', ''));
        if (preg_match('/^(\d{2}:\d{2}):\d{2}$/', $eventTime, $match)) {
            $this->merge(['event_time' => $match[1]]);
        }

        $fullName = trim((string) $this->input('full_name', ''));
        if ($fullName !== '' && ! $this->filled('first_name')) {
            $parts = preg_split('/\s+/u', $fullName, 2) ?: [];
            $first = $parts[0] ?? $fullName;
            $last = isset($parts[1]) && trim($parts[1]) !== '' ? trim($parts[1]) : '-';
            $this->merge([
                'first_name' => $first,
                'last_name' => $last,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['nullable', 'string', 'max:201'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'event_type_id' => ['nullable', 'integer', 'exists:event_types,id'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'persons' => ['required', 'integer', 'min:1', 'max:5000'],

            'billing_address' => ['required', 'string', 'max:255'],
            'billing_street' => ['required', 'string', 'max:255'],
            'billing_house_number' => ['required', 'string', 'max:20'],
            'billing_postal_code' => ['required', 'string', 'max:20'],
            'billing_city' => ['required', 'string', 'max:100'],
            'billing_country' => ['nullable', 'string', 'size:2'],

            'shipping_same_as_billing' => ['sometimes', 'boolean'],
            'shipping_address' => ['required_unless:shipping_same_as_billing,1,true', 'nullable', 'string', 'max:255'],
            'shipping_street' => ['required_unless:shipping_same_as_billing,1,true', 'nullable', 'string', 'max:255'],
            'shipping_house_number' => ['required_unless:shipping_same_as_billing,1,true', 'nullable', 'string', 'max:20'],
            'shipping_postal_code' => ['required_unless:shipping_same_as_billing,1,true', 'nullable', 'string', 'max:20'],
            'shipping_city' => ['required_unless:shipping_same_as_billing,1,true', 'nullable', 'string', 'max:100'],
            'shipping_country' => ['nullable', 'string', 'size:2'],

            'payment_method' => ['required', 'string', Rule::in(array_keys(app(\App\Services\PaymentGatewayService::class)->checkoutMethods()))],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'delivery_cost' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'delivery_km' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'tip_amount' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'gdpr_consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'Vollständiger Name',
            'first_name' => 'Vorname',
            'last_name' => 'Nachname',
            'email' => 'E-Mail-Adresse',
            'phone' => 'Telefonnummer',
            'company' => 'Firma',
            'notes' => 'Anmerkungen',
            'event_type_id' => 'Event-Art',
            'event_date' => 'Eventdatum',
            'event_time' => 'Uhrzeit',
            'persons' => 'Personenanzahl',
            'billing_address' => 'Straße, Nr, PLZ, Ort',
            'billing_street' => 'Straße (Rechnung)',
            'billing_house_number' => 'Hausnummer (Rechnung)',
            'billing_postal_code' => 'PLZ (Rechnung)',
            'billing_city' => 'Ort (Rechnung)',
            'billing_country' => 'Land (Rechnung)',
            'shipping_address' => 'Lieferadresse',
            'shipping_street' => 'Straße (Lieferung)',
            'shipping_house_number' => 'Hausnummer (Lieferung)',
            'shipping_postal_code' => 'PLZ (Lieferung)',
            'shipping_city' => 'Ort (Lieferung)',
            'shipping_country' => 'Land (Lieferung)',
            'payment_method' => 'Zahlungsart',
            'coupon_code' => 'Gutscheincode',
            'delivery_cost' => 'Lieferkosten',
            'delivery_km' => 'Lieferentfernung',
            'tip_amount' => 'Trinkgeld',
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
            'required_unless' => 'Das Feld :attribute ist erforderlich.',
            'date_format' => 'Das Feld :attribute muss im Format HH:MM angegeben werden.',
            'in' => 'Die ausgewählte :attribute ist ungültig.',
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
