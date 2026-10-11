@php
    $filters = $filters ?? [];
@endphp

<form method="GET" class="space-y-4 border border-beige bg-ivory p-5">
    @if (! empty($showSearch))
        <div>
            <label class="label-velora" for="filter-q">Suche</label>
            <input id="filter-q" type="search" name="q" value="{{ $filters['q'] ?? request('q') }}" class="input-velora" placeholder="Produkt suchen…">
        </div>
    @endif

    @if (isset($categories) && $categories->isNotEmpty())
        <div>
            <label class="label-velora" for="filter-category">{{ __('frontend.nav.catering') }}</label>
            <select id="filter-category" name="category" class="input-velora">
                <option value="">Alle Kategorien</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected(($filters['category'] ?? '') === $cat->slug)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="space-y-2">
        <p class="label-velora">Ernährung</p>
        <x-checkbox name="vegan" :checked="!empty($filters['vegan'])" :label="__('frontend.filters.vegan')" />
        <x-checkbox name="vegetarian" :checked="!empty($filters['vegetarian'])" :label="__('frontend.filters.vegetarian')" />
        <x-checkbox name="gluten_free" :checked="!empty($filters['gluten_free'])" :label="__('frontend.filters.gluten_free')" />
        @if (! empty($showLactose))
            <x-checkbox name="lactose_free" :checked="!empty($filters['lactose_free'])" :label="__('frontend.filters.lactose_free')" />
        @endif
    </div>

    <div>
        <label class="label-velora" for="filter-sort">{{ __('frontend.filters.sort') }}</label>
        <select id="filter-sort" name="sort" class="input-velora">
            <option value="" @selected(empty($filters['sort']))>{{ __('frontend.filters.sort_default') }}</option>
            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('frontend.filters.sort_price_asc') }}</option>
            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('frontend.filters.sort_price_desc') }}</option>
            <option value="name" @selected(($filters['sort'] ?? '') === 'name')>{{ __('frontend.filters.sort_name') }}</option>
            @if (! empty($showNewest))
                <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>{{ __('frontend.filters.sort_newest') }}</option>
            @endif
        </select>
    </div>

    <div class="flex flex-wrap gap-2 pt-2">
        <button type="submit" class="btn-primary !py-2.5 text-xs">{{ __('frontend.filters.apply') }}</button>
        <a href="{{ url()->current() }}" class="btn-secondary !py-2.5 text-xs">{{ __('frontend.filters.reset') }}</a>
    </div>
</form>
