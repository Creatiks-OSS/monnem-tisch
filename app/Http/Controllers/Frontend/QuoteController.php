<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\QuoteRequest;
use App\Models\EventType;
use App\Services\AvailabilityService;
use App\Services\QuoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct(
        protected QuoteService $quotes,
        protected AvailabilityService $availability
    ) {}

    public function create(): View
    {
        $eventTypes = EventType::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $blocked = collect($this->availability->getBlockedDates(now(), now()->addMonths(18)))
            ->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->toDateString())
            ->values();

        return view('frontend.quote.create', [
            'eventTypes' => $eventTypes,
            'blockedDates' => $blocked,
        ]);
    }

    public function store(QuoteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['source'] = 'website';

        $quote = $this->quotes->createFromRequest($data);

        return redirect()
            ->route('quote.success', $quote->quote_number)
            ->with('success', 'Vielen Dank! Ihre Anfrage wurde gesendet.')
            ->with('lead_tracked', true)
            ->with('lead_params', [
                'method' => 'quote_form',
                'form_type' => 'anfrage',
            ]);
    }

    public function success(string $quoteNumber): View
    {
        $quote = \App\Models\Quote::query()
            ->where('quote_number', $quoteNumber)
            ->firstOrFail();

        return view('frontend.quote.success', [
            'quote' => $quote,
        ]);
    }
}
