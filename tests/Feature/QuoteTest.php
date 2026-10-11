<?php

namespace Tests\Feature;

use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_angebot_form_creates_quote(): void
    {
        $response = $this->post(route('quote.store'), [
            'first_name' => 'Erika',
            'last_name' => 'Musterfrau',
            'email' => 'erika@example.com',
            'phone' => '+49 511 222222',
            'company' => 'Event AG',
            'event_date' => now()->addDays(21)->toDateString(),
            'persons' => 50,
            'location' => 'Hannover Congress Centrum',
            'budget' => 2500,
            'catering_wunsch' => 'Buffet und Fingerfood',
            'message' => 'Bitte um ein Angebot für unsere Firmenfeier.',
            'gdpr_consent' => '1',
        ]);

        $quote = Quote::query()->latest('id')->first();
        $this->assertNotNull($quote);
        $response->assertRedirect(route('quote.success', $quote->quote_number));

        $this->assertDatabaseHas('quotes', [
            'email' => 'erika@example.com',
            'first_name' => 'Erika',
            'status' => 'neu',
            'source' => 'website',
        ]);
    }
}
