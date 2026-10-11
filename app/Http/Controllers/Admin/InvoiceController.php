<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\ActivityLogService;
use App\Services\BusinessNotificationService;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceController extends Controller
{
    public function __construct(
        protected PdfService $pdf,
        protected ActivityLogService $activity,
        protected BusinessNotificationService $notifications
    ) {}

    public function index(Request $request): View
    {
        $invoices = Invoice::query()
            ->with(['customer', 'order'])
            ->when($request->filled('q'), fn ($q) => $q->where('invoice_number', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['customer', 'order.items', 'order.extras', 'quote', 'payments']);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function create(): View
    {
        return view('admin.invoices.create', [
            'reducedRate' => shop_tax_rate(),
            'standardRate' => shop_standard_tax_rate(),
        ]);
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $request->merge([
            'tax_rate' => $this->decimal($request->input('tax_rate')),
            'tax_standard_rate' => $this->decimal($request->input('tax_standard_rate')),
            'items' => collect($request->input('items', []))->map(function ($item) {
                return [
                    'name' => trim((string) ($item['name'] ?? '')),
                    'quantity' => $this->decimal($item['quantity'] ?? 0),
                    'unit_price' => $this->decimal($item['unit_price'] ?? 0),
                    'tax' => ($item['tax'] ?? 'reduced') === 'standard' ? 'standard' : 'reduced',
                ];
            })->all(),
        ]);

        $data = $request->validate([
            'billing_first_name' => ['required', 'string', 'max:120'],
            'billing_last_name' => ['required', 'string', 'max:120'],
            'billing_company' => ['nullable', 'string', 'max:180'],
            'billing_email' => ['required', 'email', 'max:180'],
            'billing_phone' => ['nullable', 'string', 'max:40'],
            'billing_street' => ['required', 'string', 'max:180'],
            'billing_house_number' => ['nullable', 'string', 'max:20'],
            'billing_postal_code' => ['required', 'string', 'max:20'],
            'billing_city' => ['required', 'string', 'max:120'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_standard_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax' => ['required', 'in:reduced,standard'],
        ]);

        $reducedRate = round((float) $data['tax_rate'], 2);
        $standardRate = round((float) $data['tax_standard_rate'], 2);
        $reducedNet = 0.0;
        $standardNet = 0.0;
        $lines = [];

        foreach ($data['items'] as $item) {
            $lineTotal = round((float) $item['quantity'] * (float) $item['unit_price'], 2);
            $bucket = $item['tax'] === 'standard' ? 'standard' : 'reduced';
            if ($bucket === 'standard') {
                $standardNet += $lineTotal;
            } else {
                $reducedNet += $lineTotal;
            }
            $lines[] = [
                'name' => $item['name'],
                'quantity' => round((float) $item['quantity'], 2),
                'unit_price' => round((float) $item['unit_price'], 2),
                'line_total' => $lineTotal,
                'tax' => $bucket,
            ];
        }

        $reducedNet = round($reducedNet, 2);
        $standardNet = round($standardNet, 2);
        $taxAmount = round($reducedNet * $reducedRate / 100, 2);
        $standardTax = round($standardNet * $standardRate / 100, 2);
        $subtotal = round($reducedNet + $standardNet, 2);
        $total = round($subtotal + $taxAmount + $standardTax, 2);

        $customer = Customer::query()->where('email', $data['billing_email'])->first();
        if (! $customer) {
            $customer = Customer::query()->create([
                'first_name' => $data['billing_first_name'],
                'last_name' => $data['billing_last_name'],
                'company' => $data['billing_company'] ?? null,
                'email' => $data['billing_email'],
                'phone' => $data['billing_phone'] ?? null,
                'source' => 'invoice',
            ]);
        }

        $invoice = Invoice::query()->create([
            'customer_id' => $customer->id,
            'is_manual' => true,
            'billing_first_name' => $data['billing_first_name'],
            'billing_last_name' => $data['billing_last_name'],
            'billing_company' => $data['billing_company'] ?? null,
            'billing_email' => $data['billing_email'],
            'billing_phone' => $data['billing_phone'] ?? null,
            'billing_street' => $data['billing_street'],
            'billing_house_number' => $data['billing_house_number'] ?? null,
            'billing_postal_code' => $data['billing_postal_code'],
            'billing_city' => $data['billing_city'],
            'status' => 'sent',
            'issued_at' => now(),
            'due_at' => now()->addDays(14),
            'subtotal' => $subtotal,
            'tax_rate' => $reducedRate,
            'tax_amount' => $taxAmount,
            'tax_standard_rate' => $standardRate,
            'tax_standard_amount' => $standardTax,
            'total' => $total,
            'notes' => $data['notes'] ?? null,
            'line_items' => $lines,
        ]);

        $path = $this->pdf->generateInvoicePdf($invoice);
        $this->activity->log('Manuelle Rechnung erstellt', $invoice);

        try {
            $this->notifications->sendCustomerInvoice($invoice, $path);
        } catch (\Throwable $e) {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('error', 'Fatura oluştu ama e-posta gönderilemedi: '.($e->getMessage() ?: 'posta hatası'));
        }

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Manuel fatura oluşturuldu ve '.$data['billing_email'].' adresine gönderildi.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
        ]);

        $order = Order::query()->with('customer')->findOrFail($data['order_id']);

        if ($order->invoice) {
            return redirect()->route('admin.invoices.show', $order->invoice)
                ->with('error', 'Bu sipariş için zaten bir fatura var.');
        }

        $invoice = Invoice::query()->create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'status' => 'sent',
            'issued_at' => now(),
            'due_at' => now()->addDays(14),
            'subtotal' => $order->subtotal,
            'tax_rate' => $order->tax_rate,
            'tax_amount' => $order->tax_amount,
            'tax_standard_rate' => $order->tax_standard_rate ?? shop_standard_tax_rate(),
            'tax_standard_amount' => $order->tax_standard_amount ?? 0,
            'total' => $order->total,
        ]);

        $this->pdf->generateInvoicePdf($invoice);
        $this->activity->log('Fatura erstellt', $invoice);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Fatura oluşturuldu.');
    }

    public function generatePdf(Invoice $invoice): BinaryFileResponse
    {
        $path = $this->pdf->generateInvoicePdf($invoice);
        $this->activity->log('Rechnungs-PDF generiert', $invoice);

        return response()->download($path, $invoice->invoice_number.'.pdf');
    }

    public function sendPdf(Invoice $invoice): RedirectResponse
    {
        $path = $this->pdf->generateInvoicePdf($invoice);

        try {
            $this->notifications->sendCustomerInvoice($invoice, $path);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Fatura e-posta ile gönderilemedi.');
        }

        if ($invoice->status === 'draft') {
            $invoice->update(['status' => 'sent']);
        }

        $this->activity->log('Rechnungs-PDF versendet', $invoice);

        return back()->with('success', 'Fatura müşteri e-posta adresine gönderildi.');
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:draft,sent,paid,cancelled'],
            'notes' => ['nullable', 'string'],
            'paid_at' => ['nullable', 'date'],
        ]);

        if ($data['status'] === 'paid' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        $invoice->update($data);
        $this->activity->log('Fatura güncellendi', $invoice);

        return back()->with('success', 'Fatura kaydedildi.');
    }

    protected function decimal(mixed $value): float
    {
        $raw = trim(str_replace(' ', '', (string) $value));
        if ($raw === '') {
            return 0.0;
        }
        if (str_contains($raw, ',')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        }

        return (float) $raw;
    }
}