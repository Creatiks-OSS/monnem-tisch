<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\DeliveryZone;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class CompanyAddressSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SettingsSeeder::class);

        (new PageSeeder)->seedLegalPages();

        $this->call(FaqSeeder::class);

        $mannheimPlz = ['68159', '68161', '68163', '68165', '68167', '68169', '68199', '68219', '68229', '68239', '68259', '68305', '68307', '68309'];

        $stuttgartZone = DeliveryZone::query()->where('name', 'Stuttgart Innenstadt')->first()
            ?? DeliveryZone::query()->where('city', 'Stuttgart')->first();

        if ($stuttgartZone) {
            $stuttgartZone->update([
                'name' => 'Mannheim',
                'city' => 'Mannheim',
                'postal_codes' => $mannheimPlz,
                'delivery_fee' => 0,
                'is_active' => true,
            ]);
        } else {
            DeliveryZone::query()->updateOrCreate(
                ['name' => 'Mannheim'],
                [
                    'city' => 'Mannheim',
                    'postal_codes' => $mannheimPlz,
                    'delivery_fee' => 0,
                    'minimum_order' => 150,
                    'free_delivery_threshold' => 0,
                    'radius_km' => 12,
                    'sort_order' => 1,
                    'price_per_km' => null,
                    'is_active' => true,
                ]
            );
        }

        $stuttgartCity = City::query()->where('slug', 'stuttgart')->first()
            ?? City::query()->where('name', 'Stuttgart')->first();
        $existingMannheim = City::query()->where('slug', 'mannheim')->first();
        $mannheimData = [
            'name' => 'Mannheim',
            'slug' => 'mannheim',
            'postal_code' => '68159',
            'delivery_price' => 0,
            'delivery_radius' => 12,
            'content' => '<p>Monnemtisch Catering beliefert Mannheim mit Premium-Buffets, Fingerfood und Event-Catering. Innerhalb Mannheims ist die Lieferung kostenlos.</p>',
            'faq' => [
                ['q' => 'Liefert ihr in ganz Mannheim?', 'a' => 'Ja, wir decken alle Stadtteile Mannheims ab. Die Lieferung innerhalb Mannheims ist kostenlos.'],
                ['q' => 'Wie hoch ist die Liefergebühr?', 'a' => 'Innerhalb Mannheims kostenlos. Außerhalb 1,00 € je Kilometer ab H1, 10, 68159 Mannheim.'],
            ],
            'meta_title' => 'Catering Mannheim | Monnemtisch Catering',
            'meta_description' => 'Premium Catering und Partyservice in Mannheim – Buffets, Fingerfood und Events von Monnemtisch.',
            'is_active' => true,
        ];

        if ($existingMannheim) {
            $existingMannheim->update(collect($mannheimData)->except('slug')->all());
            if ($stuttgartCity && $stuttgartCity->id !== $existingMannheim->id) {
                $stuttgartCity->update(['is_active' => false]);
            }
        } elseif ($stuttgartCity) {
            $stuttgartCity->update($mannheimData);
        } else {
            City::query()->updateOrCreate(['slug' => 'mannheim'], $mannheimData);
        }

        $about = Page::query()->where('slug', 'ueber-uns')->first();
        if ($about && is_string($about->content) && str_contains($about->content, 'Stuttgart')) {
            $about->update([
                'content' => str_replace('Stuttgart', 'Mannheim', $about->content),
            ]);
        }

        $home = Page::query()->where('slug', 'home')->first();
        if ($home) {
            $home->update([
                'excerpt' => 'Monnemtisch Catering – Premium Catering in Mannheim',
            ]);
            $home->seo()->updateOrCreate([], [
                'title' => 'Monnemtisch Catering | Premium Catering Mannheim',
                'description' => 'Exquisites Catering für Hochzeiten, Firmenfeiern und Events in Mannheim und Umgebung.',
                'keywords' => 'Catering Mannheim, Partyservice, Hochzeitscatering',
            ]);
            $home->sections()
                ->where('type', 'hero')
                ->where('subtitle', 'like', '%Stuttgart%')
                ->update(['subtitle' => 'Von eleganten Buffets bis Fingerfood – Monnemtisch Catering bringt Genuss nach Mannheim.']);
            $home->sections()
                ->where('type', 'delivery_areas')
                ->where('subtitle', 'like', '%Stuttgart%')
                ->update(['subtitle' => 'Mannheim und Umgebung']);
        }

        Cache::flush();
    }
}
