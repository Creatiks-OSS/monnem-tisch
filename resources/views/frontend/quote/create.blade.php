@extends('layouts.frontend')

@section('title', __('frontend.quote.page_title'))
@section('meta_description', __('frontend.quote.intro'))

@section('content')
<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="[['label' => __('frontend.quote.title')]]" />
    <div class="grid gap-12 lg:grid-cols-12">
        <div class="lg:col-span-5">
            <x-section-heading
                eyebrow="Anfrage"
                :title="__('frontend.quote.title')"
                :text="__('frontend.quote.intro')"
            />
            <p class="mt-8 text-sm leading-relaxed text-charcoal/70">
                Beschreiben Sie Anlass, Personenanzahl und Wünsche – wir erstellen Ihnen ein individuelles Angebot.
            </p>
        </div>
        <div class="lg:col-span-7">
            @if ($errors->any())
                <x-alert type="error">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            <form action="{{ route('quote.store') }}" method="POST" class="space-y-5 rounded-2xl bg-white p-4 shadow-soft sm:p-6 md:p-8">
                @csrf
                <input type="hidden" name="gclid" value="">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Vorname" name="first_name" required value="{{ old('first_name') }}" />
                    <x-input label="Nachname" name="last_name" required value="{{ old('last_name') }}" />
                    <x-input label="E-Mail" name="email" type="email" required value="{{ old('email') }}" />
                    <x-input label="Telefon" name="phone" required value="{{ old('phone') }}" />
                    <x-input label="Firma" name="company" value="{{ old('company') }}" class="sm:col-span-2" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-select label="Event-Art" name="event_type_id" placeholder="Bitte wählen">
                        @foreach ($eventTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('event_type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </x-select>
                    <x-blocked-date-picker
                        class="relative z-20 sm:col-span-2"
                        label="Datum"
                        name="event_date"
                        :value="old('event_date')"
                        :blocked="$blockedDates ?? []"
                        min="{{ now()->toDateString() }}"
                    />
                    <x-input label="Uhrzeit" name="event_time" type="time" value="{{ old('event_time') }}" />
                    <x-input label="Personen" name="persons" type="number" min="1" value="{{ old('persons') }}" />
                    <x-input label="Veranstaltungsort" name="location" value="{{ old('location') }}" class="sm:col-span-2" />
                    <x-input label="Budget (€)" name="budget" type="number" min="0" step="0.01" value="{{ old('budget') }}" />
                </div>
                <x-textarea label="Catering-Wunsch" name="catering_wunsch" rows="3">{{ old('catering_wunsch') }}</x-textarea>
                <x-textarea label="Nachricht" name="message" required rows="5">{{ old('message') }}</x-textarea>
                <x-checkbox name="gdpr_consent" value="1" required>
                    Ich stimme der Verarbeitung meiner Daten gemäß
                    <a href="{{ route('legal.datenschutz') }}" class="font-medium underline decoration-beige underline-offset-2 hover:text-bronze" target="_blank" rel="noopener noreferrer">Datenschutzerklärung</a>
                    zu.
                </x-checkbox>
                <button type="submit" class="btn-primary">{{ __('frontend.cta.submit') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
