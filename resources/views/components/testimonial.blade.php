@props([
    'review',
])

@php
    $name = $review->name ?? $review->author_name ?? 'Gast';
    $text = $review->body ?? $review->content ?? $review->comment ?? $review->text ?? '';
    $rating = (int) ($review->rating ?? 5);
    $event = $review->title ?? $review->event_type ?? $review->company ?? null;
    $photo = method_exists($review, 'avatarUrl') ? $review->avatarUrl() : null;
@endphp

<figure {{ $attributes->merge(['class' => '']) }}>
    <div class="mb-3 flex gap-0.5 text-bronze" aria-label="{{ $rating }} von 5 Sternen">
        @for ($i = 1; $i <= 5; $i++)
            <svg class="h-4 w-4 {{ $i <= $rating ? 'fill-bronze' : 'fill-beige' }}" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 1.5l2.4 4.86 5.36.78-3.88 3.78.92 5.34L10 13.9l-4.8 2.52.92-5.34L2.24 7.14l5.36-.78L10 1.5z"/></svg>
        @endfor
    </div>
    <blockquote class="text-[15px] leading-relaxed text-charcoal/80">
        „{{ \Illuminate\Support\Str::limit($text, 180) }}“
    </blockquote>
    <figcaption class="mt-4 flex items-center gap-3 text-sm font-semibold text-charcoal">
        @if ($photo)
            <img src="{{ $photo }}" alt="{{ $name }}" class="h-10 w-10 rounded-full object-cover" width="40" height="40" loading="lazy">
        @endif
        <span>
            {{ $name }}
            @if ($event)
                <span class="font-normal text-muted"> · {{ $event }}</span>
            @endif
        </span>
    </figcaption>
</figure>
