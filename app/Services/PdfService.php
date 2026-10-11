<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    public function generateQuotePdf(Quote $quote): string
    {
        $quote->loadMissing(['items', 'customer', 'eventType']);

        $relative = 'pdfs/quotes/'.$quote->quote_number.'.pdf';
        $html = view('pdf.quote', [
            'quote' => $quote,
            'company' => $this->companyData(),
        ])->render();

        $pdf = Pdf::loadHTML($html)->setPaper('a4');
        Storage::disk('local')->put($relative, $pdf->output());

        return Storage::disk('local')->path($relative);
    }

    public function generateInvoicePdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['order.items', 'order.extras', 'customer', 'quote']);

        $relative = 'pdfs/invoices/'.$invoice->invoice_number.'.pdf';
        $html = view('pdf.invoice', [
            'invoice' => $invoice,
            'company' => $this->companyData(),
        ])->render();

        $pdf = Pdf::loadHTML($html)->setPaper('a4');
        Storage::disk('local')->put($relative, $pdf->output());

        if ($invoice->exists) {
            $invoice->pdf_path = $relative;
            $invoice->save();
        }

        return Storage::disk('local')->path($relative);
    }

    /**
     * @return array<string, mixed>
     */
    protected function companyData(): array
    {
        $legalName = \App\Models\Setting::get('legal_name', '', 'general')
            ?: \App\Models\Setting::get('legal_name', '', 'company');
        $street = \App\Models\Setting::get('street', '', 'contact')
            ?: \App\Models\Setting::get('address', '', 'contact');
        $taxId = \App\Models\Setting::get('tax_id', '', 'contact')
            ?: \App\Models\Setting::get('tax_id', '', 'legal');

        return [
            'name' => $legalName ?: \App\Models\Setting::get('company_name', 'Monnemtisch Catering', 'general'),
            'street' => $street,
            'postal_code' => \App\Models\Setting::get('postal_code', '', 'contact'),
            'city' => \App\Models\Setting::get('city', '', 'contact'),
            'phone' => \App\Models\Setting::get('phone', '', 'contact'),
            'email' => \App\Models\Setting::get('email', '', 'contact'),
            'tax_id' => $taxId,
            'iban' => \App\Models\Setting::get('iban', '', 'legal'),
            'logo' => site_logo_data_uri(),
        ];
    }
}
