<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuoteStatusRequest;
use App\Models\Quote;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\EmailTemplateService;
use App\Services\PdfService;
use App\Services\QuoteProposalService;
use App\Services\QuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuoteController extends Controller
{
    public function __construct(
        protected QuoteService $quotes,
        protected PdfService $pdf,
        protected EmailTemplateService $emailTemplates,
        protected ActivityLogService $activity
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quote::class);

        $quotes = Quote::query()
            ->with(['eventType', 'assignedUser'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('quote_number', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('company', 'like', $term);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.quotes.index', compact('quotes'));
    }

    public function show(Quote $quote): View
    {
        $this->authorize('view', $quote);
        $quote->load(['items', 'customer', 'eventType', 'assignedUser', 'contactMessage']);
        $users = User::role(['Super Admin', 'Admin', 'Manager', 'Sales'])->orderBy('name')->get();

        return view('admin.quotes.show', compact('quote', 'users'));
    }

    public function generate(Quote $quote, QuoteProposalService $proposals): RedirectResponse
    {
        $this->authorize('update', $quote);

        try {
            $proposals->generate($quote);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', 'Yapay zekâ teklifi oluşturuldu. PDF’i kontrol edip müşteriye gönderebilirsiniz.');
    }

    public function edit(Quote $quote): View
    {
        $this->authorize('update', $quote);
        $quote->load(['items', 'eventType']);
        $users = User::role(['Super Admin', 'Admin', 'Manager', 'Sales'])->orderBy('name')->get();

        return view('admin.quotes.edit', compact('quote', 'users'));
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorize('update', $quote);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'event_date' => ['nullable', 'date'],
            'event_time' => ['nullable', 'string', 'max:20'],
            'persons' => ['nullable', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'message' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date'],
        ]);

        $quote->update($data);
        $this->activity->log('Angebot güncellendi', $quote);

        return redirect()->route('admin.quotes.show', $quote)->with('success', 'Teklif kaydedildi.');
    }

    public function updateStatus(QuoteStatusRequest $request, Quote $quote): JsonResponse|RedirectResponse
    {
        return $this->updateDurum($request, $quote);
    }

    public function updateDurum(QuoteStatusRequest $request, Quote $quote): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $quote);

        $this->quotes->updateStatus($quote, $request->validated('status'));

        $extra = array_filter([
            'notes' => $request->validated('notes'),
            'assigned_user_id' => $request->validated('assigned_user_id'),
        ], fn ($v) => $v !== null && $v !== '');

        if ($extra) {
            $quote->update($extra);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $quote->fresh()->status,
                'message' => 'Durum güncellendi.',
            ]);
        }

        return back()->with('success', 'Durum güncellendi.');
    }

    public function generatePdf(Quote $quote): BinaryFileResponse
    {
        $this->authorize('view', $quote);
        $path = $this->pdf->generateQuotePdf($quote);
        $this->activity->log('Angebots-PDF generiert', $quote);

        return response()->download($path, $quote->quote_number.'.pdf');
    }

    public function sendPdf(Quote $quote): RedirectResponse
    {
        $this->authorize('update', $quote);

        try {
            $this->quotes->sendProposalEmail($quote);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Teklif e-posta ile gönderildi.');
    }

    public function convert(Quote $quote): RedirectResponse
    {
        $this->authorize('update', $quote);
        $order = $this->quotes->convertToOrder($quote);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Teklif siparişe dönüştürüldü.');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $this->authorize('delete', $quote);
        $quote->delete();
        $this->activity->log('Angebot silindi', $quote);

        return redirect()->route('admin.quotes.index')->with('success', 'Teklif silindi.');
    }
}