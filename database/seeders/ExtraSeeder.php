<?php

namespace Database\Seeders;

use App\Models\Extra;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExtraSeeder extends Seeder
{
    public function run(): void
    {
        $extras = [
            ['name' => 'Geschirr', 'price' => 2.50, 'price_type' => 'person', 'description' => 'Hochwertiges Geschirr inkl. Lieferung und Abholung.', 'image' => velora_photo('plates')],
            ['name' => 'Besteck', 'price' => 1.80, 'price_type' => 'person', 'description' => 'Edelstahl-Besteckset für Ihr Event.', 'image' => velora_photo('cutlery')],
            ['name' => 'Personal', 'price' => 35.00, 'price_type' => 'hour', 'description' => 'Servicekraft pro Stunde – freundlich und erfahren.', 'image' => velora_photo('staff')],
            ['name' => 'Aufbau', 'price' => 120.00, 'price_type' => 'fixed', 'description' => 'Professioneller Aufbau von Buffet und Stationen.', 'image' => velora_photo('setup')],
            ['name' => 'Abbau', 'price' => 90.00, 'price_type' => 'fixed', 'description' => 'Kompletter Abbau und Entsorgung nach dem Event.', 'image' => velora_photo('teardown')],
            ['name' => 'Getränke Service', 'price' => 4.50, 'price_type' => 'person', 'description' => 'Getränkeausgabe und Nachfüllen während der Veranstaltung.', 'image' => velora_photo('cheers')],
            ['name' => 'Tischdekoration', 'price' => 8.90, 'price_type' => 'stück', 'description' => 'Elegante Tischdekoration pro Tisch.', 'image' => velora_photo('decor')],
            ['name' => 'Warmhaltebehälter', 'price' => 25.00, 'price_type' => 'stück', 'description' => 'Professionelle Warmhaltebehälter pro Einheit.', 'image' => velora_photo('chafing')],
            ['name' => 'Live Cooking', 'price' => 250.00, 'price_type' => 'fixed', 'description' => 'Live-Cooking-Station mit Koch vor Ort.', 'image' => velora_photo('chef')],
            ['name' => 'Servicekräfte', 'price' => 28.00, 'price_type' => 'hour', 'description' => 'Zusätzliche Servicekräfte pro Stunde.', 'image' => velora_photo('staff')],
            ['name' => 'Gläser Set', 'price' => 1.50, 'price_type' => 'person', 'description' => 'Glasware für Wasser, Wein und Sekt.', 'image' => velora_photo('glasses')],
            ['name' => 'Buffetausstattung', 'price' => 75.00, 'price_type' => 'fixed', 'description' => 'Tischwäsche, Chafing Dishes und Präsentationsmaterial.', 'image' => velora_photo('linen')],
        ];

        foreach ($extras as $index => $row) {
            Extra::query()->updateOrCreate(
                ['slug' => Str::slug($row['name'])],
                [
                    'kind' => 'service',
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'image' => $row['image'],
                    'price' => $row['price'],
                    'price_type' => $row['price_type'],
                    'minimum_quantity' => 1,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }

        $drinks = [
            ['name' => 'Wasser still 0,2l', 'price' => 1.50],
            ['name' => 'Coca Cola Zero 0,2l', 'price' => 2.50],
            ['name' => 'Bionade Zitrone-Bergamotte 0,3l', 'price' => 2.50],
            ['name' => 'Apfelsaft 0,2l', 'price' => 2.50],
            ['name' => 'Orangensaft 0,2l', 'price' => 2.50],
            ['name' => 'Coca Cola 0,2l', 'price' => 2.50],
            ['name' => 'Sprite 0,2L', 'price' => 2.50],
            ['name' => 'Fanta 0,2l', 'price' => 2.50],
            ['name' => 'Bionade Holunder 0,3l', 'price' => 2.50],
        ];

        foreach ($drinks as $index => $row) {
            Extra::query()->updateOrCreate(
                ['slug' => Str::slug($row['name'])],
                [
                    'kind' => 'product',
                    'name' => $row['name'],
                    'description' => null,
                    'image' => velora_drink_photo($row['name']) ?? velora_photo('cheers'),
                    'price' => $row['price'],
                    'price_type' => 'stück',
                    'minimum_quantity' => 1,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
