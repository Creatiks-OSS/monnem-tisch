<?php

namespace App\Services;

use App\Models\CateringPackage;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class MenuPageContent
{
    public const GROUP = 'menu_pages';

    public function text(string $menu): string
    {
        $fallback = (string) ($this->defaults()[$menu] ?? '');
        $value = trim((string) setting($menu, '', self::GROUP));

        return $value !== '' ? $value : trim($fallback);
    }

    /**
     * @return list<string>
     */
    public function paragraphs(string $menu): array
    {
        $parts = preg_split("/\n\s*\n/", $this->text($menu)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn (string $part) => $part !== ''));
    }

    public function schema(string $menu): string
    {
        return trim((string) setting($menu.'_schema', '', self::GROUP));
    }

    public function excerpt(string $menu, int $limit = 160): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', $this->text($menu)) ?? '');

        return mb_strlen($plain) > $limit ? rtrim(mb_substr($plain, 0, $limit - 1)).'…' : $plain;
    }

    public function forget(string $menu): void
    {
        Setting::query()->where('group', self::GROUP)->where('key', $menu)->delete();
        Cache::forget('settings.'.self::GROUP.'.'.$menu);
        Cache::forget('settings.group.'.self::GROUP);
    }

    /**
     * @return array<string, string>
     */
    public function defaults(): array
    {
        return [
            'buffets' => <<<'TXT'
Buffet Catering in Mannheim eignet sich, wenn viele Gäste gleichzeitig essen sollen und der Ablauf ruhig bleiben muss. Monnemtisch richtet kalte und warme Buffets für Firmenfeiern, Hochzeiten, Geburtstage und Empfänge in Mannheim und der Rhein-Neckar-Region aus. Sie wählen oben ein Paket, legen die Personenzahl fest und sehen den Festpreis, bevor Sie bestellen.

Die Speisen werden frisch in der eigenen Küche vorbereitet und pünktlich an Ihre Adresse geliefert. Wärmebehälter, Serviergeschirr und Vorlegebesteck sind im Preis enthalten. So bleibt der Aufbau vor Ort einfach, auch ohne eigenes Servicepersonal. Für Büros, private Feiern und Locations in der Innenstadt stimmen wir Lieferzeit und Adresse mit Ihnen ab.

Die Pakete lassen sich frei kombinieren. Vegetarische und vegane Komponenten können Sie aus den eigenen Menüs ergänzen. Ab zehn Personen bleibt der Preis pro Person klar und die Menge passt zur Gästezahl. Ändert sich die Personenzahl kurzfristig, erreichen Sie uns während der Geschäftszeiten telefonisch oder per E-Mail. Wenn Sie in Mannheim, Ludwigshafen oder der näheren Umgebung feiern, liefern wir das Buffet termingerecht an.
TXT,
            'fingerfood' => <<<'TXT'
Fingerfood Catering in Mannheim ist die richtige Wahl für Empfänge, Firmenfeiern und stehende Events, bei denen Gäste essen und sich gleichzeitig unterhalten. Monnemtisch liefert Häppchen zum Festpreis: herzhaft, leicht und so portioniert, dass sie ohne Besteck funktionieren. Oben wählen Sie ein Paket und die Personenzahl, der Preis bleibt vor der Bestellung sichtbar.

Wir bereiten die Häppchen frisch zu und liefern sie pünktlich an Büro, Location oder private Adresse in Mannheim und der Rhein-Neckar-Region. Die Platten sind so aufgebaut, dass Ihr Team sie direkt anrichten kann. Für Sommerfeste, Produktvorstellungen, Jubiläen und After-Work-Empfänge lässt sich Fingerfood mit einem Buffet oder mit Grillcatering kombinieren.

Vegetarische und vegane Häppchen finden Sie in den eigenen Menüs und können die Auswahl ergänzen. Ab zehn Personen lohnt sich ein festes Paket, weil Menge und Budget zusammenpassen. Kurzfristige Änderungen der Gästezahl klären wir während der Geschäftszeiten persönlich. Schauen Sie sich die Pakete oben an und stellen Sie Ihr Fingerfood für Mannheim unverbindlich zusammen oder bestellen Sie direkt online.
TXT,
            'fruehstueck' => <<<'TXT'
Frühstückscatering in Mannheim bringt ein vollständiges Bürofrühstück oder ein Brunch-Buffet dorthin, wo Ihr Team oder Ihre Gäste schon sind. Monnemtisch liefert frische Pakete zum Festpreis: herzhaft und süß, klar portioniert und pünktlich zum vereinbarten Zeitpunkt. Oben wählen Sie ein Paket und die Personenzahl, der Preis pro Person bleibt vor der Bestellung transparent.

Ob Montagsmeeting, Workshop, Konferenz oder Familienbrunch: Wir bereiten alles in der eigenen Küche vor und liefern nach Mannheim und in die Rhein-Neckar-Region. Geschirr und die nötige Ausstattung für die Ausgabe sind mitgedacht, damit der Start im Büro nicht an fehlendem Service scheitert. Vegetarische und vegane Varianten können Sie aus den eigenen Menüs ergänzen.

Ab zehn Personen ist ein Frühstückspaket besonders übersichtlich, weil Menge und Budget feststehen. Ändert sich die Teilnehmerzahl, erreichen Sie uns während der Geschäftszeiten telefonisch oder per E-Mail. Wählen Sie oben Ihr Paket und sichern Sie den Termin für Ihr Frühstück in Mannheim, unverbindlich oder direkt als Online-Bestellung.
TXT,
            'grillcatering' => <<<'TXT'
Grillcatering in Mannheim bringt den Grill dorthin, wo Ihr Event stattfindet: in den Hof, in den Garten oder auf das Firmengelände. Monnemtisch plant Grillpakete zum Festpreis für Firmenfeste, Sommerfeiern und private Events. Oben sehen Sie die Pakete, legen die Personenzahl fest und behalten das Budget im Blick, bevor Sie anfragen oder bestellen.

Wir arbeiten mit frischen Zutaten und stimmen Menge, Uhrzeit und Lieferort mit Ihnen ab. Die Pakete sind frei kombinierbar. Vegetarische und vegane Grillstücke lassen sich aus den eigenen Menüs ergänzen. So ist auch bei gemischten Gruppen für jede Ernährungsform etwas dabei, ohne dass Sie mehrere Lieferanten koordinieren müssen.

Ab zehn Personen bleibt der Preis pro Person klar. Kurzfristige Änderungen der Gästezahl klären wir während der Geschäftszeiten. Wenn Sie in Mannheim oder der Rhein-Neckar-Region feiern, liefern wir das Grillcatering termingerecht an und machen den Aufbau vor Ort nachvollziehbar. Wählen Sie oben ein Paket und fragen Sie unverbindlich an.
TXT,
            'vegetarisch' => <<<'TXT'
Vegetarisches Catering in Mannheim stellt fleischfreie Gerichte in den Mittelpunkt, ohne dass die Tafel kleiner wirkt. Monnemtisch komponiert vegetarische Pakete aus der eigenen Karte: Buffet, Fingerfood, Frühstück und Grill, jeweils zum Festpreis und frei kombinierbar. Oben wählen Sie ein Paket und die Personenzahl, der Preis bleibt vor der Bestellung sichtbar.

Die Gerichte werden frisch zubereitet und pünktlich nach Mannheim und in die Rhein-Neckar-Region geliefert. Wärmebehälter und Serviergeschirr gehören dazu, damit die Ausgabe vor Ort ruhig bleibt. Für Firmenfeiern, Hochzeiten und private Feste lässt sich das vegetarische Menü mit anderen Paketen mischen, wenn nicht alle Gäste vegetarisch essen.

Ab zehn Personen passt die Menge zur Gästezahl und das Budget ist klar. Vegane Speisen finden Sie im eigenen Menü und können die Auswahl ergänzen. Änderungen der Personenzahl klären wir während der Geschäftszeiten telefonisch oder per E-Mail. Schauen Sie sich die Pakete oben an und stellen Sie Ihr vegetarisches Catering für Mannheim zusammen.
TXT,
            'vegan' => <<<'TXT'
Veganes Catering in Mannheim bedeutet eine Tafel, die vollständig pflanzlich ist und trotzdem wie ein festliches Menü wirkt. Monnemtisch stellt vegane Pakete aus der eigenen Karte zusammen: Buffet, Fingerfood, Frühstück und Grill, jeweils zum Festpreis. Oben wählen Sie ein Paket und die Personenzahl, der Preis pro Person bleibt vor der Bestellung transparent.

Wir bereiten die Speisen frisch zu und liefern sie pünktlich an Büro, Location oder private Adresse in Mannheim und der Rhein-Neckar-Region. Serviergeschirr und Wärmebehälter sind mitgedacht, damit die Ausgabe ohne extra Abstimmung funktioniert. Für gemischte Gruppen lässt sich das vegane Menü mit den anderen Paketen kombinieren, ohne dass Sie einen zweiten Caterer brauchen.

Ab zehn Personen bleibt die Menge planbar und das Budget fest. Kurzfristige Änderungen der Gästezahl klären wir während der Geschäftszeiten telefonisch oder per E-Mail. Wenn Sie in Mannheim feiern und eine vollständig pflanzliche Tafel wollen, wählen Sie oben ein Paket und fragen Sie unverbindlich an oder bestellen Sie direkt online.
TXT,
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function menus(): array
    {
        $menus = [];
        foreach (CateringPackage::MENUS as $key => $item) {
            $menus[] = [
                'key' => $key,
                'label' => $item['label'],
            ];
        }

        return $menus;
    }
}
