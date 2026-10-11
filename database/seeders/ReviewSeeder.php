<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            ['name' => 'Anna Müller', 'company' => null, 'rating' => 5, 'title' => 'Perfekte Hochzeit', 'body' => 'Das Catering zu unserer Hochzeit war einfach traumhaft. Alles frisch, stilvoll präsentiert und das Team unglaublich freundlich.', 'image' => '/images/reviews/anna-mueller.webp'],
            ['name' => 'Thomas Weber', 'company' => 'Weber Consulting', 'rating' => 5, 'title' => 'Top für Firmenevents', 'body' => 'Wir buchen Monnemtisch regelmäßig für Kundenveranstaltungen. Pünktlich, professionell und immer lecker.', 'image' => '/images/reviews/thomas-weber.webp'],
            ['name' => 'Lisa Hoffmann', 'company' => null, 'rating' => 5, 'title' => 'Fingerfood vom Feinsten', 'body' => 'Die Fingerfood-Auswahl war ein Highlight auf unserer Geburtstagsfeier. Gäste waren begeistert!', 'image' => '/images/reviews/lisa-hoffmann.webp'],
            ['name' => 'Markus Schneider', 'company' => 'Schneider GmbH', 'rating' => 4, 'title' => 'Sehr gutes Business Catering', 'body' => 'Für unser Jahresmeeting genau richtig. Gute Abstimmung und klare Kommunikation im Vorfeld.', 'image' => '/images/reviews/markus-schneider.webp'],
            ['name' => 'Julia Becker', 'company' => null, 'rating' => 5, 'title' => 'Empfehlung aus Mannheim', 'body' => 'Lokaler Partyservice mit Premium-Anspruch. Besonders das mediterrane Buffet war hervorragend.', 'image' => '/images/reviews/julia-becker.webp'],
            ['name' => 'Peter Klein', 'company' => 'Expo Partner AG', 'rating' => 5, 'title' => 'Zuverlässig auf der Messe', 'body' => 'Messe-Catering ohne Stress – Snacks und Getränke kamen pünktlich und in Top-Qualität.', 'image' => '/images/reviews/peter-klein.webp'],
            ['name' => 'Sarah Neumann', 'company' => null, 'rating' => 4, 'title' => 'Tolles Grillbuffet', 'body' => 'Live-Cooking und Grillpaket haben unsere Gartenparty gerettet. Nächstes Jahr wieder!', 'image' => '/images/reviews/sarah-neumann.webp'],
            ['name' => 'Daniel Richter', 'company' => 'Richter & Co.', 'rating' => 5, 'title' => 'Professionell und lecker', 'body' => 'Von der Beratung bis zur Lieferung alles stimmig. Absolut empfehlenswert für Business Events.', 'image' => '/images/reviews/daniel-richter.webp'],
            ['name' => 'Claudia Vogt', 'company' => null, 'rating' => 5, 'title' => 'Vegetarisch genial', 'body' => 'Endlich ein Caterer, der vegetarische und vegane Optionen ernst nimmt – und sie schmecken fantastisch.', 'image' => '/images/reviews/claudia-vogt.webp'],
            ['name' => 'Michael Braun', 'company' => 'Braun Events', 'rating' => 4, 'title' => 'Sehr gute Zusammenarbeit', 'body' => 'Flexible Mengenanpassung und faire Preise. Für unsere Eventplanung ein fester Partner.', 'image' => '/images/reviews/michael-braun.webp'],
            ['name' => 'Nina Krüger', 'company' => null, 'rating' => 5, 'title' => 'Wunderschöne Präsentation', 'body' => 'Nicht nur der Geschmack, auch die Optik war erstklassig. Gäste haben ständig Fotos gemacht.', 'image' => '/images/reviews/nina-krueger.webp'],
            ['name' => 'Stefan Lange', 'company' => 'Lange Media', 'rating' => 5, 'title' => 'Frühstück fürs Team', 'body' => 'Business Frühstück war frisch und abwechslungsreich. Unser Team war rundum zufrieden.', 'image' => '/images/reviews/stefan-lange.webp'],
        ];

        foreach ($reviews as $index => $row) {
            Review::query()->updateOrCreate(
                [
                    'name' => $row['name'],
                    'title' => $row['title'],
                ],
                [
                    'company' => $row['company'],
                    'rating' => $row['rating'],
                    'body' => $row['body'],
                    'image' => $row['image'],
                    'is_featured' => true,
                    'is_approved' => true,
                    'source' => 'manual',
                    'reviewed_at' => now()->subDays($index * 3),
                ]
            );
        }
    }
}
