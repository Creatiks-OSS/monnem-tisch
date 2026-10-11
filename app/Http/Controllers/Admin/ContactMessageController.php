<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Quote;
use App\Services\ActivityLogService;
use App\Services\QuoteProposalService;
use App\Services\QuoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function __construct(
        protected ActivityLogService $activity,
        protected QuoteService $quotes
    ) {}

    public function index(Request $request): View
    {
        $messages = ContactMessage::query()
            ->with('quote')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('subject', 'like', $term);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact-messages.index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        if (! $contactMessage->is_read) {
            $contactMessage->update(['is_read' => true]);
        }

        $contactMessage->load(['quote.items', 'quote.eventType']);

        return view('admin.contact-messages.show', ['message' => $contactMessage]);
    }

    public function createQuote(ContactMessage $contactMessage): RedirectResponse
    {
        $this->authorize('create', Quote::class);

        $quote = $this->quotes->createFromContact($contactMessage);

        return redirect()
            ->route('admin.contact-messages.show', $contactMessage)
            ->with('success', 'Teklif kaydı '.$quote->quote_number.' oluşturuldu. Yapay zekâ ile teklif üretip müşteriye gönderebilirsiniz.');
    }

    public function generateQuote(Request $request, ContactMessage $contactMessage, QuoteProposalService $proposals): RedirectResponse
    {
        $this->authorize('create', Quote::class);

        $data = $request->validate([
            'persons' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $quote = $this->quotes->createFromContact($contactMessage);
        $this->authorize('update', $quote);

        if (! empty($data['persons'])) {
            $quote->update(['persons' => $data['persons']]);
        }

        try {
            $proposals->generate($quote);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.contact-messages.show', $contactMessage)
            ->with('success', 'Yapay zekâ teklifi oluşturuldu. PDF’i kontrol edip müşteriye gönderebilirsiniz.');
    }

    public function sendPdf(ContactMessage $contactMessage): RedirectResponse
    {
        $quote = $contactMessage->quote;
        if (! $quote) {
            return back()->with('error', 'Önce teklif kaydı oluşturun.');
        }

        $this->authorize('update', $quote);

        try {
            $this->quotes->sendProposalEmail($quote);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Teklif e-posta ile gönderildi.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();
        $this->activity->log('Kontaktnachricht silindi', $contactMessage);

        return redirect()->route('admin.contact-messages.index')->with('success', 'Mesaj silindi.');
    }
}
