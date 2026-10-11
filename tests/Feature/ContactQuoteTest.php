<?php

namespace Tests\Feature;

use App\Mail\QuoteProposalMail;
use App\Models\ContactMessage;
use App\Models\EventType;
use App\Models\Quote;
use App\Models\User;
use App\Services\PdfService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactQuoteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create([
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $this->admin->assignRole('Super Admin');
    }

    public function test_admin_can_create_quote_from_contact_message(): void
    {
        $eventType = EventType::query()->create([
            'name' => 'Hochzeit',
            'slug' => 'hochzeit',
            'is_active' => true,
        ]);

        $message = $this->makeMessage();

        $this->actingAs($this->admin)
            ->post(route('admin.contact-messages.create-quote', $message))
            ->assertRedirect(route('admin.contact-messages.show', $message))
            ->assertSessionHas('success');

        $message->refresh();
        $this->assertNotNull($message->quote_id);
        $this->assertSame('bearbeitet', $message->status);

        $quote = Quote::query()->find($message->quote_id);
        $this->assertNotNull($quote);
        $this->assertSame('kontakt', $quote->source);
        $this->assertSame('Hans', $quote->first_name);
        $this->assertSame('Schmidt', $quote->last_name);
        $this->assertSame('hans@example.com', $quote->email);
        $this->assertSame($eventType->id, $quote->event_type_id);
        $this->assertSame($message->message, $quote->message);
    }

    public function test_creating_quote_twice_reuses_the_same_quote(): void
    {
        $message = $this->makeMessage();

        $this->actingAs($this->admin)
            ->post(route('admin.contact-messages.create-quote', $message))
            ->assertRedirect();

        $quoteId = $message->fresh()->quote_id;

        $this->actingAs($this->admin)
            ->post(route('admin.contact-messages.create-quote', $message))
            ->assertRedirect();

        $this->assertSame($quoteId, $message->fresh()->quote_id);
        $this->assertSame(1, Quote::query()->count());
    }

    public function test_admin_cannot_send_pdf_before_quote_items_exist(): void
    {
        $message = $this->makeMessage();

        $this->actingAs($this->admin)
            ->post(route('admin.contact-messages.create-quote', $message))
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->from(route('admin.contact-messages.show', $message))
            ->post(route('admin.contact-messages.send-pdf', $message))
            ->assertRedirect(route('admin.contact-messages.show', $message))
            ->assertSessionHas('error');
    }

    public function test_admin_can_send_quote_pdf_from_contact_message(): void
    {
        Mail::fake();

        $message = $this->makeMessage();
        $this->actingAs($this->admin)
            ->post(route('admin.contact-messages.create-quote', $message))
            ->assertRedirect();

        $quote = $message->fresh()->quote;
        $quote->items()->create([
            'name' => 'Buffet Deluxe',
            'quantity' => 1,
            'unit_price' => 100,
            'persons' => 25,
            'line_total' => 100,
        ]);
        $quote->update([
            'subtotal' => 93.46,
            'tax_amount' => 6.54,
            'total' => 100,
        ]);

        $pdfPath = storage_path('app/testing-quote.pdf');
        file_put_contents($pdfPath, 'pdf');

        $this->mock(PdfService::class, function ($mock) use ($pdfPath) {
            $mock->shouldReceive('generateQuotePdf')->once()->andReturn($pdfPath);
        });

        $this->actingAs($this->admin)
            ->post(route('admin.contact-messages.send-pdf', $message->fresh()))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('angebot_erstellt', $quote->fresh()->status);
        Mail::assertSent(QuoteProposalMail::class, function (QuoteProposalMail $mail) use ($quote) {
            return $mail->hasTo($quote->email)
                && $mail->quote->is($quote);
        });
    }

    public function test_contact_show_page_has_quote_actions(): void
    {
        $message = $this->makeMessage();

        $this->actingAs($this->admin)
            ->get(route('admin.contact-messages.show', $message))
            ->assertOk()
            ->assertSee('Teklif kaydı oluştur')
            ->assertSee('Yapay zekâ ile teklif oluştur');
    }

    protected function makeMessage(): ContactMessage
    {
        return ContactMessage::query()->create([
            'name' => 'Hans Schmidt',
            'email' => 'hans@example.com',
            'phone' => '+49 511 333333',
            'subject' => 'Frage zum Catering',
            'event_type' => 'Hochzeit',
            'event_date' => now()->addDays(30)->toDateString(),
            'message' => 'Bitte um ein Angebot für unsere Hochzeit.',
            'source' => 'website',
            'gdpr_consent' => true,
        ]);
    }
}
