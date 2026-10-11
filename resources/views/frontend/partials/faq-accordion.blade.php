@props([])
@php
    $faqs = $faqs ?? collect();
    $title = $title ?? __('frontend.home.faq_title');
@endphp

@if ($faqs->isNotEmpty())
@php
    app(\App\Services\SchemaService::class)->push(
        app(\App\Services\SchemaService::class)->faqPage($faqs)
    );
@endphp
<section class="section-pad">
    <div class="container-velora max-w-3xl" x-data="faqAccordion">
        <x-section-heading :title="$title" align="center" class="mb-10" />
        <div class="divide-y divide-beige border-y border-beige">
            @foreach ($faqs as $faq)
                @php
                    $qid = $faq->id ?? $loop->index;
                    $question = is_array($faq) ? ($faq['question'] ?? $faq['q'] ?? '') : ($faq->question ?? '');
                    $answer = is_array($faq) ? ($faq['answer'] ?? $faq['a'] ?? '') : ($faq->answer ?? '');
                @endphp
                <div class="py-4">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 text-left"
                        @click="toggle({{ Js::from($qid) }})"
                        :aria-expanded="(openId === {{ Js::from($qid) }}).toString()"
                    >
                        <span class="text-lg font-bold text-charcoal">{{ $question }}</span>
                        <span class="text-bronze" x-text="openId === {{ Js::from($qid) }} ? '−' : '+'"></span>
                    </button>
                    <div x-show="openId === {{ Js::from($qid) }}" x-collapse x-cloak class="pt-3 text-sm leading-relaxed text-charcoal/75">
                        {!! nl2br(e($answer)) !!}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
