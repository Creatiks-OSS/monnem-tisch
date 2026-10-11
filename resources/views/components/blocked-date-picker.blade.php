@props([
    'label' => 'Datum',
    'name' => 'event_date',
    'required' => false,
    'value' => null,
    'blocked' => [],
    'min' => null,
    'max' => null,
])

@php
    $blockedDates = collect($blocked ?? [])
        ->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->toDateString())
        ->values()
        ->all();
    $minDate = $min ?: now()->toDateString();
    $initial = $value ? \Illuminate\Support\Carbon::parse($value)->toDateString() : '';
    if ($initial !== '' && in_array($initial, $blockedDates, true)) {
        $initial = '';
    }
    $weekdays = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];
@endphp

@once
@push('head')
<style>
.date-picker-wrap { position: relative; overflow: visible; }
.date-picker-panel {
    position: absolute;
    z-index: 60;
    top: calc(100% + 0.45rem);
    left: 0;
    right: 0;
    width: 100%;
    min-width: 17.5rem;
    padding: 0.85rem;
    border: 1px solid var(--color-beige, #e6e0d6);
    border-radius: 1rem;
    background: #fff;
    box-shadow: 0 16px 40px rgba(27, 36, 48, 0.12);
}
.date-picker-cal {
    display: grid !important;
    grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
    gap: 2px;
    width: 100%;
}
.date-picker-cal > span {
    display: block;
    text-align: center;
    padding: 0.2rem 0 0.45rem;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--color-muted, #6b7280);
}
.date-picker-day {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 2.15rem;
    border: 0;
    border-radius: 0.55rem;
    background: transparent;
    color: var(--color-charcoal, #1b2430);
    font-size: 0.82rem;
    font-weight: 600;
    line-height: 1;
    cursor: pointer;
}
.date-picker-day:hover:not(:disabled) { background: var(--color-cream, #f7f5f1); }
.date-picker-day.is-empty { visibility: hidden; pointer-events: none; }
.date-picker-day.is-muted { color: #c5c0b8; cursor: not-allowed; }
.date-picker-day.is-blocked {
    background: #fde8e4;
    color: var(--color-danger, #c2413b);
    text-decoration: line-through;
    cursor: not-allowed;
}
.date-picker-day.is-selected { background: var(--color-bronze, #d89b2b); color: #132A3A; }
.date-picker-day.is-today:not(.is-selected):not(.is-blocked) {
    box-shadow: inset 0 0 0 1px var(--color-bronze, #d89b2b);
}
</style>
@endpush
@endonce

<div {{ $attributes->only('class')->merge(['class' => 'min-w-0 w-full']) }}>
    @if ($label)
        <label for="{{ $name }}_display" class="label-velora">
            {{ $label }}@if ($required) <span class="text-bronze">*</span>@endif
        </label>
    @endif

    <div
        class="date-picker-wrap"
        x-data="blockedDatePicker({
            value: @js($initial),
            blocked: @js($blockedDates),
            min: @js($minDate),
            max: @js($max),
            required: @js((bool) $required),
        })"
        @keydown.escape.window="open = false"
        @click.outside="open = false"
    >
        <input type="hidden" name="{{ $name }}" :value="value">

        <button
            type="button"
            id="{{ $name }}_display"
            class="input-velora flex w-full items-center justify-between text-left"
            :class="open ? 'border-bronze ring-1 ring-bronze' : ''"
            @click="open = !open"
            :aria-expanded="open"
            aria-haspopup="dialog"
        >
            <span class="truncate" :class="value ? 'text-charcoal' : 'text-muted/70'" x-text="displayValue() || 'Datum wählen'"></span>
            <svg class="ml-2 h-4 w-4 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <rect x="3" y="5" width="18" height="16" rx="2" stroke-width="1.5"/>
                <path stroke-linecap="round" stroke-width="1.5" d="M8 3v4M16 3v4M3 10h18"/>
            </svg>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition.origin.top
            class="date-picker-panel"
            role="dialog"
            aria-label="Kalender"
        >
            <div class="mb-3 flex items-center justify-between">
                <button type="button" class="rounded-lg p-1.5 text-charcoal hover:bg-cream" @click="prevMonth()" aria-label="Vorheriger Monat">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 6 9 12l6 6"/></svg>
                </button>
                <p class="text-sm font-semibold" x-text="monthLabel()"></p>
                <button type="button" class="rounded-lg p-1.5 text-charcoal hover:bg-cream" @click="nextMonth()" aria-label="Nächster Monat">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 6 6 6-6 6"/></svg>
                </button>
            </div>

            <div class="date-picker-cal" style="display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:2px">
                @foreach ($weekdays as $day)
                    <span>{{ $day }}</span>
                @endforeach
            </div>
            <div
                class="date-picker-cal"
                style="display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:2px"
                x-html="daysHtml(year, month, value)"
                @click="pickFromEvent($event)"
            ></div>

            <p class="mt-3 flex items-center gap-2 text-xs text-muted">
                <span class="inline-block h-3 w-3 rounded bg-danger"></span>
                Rote Tage sind ausgebucht und nicht buchbar.
            </p>
        </div>
    </div>

    @if ($name && $errors->has($name))
        <p class="mt-1 text-xs text-danger">{{ $errors->first($name) }}</p>
    @endif
</div>

@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('blockedDatePicker', (cfg = {}) => ({
        value: cfg.value || '',
        blocked: Array.isArray(cfg.blocked) ? cfg.blocked : [],
        min: cfg.min || '',
        max: cfg.max || '',
        required: Boolean(cfg.required),
        open: false,
        year: 0,
        month: 0,
        monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
        init() {
            const start = this.value || this.min || this.todayYmd();
            const d = this.parseYmd(start);
            this.year = d.getFullYear();
            this.month = d.getMonth();
            if (this.value && this.isDisabled(this.value)) {
                this.value = '';
            }
        },
        todayYmd() {
            const n = new Date();
            return this.toYmd(n);
        },
        toYmd(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + day;
        },
        parseYmd(s) {
            const parts = String(s || '').split('-').map(Number);
            return new Date(parts[0] || 1970, (parts[1] || 1) - 1, parts[2] || 1);
        },
        displayValue() {
            if (!this.value) return '';
            const d = this.parseYmd(this.value);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            return day + '.' + month + '.' + d.getFullYear();
        },
        monthLabel() {
            return this.monthNames[this.month] + ' ' + this.year;
        },
        isBlocked(ymd) {
            return this.blocked.indexOf(ymd) !== -1;
        },
        isDisabled(ymd) {
            if (!ymd) return true;
            if (this.min && ymd < this.min) return true;
            if (this.max && ymd > this.max) return true;
            return this.isBlocked(ymd);
        },
        dayClass(ymd) {
            if (!ymd) return 'is-empty';
            const classes = [];
            if (this.isBlocked(ymd)) classes.push('is-blocked');
            else if (this.isDisabled(ymd)) classes.push('is-muted');
            if (ymd === this.value) classes.push('is-selected');
            if (ymd === this.todayYmd()) classes.push('is-today');
            return classes.join(' ');
        },
        cells() {
            const first = new Date(this.year, this.month, 1);
            const startDow = (first.getDay() + 6) % 7;
            const daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
            const list = [];
            for (let i = 0; i < startDow; i++) list.push(null);
            for (let d = 1; d <= daysInMonth; d++) {
                list.push(this.toYmd(new Date(this.year, this.month, d)));
            }
            while (list.length % 7 !== 0) list.push(null);
            return list;
        },
        daysHtml() {
            return this.cells().map((cell) => {
                const cls = this.dayClass(cell);
                const disabled = !cell || this.isDisabled(cell);
                const label = cell ? String(Number(String(cell).slice(-2))) : '';
                const title = cell && this.isBlocked(cell) ? ' title="Ausgebucht"' : '';
                return '<button type="button" class="date-picker-day ' + cls + '" data-date="' + (cell || '') + '"' + (disabled ? ' disabled' : '') + title + '>' + label + '</button>';
            }).join('');
        },
        pickFromEvent(e) {
            const btn = e.target.closest('[data-date]');
            if (!btn || btn.disabled) return;
            const ymd = btn.getAttribute('data-date');
            if (ymd) this.select(ymd);
        },
        prevMonth() {
            if (this.month === 0) {
                this.month = 11;
                this.year -= 1;
            } else {
                this.month -= 1;
            }
        },
        nextMonth() {
            if (this.month === 11) {
                this.month = 0;
                this.year += 1;
            } else {
                this.month += 1;
            }
        },
        select(ymd) {
            if (this.isDisabled(ymd)) return;
            this.value = ymd;
            this.open = false;
            this.$dispatch('date-selected', ymd);
        },
    }));
});
</script>
@endpush
@endonce
