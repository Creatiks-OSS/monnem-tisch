@extends('layouts.frontend')

@section('title', __('frontend.contact.title').' – '.__('frontend.brand'))
@section('meta_description', __('frontend.contact.intro'))

@section('content')
@php
    $dayNames = [0 => 'Sonntag', 1 => 'Montag', 2 => 'Dienstag', 3 => 'Mittwoch', 4 => 'Donnerstag', 5 => 'Freitag', 6 => 'Samstag'];
@endphp

<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="[['label' => __('frontend.contact.title')]]" />
    <div class="grid gap-12 lg:grid-cols-12">
        <div class="lg:col-span-5">
            <x-section-heading
                eyebrow="Monnemtisch"
                :title="__('frontend.contact.title')"
                :text="__('frontend.contact.intro')"
                :level="1"
            />
            <div class="mt-8 space-y-3 text-sm text-charcoal/80">
                @if (! empty($contact['phone']))
                    <p><a class="hover:text-bronze" href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></p>
                @endif
                @if (! empty($contact['email']))
                    <p><a class="hover:text-bronze" href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></p>
                @endif
                @if (! empty($contact['address']) || ! empty($contact['city']))
                    <p>
                        {{ $contact['address'] ?? '' }}
                        @if (! empty($contact['postal_code']) || ! empty($contact['city']))
                            <br>{{ trim(($contact['postal_code'] ?? '').' '.($contact['city'] ?? '')) }}
                        @endif
                    </p>
                @endif
            </div>

            @if (($hours ?? collect())->isNotEmpty())
                <div class="mt-10">
                    <h2 class="text-2xl font-bold">{{ __('frontend.contact.hours') }}</h2>
                    <ul class="mt-4 space-y-2 text-sm text-charcoal/75">
                        @foreach ($hours as $hour)
                            <li class="flex justify-between gap-4 border-b border-beige py-2">
                                <span>{{ $dayNames[$hour->day_of_week] ?? ('Tag '.$hour->day_of_week) }}</span>
                                <span>{{ $hour->hoursLabel() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
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

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-5 rounded-2xl bg-white p-4 shadow-soft sm:p-6 md:p-8">
                @csrf
                <input type="hidden" name="gclid" value="">
                <x-input label="Name" name="name" required value="{{ old('name') }}" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="E-Mail" name="email" type="email" required value="{{ old('email') }}" />
                    <x-input label="Telefon" name="phone" value="{{ old('phone') }}" />
                </div>
                <x-input label="Betreff" name="subject" value="{{ old('subject') }}" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-select label="Event-Art" name="event_type" placeholder="Bitte wählen">
                        @foreach ($eventTypes as $type)
                            <option value="{{ $type->name }}" @selected(old('event_type') === $type->name)>{{ $type->name }}</option>
                        @endforeach
                    </x-select>
                    <x-blocked-date-picker
                        class="relative z-20"
                        label="Eventdatum"
                        name="event_date"
                        :value="old('event_date')"
                        :blocked="$blockedDates ?? []"
                        min="{{ now()->toDateString() }}"
                    />
                </div>
                <x-textarea label="Nachricht" name="message" required>{{ old('message') }}</x-textarea>
                <x-checkbox name="gdpr_consent" value="1" required :label="__('frontend.checkout.gdpr')" />
                <button type="submit" class="btn-primary">{{ __('frontend.contact.send') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
