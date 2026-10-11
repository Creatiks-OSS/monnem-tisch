<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'category' => 'general',
                'question' => 'Wie früh sollte ich mein Catering buchen?',
                'answer' => 'Wir empfehlen eine Buchung mindestens 2–4 Wochen im Voraus. Für Hochzeiten und große Events idealerweise 6–8 Wochen vorher.',
            ],
            [
                'category' => 'delivery',
                'question' => 'In welche Gebiete liefert Monnemtisch Catering?',
                'answer' => 'Wir liefern in Mannheim und Umgebung. Innerhalb Mannheims ist die Lieferung kostenlos; außerhalb berechnen wir 1,00 € je Kilometer ab H1, 10, 68159 Mannheim.',
            ],
            [
                'category' => 'allergens',
                'question' => 'Können Allergene und Unverträglichkeiten berücksichtigt werden?',
                'answer' => 'Ja. Bitte teilen Sie uns Allergene und Unverträglichkeiten bei der Bestellung oder Anfrage mit. Viele Gerichte sind vegetarisch, vegan oder glutenfrei anpassbar.',
            ],
            [
                'category' => 'booking',
                'question' => 'Wie funktioniert die Angebotsanfrage?',
                'answer' => 'Über unser Formular unter „Angebot anfordern“ erhalten wir Ihre Eckdaten und melden uns zeitnah mit einem individuellen Angebot.',
            ],
            [
                'category' => 'general',
                'question' => 'Gibt es eine Mindestbestellmenge?',
                'answer' => 'Je nach Produkt liegt die Mindestpersonenanzahl meist bei 10 Personen. Details finden Sie auf den jeweiligen Produktseiten.',
            ],
            [
                'category' => 'delivery',
                'question' => 'Wie lange im Voraus muss die Lieferung geplant werden?',
                'answer' => 'In der Regel benötigen wir 48 Stunden Vorlauf. Bei kurzfristigen Anfragen prüfen wir die Verfügbarkeit individuell.',
            ],
            [
                'category' => 'booking',
                'question' => 'Kann ich Menüs individuell zusammenstellen?',
                'answer' => 'Absolut. Nutzen Sie unseren Konfigurator oder sprechen Sie uns direkt an – wir erstellen passende Menükombinationen für Ihren Anlass.',
            ],
            [
                'category' => 'allergens',
                'question' => 'Sind die Speisen frisch zubereitet?',
                'answer' => 'Ja, wir bereiten unsere Speisen frisch und verwenden hochwertige Zutaten – auch regional, wo möglich.',
            ],
            [
                'category' => 'general',
                'question' => 'Bieten Sie auch Personal und Geschirr an?',
                'answer' => 'Ja. Als Extras können Sie u. a. Geschirr, Besteck, Aufbau/Abbau, Live Cooking und Servicekräfte dazu buchen.',
            ],
            [
                'category' => 'payment',
                'question' => 'Welche Zahlungsmöglichkeiten gibt es?',
                'answer' => 'Sie können Ihre Bestellung als Anfrage oder auf Rechnung abwickeln. Weitere Optionen besprechen wir gerne im Angebot.',
            ],
            [
                'category' => 'booking',
                'question' => 'Was passiert bei Stornierung?',
                'answer' => 'Stornierungsbedingungen hängen vom Zeitpunkt und Umfang ab und sind in unseren AGB geregelt. Sprechen Sie uns frühzeitig an.',
            ],
            [
                'category' => 'delivery',
                'question' => 'Liefert ihr auch an Wochenenden?',
                'answer' => 'Ja, wir liefern auch samstags. Sonntags nach Absprache – abhängig von Verfügbarkeit und Eventgröße.',
            ],
        ];

        foreach ($faqs as $index => $row) {
            Faq::query()->updateOrCreate(
                ['question' => $row['question']],
                [
                    'category' => $row['category'],
                    'answer' => $row['answer'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
