<?php

namespace App\Services;

use App\Models\Product;

class AllergenCatalog
{
    /**
     * Official kitchen codes used on menus and the Allergene page.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        return [
            'a' => 'Glutenhaltiges Getreide',
            'a1' => 'Glutenhaltiges Getreide (Weizen)',
            'a2' => 'Glutenhaltiges Getreide (Dinkel)',
            'a3' => 'Glutenhaltiges Getreide (Hafer)',
            'a4' => 'Glutenhaltiges Getreide (Gerste)',
            'a5' => 'Glutenhaltiges Getreide (Roggen)',
            'b' => 'Krebstiere und Krebstiererzeugnisse',
            'c' => 'Eier und Eiererzeugnisse',
            'd' => 'Fisch und Fischerzeugnisse',
            'e' => 'Erdnüsse und Erdnusserzeugnisse',
            'f' => 'Soja und Sojaerzeugnisse',
            'g' => 'Milch und Milcherzeugnisse (inkl. Laktose)',
            'h' => 'Schalenfrüchte (Mandeln, Haselnüsse usw.) und daraus hergestellte Erzeugnisse',
            'i' => 'Hülsenfrüchte',
            'l' => 'Sellerie und Sellerieerzeugnisse',
            'm' => 'Senf und Senferzeugnisse',
            'n' => 'Sesamsamen und daraus hergestellte Erzeugnisse',
            'o' => 'Schwefeldioxid/Sulfite (als SO2) > 10 mg/kg',
            'p' => 'Lupine und Lupinenerzeugnisse',
            'r' => 'Weichtiere und Weichtiererzeugnisse',
        ];
    }

    /**
     * @return list<string>
     */
    public function codes(): array
    {
        return array_keys($this->all());
    }

    public function label(string $code): string
    {
        return $this->all()[strtolower(trim($code))] ?? strtoupper($code);
    }

    /**
     * @param  list<string>|string|null  $codes
     * @return list<string>
     */
    public function normalize(mixed $codes): array
    {
        $items = is_array($codes) ? $codes : (preg_split('/[\s,;\/|]+/', (string) $codes) ?: []);
        $known = $this->all();
        $out = [];

        foreach ($items as $code) {
            $key = strtolower(trim((string) $code));
            if ($key !== '' && isset($known[$key])) {
                $out[] = $key;
            }
        }

        return $this->sortCodes(array_values(array_unique($out)));
    }

    /**
     * @return list<string>
     */
    public function forProduct(Product $product): array
    {
        $stored = $this->normalize($product->allergens);
        if ($stored !== []) {
            return $stored;
        }

        $named = $this->byName()[$this->nameKey($product->name)] ?? null;
        if (is_array($named)) {
            return $this->applyDietFlags($this->normalize($named), $product);
        }

        return $this->infer($product);
    }

    /**
     * @param  list<string>  $codes
     * @return list<array{code: string, label: string}>
     */
    public function badges(array $codes): array
    {
        return array_map(fn (string $code) => [
            'code' => $code,
            'label' => $this->label($code),
        ], $this->normalize($codes));
    }

    /**
     * @return array<string, list<string>>
     */
    public function byName(): array
    {
        return [
            'antipasti platte' => ['a1', 'g', 'l'],
            'auberginen-carpaccio' => ['n'],
            "baba's knusperplatte" => ['a1', 'c', 'g', 'n'],
            'brotkorb & butter' => ['a1', 'g'],
            'bruschetta' => ['a1'],
            'caesar salad' => ['a1', 'c', 'd', 'g', 'm'],
            'chicken nuggets platte' => ['a1', 'c', 'g'],
            'crostini mit ziegenkäse' => ['a1', 'g'],
            'datteln im speckmantel' => [],
            'fiesta mexicana platte' => ['a1', 'g', 'i'],
            'frühlingsrollen thai style' => ['a1', 'f', 'n'],
            'garnelen spieße' => ['b'],
            'gartensalat' => ['m'],
            'gemüsesticks platte' => [],
            'griechischer salat' => ['g'],
            'große wurstplatte' => ['m'],
            'hackfleischbällchen' => ['a1', 'c', 'g'],
            'halbe gefüllte eier' => ['c', 'm'],
            'kartoffelsalat' => ['c', 'm'],
            'käseplatte' => ['g'],
            'lachs canapé' => ['a1', 'd', 'g'],
            'lachsplatte' => ['d'],
            'melonen-feta-salat' => ['g'],
            'melonenschiffchen' => [],
            'mini cheeseburger' => ['a1', 'c', 'g', 'n'],
            'mini cheese burger' => ['a1', 'c', 'g', 'n'],
            'mini crispy chicken burger' => ['a1', 'c', 'g', 'n'],
            'mini pizza-mix' => ['a1', 'g'],
            'mini quiche mix-platte' => ['a1', 'c', 'g'],
            'mini veggi burger' => ['a1', 'g', 'i', 'n'],
            'mini-döner' => ['a1', 'g', 'n'],
            'mini-schnitzel platte' => ['a1', 'c', 'g'],
            'nudelsalat mediterran' => ['a1', 'c', 'g'],
            'oriental mezze platte' => ['a1', 'g', 'i', 'n'],
            'orientalische hähnchenspieße' => ['n'],
            'samosas indische art' => ['a1', 'i'],
            'tomate mozzarella' => ['g'],
            'vitello tonnato' => ['c', 'd', 'g'],
            'backfisch & remoulade' => ['a1', 'c', 'd', 'm'],
            'cheeseburger station' => ['a1', 'c', 'g', 'n'],
            'chicken tikka masala' => ['g', 'i'],
            'currywurst' => ['m'],
            'fettucine salmone' => ['a1', 'd', 'g'],
            'gefüllte paprika' => ['g', 'i'],
            'gnocchi spinaci' => ['a1', 'c', 'g'],
            'hähnchen cordon bleu' => ['a1', 'c', 'g'],
            'hähnchen-jägerschnitzel' => ['a1', 'c', 'g'],
            'hähnchenbrust caprese' => ['g'],
            'hähnchenbrustfilet' => [],
            'hähnchengeschnetzeltes' => ['g', 'l'],
            'hühnerfrikassee' => ['a1', 'c', 'g', 'l'],
            'karotte kartoffel ingwer suppe' => ['l'],
            'kartoffelgratin' => ['g'],
            'lachs-brokkoli auflauf' => ['d', 'g'],
            'lachsfilet' => ['d'],
            'lasagne bolognese' => ['a1', 'c', 'g', 'l'],
            'linguini mit spargel' => ['a1', 'g'],
            'paella pollo' => ['l'],
            'pangasiusfilet' => ['d'],
            'pasta station duo' => ['a1', 'c', 'g'],
            'putensteak mediterran' => [],
            'ratatouille de provence' => ['l'],
            'rinder hüftsteaks' => [],
            'rinderbraten' => ['l'],
            'rindergulasch' => ['l'],
            'schnitzel' => ['a1', 'c', 'g'],
            'spargel & sauce hollandaise' => ['c', 'g'],
            'veganes chili sin carne' => ['i'],
            'vegetarische lasagne' => ['a1', 'c', 'g', 'l'],
            'wiener würstchen' => ['m'],
            'baguettescheiben' => ['a1'],
            'basmati reis' => [],
            'bratkartoffeln' => [],
            'gemüsepfanne' => ['l'],
            'kaisergemüse' => ['l'],
            'kartoffelecken' => [],
            'kartoffelklöße' => ['c', 'g'],
            'penne nudeln' => ['a1'],
            'rosmarinkartoffeln' => [],
            'rotkraut' => [],
            'sauerkraut' => [],
            'spätzle' => ['a1', 'c', 'g'],
            'tagliatelle' => ['a1', 'c', 'g'],
            'mousse au chocolat' => ['c', 'g'],
            'dubai-schoko-tiramisu' => ['a1', 'c', 'g', 'h'],
            'tiramisu' => ['a1', 'c', 'g'],
            'erdbeer-tiramisu' => ['a1', 'c', 'g'],
            'himbeer-mascarponecreme' => ['c', 'g'],
            'panna cotta' => ['g'],
            'erdbeer-minz quark' => ['g'],
            'zitronencreme-panna-cotta' => ['g'],
            'rote grütze mit vanillecreme' => ['g'],
            'oreo creme dessert' => ['a1', 'c', 'g', 'f'],
            'obstplatte' => [],
            'kaiserschmarrn' => ['a1', 'c', 'g'],
            'apfelstrudel' => ['a1', 'c', 'g'],
            'käsekuchen' => ['a1', 'c', 'g'],
            'obstkuchen' => ['a1', 'c', 'g'],
            'hähnchen wrap' => ['a1', 'c', 'g'],
            'lachs-ei wrap' => ['a1', 'c', 'd', 'g'],
            'tomaten wrap' => ['a1', 'g'],
            'hähnchen canape' => ['a1', 'c', 'g'],
            'pastrami canape' => ['a1', 'g', 'm'],
            'ciabatta pastrami-käse' => ['a1', 'g', 'm'],
            'ciabatta tomate-mozzarella' => ['a1', 'g'],
            'käsebrötchen' => ['a1', 'g'],
            'tomate-mozzarella brötchen' => ['a1', 'g'],
            'lachs bagel' => ['a1', 'd', 'g'],
            'käse-schinken bagel' => ['a1', 'g'],
            'avocado bagel' => ['a1', 'g'],
            'clubsandwich' => ['a1', 'c', 'g'],
            'mini-sandwich-mix' => ['a1', 'c', 'g'],
            'melone-feta-salat im glas' => ['g'],
            'kartoffelsalat im glas' => ['c', 'm'],
            'nudelsalat im glas' => ['a1', 'c', 'g'],
            'caesar salad im glas' => ['a1', 'c', 'd', 'g', 'm'],
            'tomate-mozzarella spieß' => ['g'],
            'käsespieße' => ['g'],
            'hähnchen-ananas spieß' => [],
            'garnelen spieß' => ['b'],
            'vegetarische grillspieße' => [],
            'dattel spieße' => [],
            'melonenplatte' => [],
            'große käseplatte' => ['g'],
            'brot- und brötchenkorb' => ['a1', 'g'],
            'laugengebäck mix' => ['a1'],
            'mettbrötchen' => ['a1'],
            'salamibrötchen' => ['a1'],
            'bacon-ei bagel' => ['a1', 'c', 'g'],
            'herzhafte aufstriche' => ['g'],
            'eiersalat' => ['c', 'm'],
            'gekochte eier' => ['c'],
            'marmeladen nutella mix' => ['a1', 'f', 'g', 'h'],
            'quark & honig' => ['g'],
            'berliner' => ['a1', 'c', 'g'],
            'schoko croissants' => ['a1', 'c', 'g'],
            'mini-muffin platte' => ['a1', 'c', 'g'],
            'blechkuchen' => ['a1', 'c', 'g'],
            'rührei' => ['c', 'g'],
            'rührei mit gemüse' => ['c', 'g'],
            'rührei mit speck' => ['c', 'g'],
            'bratkartoffeln mit gemüse' => [],
            'schweizer rösti mit spiegelei' => ['c', 'g'],
            'nürnberger würstchen' => ['m'],
            'weißwurst & brezel' => ['a1', 'c', 'g', 'm'],
            'pfannkuchen mit ahornsirup' => ['a1', 'c', 'g'],
            'french toasts' => ['a1', 'c', 'g'],
            'waffeln mit beeren' => ['a1', 'c', 'g'],
            'hähnchensteak honig-senf' => ['m'],
            'hähnchensteak mediterran' => [],
            'putensteak provence' => [],
            'bratwürste' => ['m'],
            'vegetarische bratwürste' => ['a1', 'f', 'i'],
            'puten schaschlik spieße' => [],
            'hähnchen-champignon spieß' => [],
            'kebab grillspieße' => ['n'],
            'limonen-lachsfilet' => ['d'],
            'chicken wings honey-bbq' => ['m'],
            'nürnberger rostbratwürste' => ['m'],
            'käsewürstchen' => ['g', 'm'],
            'halloumi-gemüse spieße' => ['g'],
            'bbq tofu-spieße' => ['f'],
            'halloumi grillkäse' => ['g'],
            'maiskolben' => [],
            'grillgemüse' => [],
            'grillkartoffeln mit quark' => ['g'],
            'kräuterbaguette vom grill' => ['a1', 'g'],
            'gegrillte ananas' => [],
        ];
    }

    /**
     * @return list<string>
     */
    protected function infer(Product $product): array
    {
        $hay = mb_strtolower(trim(implode(' ', array_filter([
            $product->name,
            $product->short_description,
            is_array($product->ingredients) ? implode(' ', $product->ingredients) : (string) $product->ingredients,
            $product->description,
        ]))));

        $codes = [];
        $rules = [
            'a2' => ['dinkel'],
            'a3' => ['hafer', 'oat'],
            'a4' => ['gerste', 'malz'],
            'a5' => ['roggen'],
            'a1' => ['weizen', 'brot', 'brötchen', 'semmel', 'burger', 'wrap', 'pita', 'yufka', 'börek', 'borek', 'nudel', 'pasta', 'lasagne', 'linguini', 'fettucine', 'gnocchi', 'pizza', 'quiche', 'crostini', 'bruschetta', 'paniert', 'panade', 'knusper', 'crispy', 'nugget', 'schnitzel', 'cordon', 'samosa', 'frühlingsrolle', 'fruehlingsrolle', 'canapé', 'canape', 'toast', 'croissant', 'waffle', 'kuchen', 'mehl'],
            'b' => ['garnele', 'shrimp', 'scampi', 'krabbe', 'hummer', 'krebst'],
            'c' => ['ei ', 'eier', ' omelett', 'mayo', 'aioli', 'remoulade', 'quiche', 'tiramisu'],
            'd' => ['fisch', 'lachs', 'thun', 'tuna', 'sardine', 'anchovi', 'matjes', 'forelle', 'pangasius', 'tonnato', 'backfisch', 'salmone'],
            'e' => ['erdnuss', 'peanut'],
            'f' => ['soja', 'tofu', 'edamame', 'miso'],
            'g' => ['milch', 'käse', 'kaese', 'cheese', 'mozzarella', 'feta', 'ziege', 'joghurt', 'yogurt', 'sahne', 'butter', 'ricotta', 'parmesan', 'tzatziki', 'quark', 'labneh', 'halloumi', 'cheddar', 'gouda', 'crème', 'creme', 'gratin', 'béchamel', 'bechamel'],
            'h' => ['mandel', 'haselnuss', 'walnuss', 'cashew', 'pistazie', 'pecan', 'nuss'],
            'i' => ['linse', 'kicher', 'bohne', 'hummus', 'falafel', 'erbse', 'hülsen', 'huelsen'],
            'l' => ['sellerie', 'suppe', 'fond', 'eintopf'],
            'm' => ['senf', 'mustard', 'remoulade'],
            'n' => ['sesam', 'tahini', 'tahin', 'döner', 'doner'],
            'o' => ['sulfit', 'schwefel'],
            'p' => ['lupine'],
            'r' => ['muschel', 'calamar', 'tintenfisch', 'oktopus', 'auster', 'weichtier'],
        ];

        foreach ($rules as $code => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($hay, $needle)) {
                    $codes[] = $code;
                    break;
                }
            }
        }

        if (in_array('a1', $codes, true) || in_array('a2', $codes, true) || in_array('a3', $codes, true) || in_array('a4', $codes, true) || in_array('a5', $codes, true)) {
            $codes[] = 'a';
        }

        return $this->applyDietFlags($this->normalize($codes), $product);
    }

    /**
     * @param  list<string>  $codes
     * @return list<string>
     */
    protected function applyDietFlags(array $codes, Product $product): array
    {
        if ($product->is_gluten_free) {
            $codes = array_values(array_diff($codes, ['a', 'a1', 'a2', 'a3', 'a4', 'a5']));
        }
        if ($product->is_lactose_free) {
            $codes = array_values(array_diff($codes, ['g']));
        }
        if ($product->is_vegan) {
            $codes = array_values(array_diff($codes, ['b', 'c', 'd', 'g', 'r']));
        }

        return $this->sortCodes($codes);
    }

    /**
     * @param  list<string>  $codes
     * @return list<string>
     */
    protected function sortCodes(array $codes): array
    {
        $order = array_flip($this->codes());
        usort($codes, fn (string $a, string $b) => ($order[$a] ?? 99) <=> ($order[$b] ?? 99));

        return array_values($codes);
    }

    protected function nameKey(string $name): string
    {
        return mb_strtolower(trim($name));
    }
}
