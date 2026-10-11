<?php

namespace App\Services;

class ProductCalorieCatalog
{
    /**
     * Researched kcal per typical catering portion (not per 100 g).
     *
     * Sources: kalorientabelle.net, FDDB, Mahlzait, typical German catering
     * serving sizes. Same dish name always shares one value across menus.
     *
     * Portions used:
     * - Vorspeisen: buffet share ~80–150 g / 2–3 pieces
     * - Hauptgerichte: plated catering portion ~250–350 g
     * - Beilagen: ~150–180 g
     * - Desserts: ~100–130 g
     * - Fingerfood: 1–2 pieces or glass
     * - Breakfast: 1 piece / ~80–150 g
     * - Grill: 1 steak (~160–180 g) or 2–3 sausages / skewers
     *
     * @return array<string, int>
     */
    public function byName(): array
    {
        return [
            // Vorspeisen / shared platters
            'Antipasti Platte' => 180,
            'Auberginen-Carpaccio' => 80,
            "Baba's Knusperplatte" => 340,
            'Brotkorb & Butter' => 240,
            'Bruschetta' => 140,
            'Caesar Salad' => 230,
            'Chicken Nuggets Platte' => 280,
            'Crostini mit Ziegenkäse' => 155,
            'Datteln im Speckmantel' => 150,
            'Fiesta Mexicana Platte' => 365,
            'Frühlingsrollen Thai Style' => 190,
            'Garnelen Spieße' => 110,
            'Gartensalat' => 105,
            'Gemüsesticks Platte' => 95,
            'Griechischer Salat' => 200,
            'Große Wurstplatte' => 240,
            'Hackfleischbällchen' => 220,
            'Halbe gefüllte Eier' => 130,
            'Kartoffelsalat' => 180,
            'Käseplatte' => 275,
            'Lachs Canapé' => 150,
            'Lachsplatte' => 160,
            'Melonen-Feta-Salat' => 135,
            'Melonenschiffchen' => 65,
            'Mini Cheeseburger' => 215,
            'Mini Cheese Burger' => 215,
            'Mini Crispy Chicken Burger' => 230,
            'Mini Pizza-Mix' => 215,
            'Mini Quiche Mix-Platte' => 230,
            'Mini Veggi Burger' => 160,
            'Mini-Döner' => 175,
            'Mini-Schnitzel Platte' => 185,
            'Nudelsalat Mediterran' => 170,
            'Oriental Mezze Platte' => 215,
            'Orientalische Hähnchenspieße' => 135,
            'Samosas Indische Art' => 175,
            'Tomate Mozzarella' => 180,
            'Vitello Tonnato' => 190,

            // Hauptgerichte
            'Backfisch & Remoulade' => 500,
            'Cheeseburger Station' => 550,
            'Chicken Tikka Masala' => 450,
            'Currywurst' => 410,
            'Fettucine Salmone' => 440,
            'Gefüllte Paprika' => 380,
            'Gnocchi Spinaci' => 450,
            'Hähnchen Cordon Bleu' => 490,
            'Hähnchen-Jägerschnitzel' => 440,
            'Hähnchenbrust Caprese' => 380,
            'Hähnchenbrustfilet' => 300,
            'Hähnchengeschnetzeltes' => 300,
            'Hühnerfrikassee' => 330,
            'Karotte Kartoffel Ingwer Suppe' => 140,
            'Kartoffelgratin' => 400,
            'Lachs-Brokkoli Auflauf' => 450,
            'Lachsfilet' => 330,
            'Lasagne Bolognese' => 510,
            'Linguini mit Spargel' => 420,
            'Paella Pollo' => 420,
            'Pangasiusfilet' => 290,
            'Pasta Station Duo' => 450,
            'Putensteak Mediterran' => 310,
            'Ratatouille de Provence' => 200,
            'Rinder Hüftsteaks' => 360,
            'Rinderbraten' => 410,
            'Rindergulasch' => 360,
            'Schnitzel' => 430,
            'Spargel & Sauce Hollandaise' => 320,
            'Veganes Chili sin Carne' => 300,
            'Vegetarische Lasagne' => 400,
            'Wiener Würstchen' => 380,

            // Beilagen
            'Baguettescheiben' => 140,
            'Basmati Reis' => 195,
            'Bratkartoffeln' => 250,
            'Gemüsepfanne' => 90,
            'Kaisergemüse' => 70,
            'Kartoffelecken' => 320,
            'Kartoffelklöße' => 290,
            'Penne Nudeln' => 290,
            'Rosmarinkartoffeln' => 270,
            'Rotkraut' => 80,
            'Sauerkraut' => 40,
            'Spätzle' => 270,
            'Tagliatelle' => 270,

            // Desserts
            'Mousse au chocolat' => 280,
            'Dubai-Schoko-Tiramisu' => 420,
            'Tiramisu' => 340,
            'Erdbeer-Tiramisu' => 310,
            'Himbeer-Mascarponecreme' => 265,
            'Panna Cotta' => 240,
            'Erdbeer-Minz Quark' => 135,
            'Zitronencreme-Panna-Cotta' => 230,
            'Rote Grütze mit Vanillecreme' => 175,
            'Oreo Creme Dessert' => 335,
            'Obstplatte' => 100,
            'Kaiserschmarrn' => 430,
            'Apfelstrudel' => 320,
            'Käsekuchen' => 350,
            'Obstkuchen' => 265,

            // Fingerfood pieces
            'Hähnchen Wrap' => 320,
            'Lachs-Ei Wrap' => 340,
            'Tomaten Wrap' => 270,
            'Hähnchen Canape' => 85,
            'Pastrami Canape' => 95,
            'Ciabatta Pastrami-Käse' => 240,
            'Ciabatta Tomate-Mozzarella' => 230,
            'Käsebrötchen' => 280,
            'Tomate-Mozzarella Brötchen' => 260,
            'Lachs Bagel' => 410,
            'Käse-Schinken Bagel' => 400,
            'Avocado Bagel' => 360,
            'Clubsandwich' => 350,
            'Mini-Sandwich-Mix' => 225,
            'Melone-Feta-Salat im Glas' => 110,
            'Kartoffelsalat im Glas' => 145,
            'Nudelsalat im Glas' => 170,
            'Caesar Salad im Glas' => 230,
            'Tomate-Mozzarella Spieß' => 70,
            'Käsespieße' => 140,
            'Hähnchen-Ananas Spieß' => 80,
            'Garnelen Spieß' => 50,
            'Vegetarische Grillspieße' => 70,
            'Dattel Spieße' => 110,
            'Melonenplatte' => 70,

            // Frühstück
            'Große Käseplatte' => 390,
            'Brot- und Brötchenkorb' => 210,
            'Laugengebäck Mix' => 280,
            'Mettbrötchen' => 380,
            'Salamibrötchen' => 280,
            'Bacon-Ei Bagel' => 420,
            'Herzhafte Aufstriche' => 130,
            'Eiersalat' => 155,
            'Gekochte Eier' => 80,
            'Marmeladen Nutella Mix' => 190,
            'Quark & Honig' => 160,
            'Berliner' => 360,
            'Schoko Croissants' => 300,
            'Mini-Muffin Platte' => 300,
            'Blechkuchen' => 350,

            'Rührei' => 230,
            'Rührei mit Gemüse' => 210,
            'Rührei mit Speck' => 340,
            'Bratkartoffeln mit Gemüse' => 235,
            'Schweizer Rösti mit Spiegelei' => 415,
            'Nürnberger Würstchen' => 250,
            'Weißwurst & Brezel' => 420,
            'Pfannkuchen mit Ahornsirup' => 500,
            'French Toasts' => 400,
            'Waffeln mit Beeren' => 390,

            // Grill
            'Hähnchensteak Honig-Senf' => 340,
            'Hähnchensteak Mediterran' => 330,
            'Putensteak Provence' => 290,
            'Bratwürste' => 375,
            'Vegetarische Bratwürste' => 200,
            'Puten Schaschlik Spieße' => 225,
            'Hähnchen-Champignon Spieß' => 170,
            'Kebab Grillspieße' => 290,
            'Limonen-Lachsfilet' => 330,
            'Chicken Wings Honey-BBQ' => 420,
            'Nürnberger Rostbratwürste' => 300,
            'Käsewürstchen' => 320,
            'Halloumi-Gemüse Spieße' => 250,
            'BBQ Tofu-Spieße' => 180,
            'Halloumi Grillkäse' => 320,
            'Maiskolben' => 150,
            'Grillgemüse' => 120,
            'Grillkartoffeln mit Quark' => 305,
            'Kräuterbaguette vom Grill' => 240,
            'Gegrillte Ananas' => 75,
        ];
    }

    public function forName(string $name, ?string $categorySlug = null): int
    {
        return $this->byName()[$name] ?? $this->categoryDefault($categorySlug);
    }

    public function categoryDefault(?string $categorySlug): int
    {
        return match ($categorySlug) {
            'vorspeisen' => 180,
            'hauptgerichte' => 400,
            'beilagen' => 200,
            'desserts' => 280,
            'fingerfood' => 160,
            'kalte-fruehstueck' => 250,
            'warme-speisen' => 320,
            'grillgut-fleisch' => 330,
            'grillgut-beilagen' => 180,
            default => 200,
        };
    }
}
