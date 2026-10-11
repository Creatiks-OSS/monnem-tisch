@php
    $phone = '';
    $message = rawurlencode('Hallo Monnemtisch Catering, ich interessiere mich für ein Angebot.');
    try {
        $settings = app(\App\Services\SettingService::class);
        $phone = preg_replace('/\D+/', '', $settings->string('whatsapp', $settings->string('phone', '', 'contact'), 'contact'));
    } catch (\Throwable $e) {
        $phone = '';
    }
@endphp

@if ($phone)
    <a
        href="https://wa.me/{{ $phone }}?text={{ $message }}"
        target="_blank"
        rel="noopener noreferrer"
        class="fixed bottom-40 right-4 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lift transition hover:scale-105 md:bottom-24 md:right-6"
        aria-label="WhatsApp"
    >
        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.5 3.5A11.9 11.9 0 0 0 12.05 0C5.5 0 .2 5.3.2 11.85c0 2.1.55 4.1 1.6 5.9L0 24l6.4-1.7a11.8 11.8 0 0 0 5.65 1.45h.05c6.55 0 11.85-5.3 11.85-11.85 0-3.15-1.25-6.15-3.5-8.4ZM12.1 21.5h-.04a9.8 9.8 0 0 1-5-1.35l-.36-.2-3.8 1 1.02-3.7-.24-.38a9.8 9.8 0 0 1-1.5-5.2C2.18 6.4 6.6 2 12.06 2c2.6 0 5.05 1 6.9 2.85a9.7 9.7 0 0 1 2.85 6.9c0 5.45-4.43 9.75-9.71 9.75Zm5.35-7.3c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.64-2.04-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.6-.92-2.2-.24-.57-.5-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.5s1.07 2.9 1.22 3.1c.15.2 2.1 3.2 5.1 4.48.71.3 1.27.49 1.7.63.72.23 1.37.2 1.88.12.57-.09 1.75-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35Z"/></svg>
    </a>
@endif
