<button
    type="button"
    x-data="{ show: false }"
    x-init="
        const update = () => { show = window.scrollY > 320 };
        update();
        window.addEventListener('scroll', update, { passive: true });
    "
    x-show="show"
    x-cloak
    x-transition.opacity.duration.200ms
    @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
    class="fixed bottom-[5.5rem] right-4 z-40 flex h-12 w-12 items-center justify-center rounded-full border border-beige bg-white text-charcoal shadow-lift transition hover:scale-105 hover:border-bronze hover:text-bronze md:bottom-6 md:right-6"
    aria-label="Nach oben"
>
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
    </svg>
</button>
