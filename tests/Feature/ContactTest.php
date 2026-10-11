<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\ContactMessage;
use Database\Seeders\BusinessHourSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_creates_message(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Hans Schmidt',
            'email' => 'hans@example.com',
            'phone' => '+49 511 333333',
            'subject' => 'Frage zum Catering',
            'event_type' => 'Hochzeit',
            'message' => 'Hallo, ich hätte gerne Informationen zu Ihren Hochzeitspaketen.',
            'gdpr_consent' => '1',
            'gclid' => 'test123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('lead_tracked', true);
        $this->followRedirects($response)
            ->assertOk()
            ->assertSee('generate_lead', false)
            ->assertSee('contact_form', false);
        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Hans Schmidt',
            'email' => 'hans@example.com',
            'subject' => 'Frage zum Catering',
            'source' => 'website',
            'status' => 'neu',
            'gclid' => 'test123',
        ]);

        $this->assertSame(1, ContactMessage::query()->count());
    }

    public function test_contact_page_shows_business_hours(): void
    {
        $this->seed(BusinessHourSeeder::class);

        $this->get(route('contact.show'))
            ->assertOk()
            ->assertSee('Öffnungszeiten')
            ->assertSee('Eventdatum')
            ->assertSee('Datum wählen')
            ->assertSee('Montag')
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('Samstag')
            ->assertSee('10:00')
            ->assertSee('14:00')
            ->assertSee('Sonntag')
            ->assertSee('Geschlossen');

        $sunday = BusinessHour::query()->where('day_of_week', 0)->first();
        $this->assertNotNull($sunday);
        $this->assertSame('Geschlossen', $sunday->hoursLabel());
    }
}
