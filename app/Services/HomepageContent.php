<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class HomepageContent
{
    public const GROUP = 'homepage';

    public function text(string $key): string
    {
        $fallback = (string) ($this->defaults()[$key] ?? '');
        $value = trim((string) setting($key, '', self::GROUP));

        return $value !== '' ? $value : $fallback;
    }

    /**
     * @return list<string>
     */
    public function paragraphs(string $key): array
    {
        $parts = preg_split("/\r\n|\n|\r/", $this->text($key)) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), fn (string $part) => $part !== ''));

        return $parts !== [] ? $parts : [''];
    }

    public function image(string $key, string $fallbackUrl): string
    {
        $path = trim((string) setting($key, '', self::GROUP));
        if ($path === '') {
            return $fallbackUrl;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return public_media_url($path) ?: $fallbackUrl;
    }

    public function hasCustomImage(string $key): bool
    {
        return trim((string) setting($key, '', self::GROUP)) !== '';
    }

    /**
     * @return array<string, string>
     */
    public function defaults(): array
    {
        return [
            'hero_line_1' => 'Catering in Mannheim für',
            'hero_line_2' => 'Firmen, Hochzeiten & Events',
            'hero_lead' => 'Erstelle dir mit wenigen Klicks dein Buffet zum Festpreis & bestelle online.',
            'hero_btn_primary' => 'Buffets entdecken',
            'hero_btn_secondary' => 'Kontakt aufnehmen',
            'hero_alt' => 'Festlich angerichtetes Catering-Buffet von Monnemtisch in Mannheim',
            'trust_1' => 'Hohe Qualität',
            'trust_2' => 'Regional & frisch',
            'trust_3' => 'Pünktliche Lieferung',
            'trust_4' => 'Individuelle Menüs',
            'cats_title' => 'Unsere Kategorien',
            'cats_lead' => 'Wählen Sie aus unseren vielfältigen Catering-Paketen',
            'cat_btn' => 'Jetzt entdecken',
            'cat_1_title' => 'Kalt-warme Buffets',
            'cat_1_alt' => 'Kalt-warmes Buffet Catering von Monnemtisch in Mannheim',
            'cat_2_title' => 'Fingerfood',
            'cat_2_alt' => 'Fingerfood Catering von Monnemtisch in Mannheim',
            'cat_3_title' => 'Frühstück',
            'cat_3_alt' => 'Frühstück Catering von Monnemtisch in Mannheim',
            'cat_4_title' => 'Grillcatering',
            'cat_4_alt' => 'Grillcatering von Monnemtisch in Mannheim',
            'cat_5_title' => 'Vegetarisch',
            'cat_5_alt' => 'Vegetarisches Catering von Monnemtisch in Mannheim',
            'cat_6_title' => 'Vegan',
            'cat_6_alt' => 'Veganes Catering von Monnemtisch in Mannheim',
            'split_1_kicker' => 'Ihr Catering Service in Mannheim',
            'split_1_title' => 'Bestes Catering für Privat- und Geschäftskunden',
            'split_1_body' => "Monnemtisch ist Ihr Caterer für private und geschäftliche Anlässe in Mannheim und der Rhein-Neckar-Region. Wir verbinden erstklassige Qualität mit einem reibungslosen Ablauf.\nOb Firmenfeier, Hochzeit oder Team-Event – wir liefern leckeres Catering und machen Ihre Veranstaltung zu einem kulinarischen Highlight.\nWir kochen aus Leidenschaft und legen Wert auf frische Zutaten und eine ansprechende Präsentation.",
            'split_1_btn' => 'Mehr über uns',
            'split_1_alt' => 'Gedeckter Catering-Tisch von Monnemtisch für Events in Mannheim',
            'split_2_kicker' => 'Bleiben Sie flexibel',
            'split_2_title' => 'Catering einfach online bestellen',
            'split_2_body' => "Bestellen Sie Ihr Catering direkt über unseren Onlineshop. Nach Ihrer Bestellung erhalten Sie eine Bestätigung, und wir stimmen den Liefertermin mit Ihnen ab.\nStellen Sie Ihr Menü anhand der Kategorien zusammen, wählen Sie aus den Paketen und behalten Sie den Festpreis im Blick.\nÄndert sich kurzfristig die Personenzahl, erreichen Sie uns während der Geschäftszeiten persönlich, per E-Mail oder Telefon.",
            'split_2_alt' => 'Frisch zubereitete Speisen aus der Küche von Monnemtisch',
            'split_3_kicker' => 'Unser Team',
            'split_3_title' => 'Catering aus Tradition und Leidenschaft',
            'split_3_body' => "Hinter Monnemtisch steht ein Küchenteam, das Veranstaltungen ernst nimmt: von der Vorbereitung bis zur pünktlichen Lieferung.\nWir möchten für zuverlässiges Catering und persönlichen Service stehen. Jedes Detail wird mit Sorgfalt vorbereitet.",
            'split_3_alt' => 'Service und Präsentation beim Catering von Monnemtisch',
            'reasons_title' => '5 Gründe, bei uns Ihr Catering zu bestellen',
            'split_4_alt' => 'Buffet von Monnemtisch Catering in Mannheim',
            'reason_1_icon' => '📅',
            'reason_1_title' => 'Flexibilität',
            'reason_1_text' => 'Dank unserer flexiblen Abläufe können wir auch kurzfristig die Anzahl der Gäste oder Speisen anpassen.',
            'reason_2_icon' => '😲',
            'reason_2_title' => 'Gäste beeindrucken',
            'reason_2_text' => 'Wir haben den Anspruch, mit jedem Setup Gäste nachhaltig zu beeindrucken und eine echte Wohlfühlatmosphäre zu schaffen.',
            'reason_3_icon' => '👨‍🍳',
            'reason_3_title' => 'Unser Küchenteam',
            'reason_3_text' => 'Unsere Köche wissen genau, worauf es bei Veranstaltungen ankommt, und treffen mit durchdachten Rezepturen jeden Geschmack.',
            'reason_4_icon' => '🏅',
            'reason_4_title' => 'Faire Preise',
            'reason_4_text' => 'Transparente Festpreise ohne versteckte Kosten. Sie sehen Ihr Budget, bevor Sie bestellen.',
            'reason_5_icon' => '🌱',
            'reason_5_title' => 'Gesund & frisch',
            'reason_5_text' => 'Wir kaufen Zutaten frisch und wenn möglich regional ein. Den Unterschied schmeckt man bei jedem Bissen.',
            'service_title' => 'Der komplette Sorglos-Service',
            'service_1_title' => 'Voller Service inklusive',
            'service_1_text' => 'Wärmebehälter, Serviergeschirr und das Vorlegebesteck sind bei jeder Lieferung bereits im Preis inbegriffen.',
            'service_2_title' => 'Maximale Flexibilität',
            'service_2_text' => 'Bestellungen können bis zu 48 Stunden vor dem Event angepasst oder storniert werden.',
            'service_3_title' => 'Immer für Sie da',
            'service_3_text' => 'Unser Team ist während der Geschäftszeiten telefonisch oder per E-Mail erreichbar und hilft Ihnen direkt weiter.',
            'gallery_title' => 'Einblicke & Galerie',
            'gallery_lead' => 'Impressionen aus unseren Caterings',
            'gallery_btn' => 'Alle Fotos ansehen',
            'concepts_title' => 'Maßgeschneiderte Catering-Konzepte in Mannheim',
            'concepts_lead' => 'Spezialisierte Angebote für jeden Anlass in Mannheim und der Rhein-Neckar-Region – ab 10 Personen zum transparenten Festpreis.',
            'seo_title' => 'Catering in Mannheim online bestellen – frisch & individuell',
            'seo_p1' => 'Als Caterer für Mannheim und die Region beliefern wir Unternehmen, Messestände und private Gastgeber in Mannheim, Ludwigshafen, Heidelberg und im weiteren Umland.',
            'seo_p2' => 'Stellen Sie Ihr Menü aus kalt-warmen Buffets, Fingerfood, Business-Frühstück oder Grillcatering direkt online zusammen. Wärmebehälter, Serviergeschirr und Vorlegebesteck sind bei jeder Lieferung inklusive.',
            'reviews_kicker' => 'Kundenstimmen',
            'reviews_title' => 'Das sagen unsere Kunden',
            'reviews_btn' => 'Alle Bewertungen ansehen',
            'cta_title' => 'Ihr nächstes Event verdient das Beste',
            'cta_lead' => 'Lassen Sie uns Ihr Catering planen. Kostenlose Beratung und individuelle Angebote für Ihr Event.',
            'cta_btn' => 'Jetzt Angebot anfordern',
        ];
    }

    /**
     * @return list<array{title: string, hint?: string, fields: list<array{key: string, label: string, type: string}>, images?: list<array{key: string, label: string, fallback: string}>}>
     */
    public function sections(): array
    {
        return [
            [
                'title' => 'Üst alan (Hero)',
                'hint' => 'Büyük başlık, kısa metin ve arka plan fotoğrafı.',
                'fields' => [
                    ['key' => 'hero_line_1', 'label' => 'Başlık, beyaz satır', 'type' => 'text'],
                    ['key' => 'hero_line_2', 'label' => 'Başlık, altın satır', 'type' => 'text'],
                    ['key' => 'hero_lead', 'label' => 'Alt metin', 'type' => 'textarea'],
                    ['key' => 'hero_btn_primary', 'label' => 'Altın buton', 'type' => 'text'],
                    ['key' => 'hero_btn_secondary', 'label' => 'Çerçeveli buton', 'type' => 'text'],
                    ['key' => 'hero_alt', 'label' => 'Görsel açıklaması (alt metin)', 'type' => 'text'],
                ],
                'images' => [
                    ['key' => 'hero_image', 'label' => 'Arka plan görseli', 'fallback' => 'hero'],
                ],
            ],
            [
                'title' => 'Güven çubuğu',
                'fields' => [
                    ['key' => 'trust_1', 'label' => '1. madde', 'type' => 'text'],
                    ['key' => 'trust_2', 'label' => '2. madde', 'type' => 'text'],
                    ['key' => 'trust_3', 'label' => '3. madde', 'type' => 'text'],
                    ['key' => 'trust_4', 'label' => '4. madde', 'type' => 'text'],
                ],
            ],
            [
                'title' => 'Kategoriler',
                'hint' => 'Kartların gideceği sayfalar sabit: Buffets, Fingerfood, Frühstück, Grillcatering, Vegetarisch, Vegan.',
                'fields' => [
                    ['key' => 'cats_title', 'label' => 'Bölüm başlığı', 'type' => 'text'],
                    ['key' => 'cats_lead', 'label' => 'Bölüm açıklaması', 'type' => 'textarea'],
                    ['key' => 'cat_btn', 'label' => 'Kart butonu', 'type' => 'text'],
                    ['key' => 'cat_1_title', 'label' => '1. kart başlığı', 'type' => 'text'],
                    ['key' => 'cat_1_alt', 'label' => '1. kart görsel açıklaması', 'type' => 'text'],
                    ['key' => 'cat_2_title', 'label' => '2. kart başlığı', 'type' => 'text'],
                    ['key' => 'cat_2_alt', 'label' => '2. kart görsel açıklaması', 'type' => 'text'],
                    ['key' => 'cat_3_title', 'label' => '3. kart başlığı', 'type' => 'text'],
                    ['key' => 'cat_3_alt', 'label' => '3. kart görsel açıklaması', 'type' => 'text'],
                    ['key' => 'cat_4_title', 'label' => '4. kart başlığı', 'type' => 'text'],
                    ['key' => 'cat_4_alt', 'label' => '4. kart görsel açıklaması', 'type' => 'text'],
                    ['key' => 'cat_5_title', 'label' => '5. kart başlığı', 'type' => 'text'],
                    ['key' => 'cat_5_alt', 'label' => '5. kart görsel açıklaması', 'type' => 'text'],
                    ['key' => 'cat_6_title', 'label' => '6. kart başlığı', 'type' => 'text'],
                    ['key' => 'cat_6_alt', 'label' => '6. kart görsel açıklaması', 'type' => 'text'],
                ],
                'images' => [
                    ['key' => 'cat_1_image', 'label' => '1. kart görseli', 'fallback' => 'buffet'],
                    ['key' => 'cat_2_image', 'label' => '2. kart görseli', 'fallback' => 'fingerfood'],
                    ['key' => 'cat_3_image', 'label' => '3. kart görseli', 'fallback' => 'breakfast'],
                    ['key' => 'cat_4_image', 'label' => '4. kart görseli', 'fallback' => 'grill'],
                    ['key' => 'cat_5_image', 'label' => '5. kart görseli', 'fallback' => 'vegetarisch'],
                    ['key' => 'cat_6_image', 'label' => '6. kart görseli', 'fallback' => 'vegan'],
                ],
            ],
            [
                'title' => 'Bölüm 1 — Hakkımızda',
                'hint' => 'Metinde her paragrafı yeni bir satıra yazın.',
                'fields' => [
                    ['key' => 'split_1_kicker', 'label' => 'Üst etiket', 'type' => 'text'],
                    ['key' => 'split_1_title', 'label' => 'Başlık', 'type' => 'text'],
                    ['key' => 'split_1_body', 'label' => 'Metin', 'type' => 'textarea'],
                    ['key' => 'split_1_btn', 'label' => 'Buton', 'type' => 'text'],
                    ['key' => 'split_1_alt', 'label' => 'Görsel açıklaması', 'type' => 'text'],
                ],
                'images' => [
                    ['key' => 'split_1_image', 'label' => 'Görsel', 'fallback' => 'table'],
                ],
            ],
            [
                'title' => 'Bölüm 2 — Online sipariş',
                'hint' => 'Metinde her paragrafı yeni bir satıra yazın.',
                'fields' => [
                    ['key' => 'split_2_kicker', 'label' => 'Üst etiket', 'type' => 'text'],
                    ['key' => 'split_2_title', 'label' => 'Başlık', 'type' => 'text'],
                    ['key' => 'split_2_body', 'label' => 'Metin', 'type' => 'textarea'],
                    ['key' => 'split_2_alt', 'label' => 'Görsel açıklaması', 'type' => 'text'],
                ],
                'images' => [
                    ['key' => 'split_2_image', 'label' => 'Görsel', 'fallback' => 'kitchen'],
                ],
            ],
            [
                'title' => 'Bölüm 3 — Ekip',
                'hint' => 'Metinde her paragrafı yeni bir satıra yazın.',
                'fields' => [
                    ['key' => 'split_3_kicker', 'label' => 'Üst etiket', 'type' => 'text'],
                    ['key' => 'split_3_title', 'label' => 'Başlık', 'type' => 'text'],
                    ['key' => 'split_3_body', 'label' => 'Metin', 'type' => 'textarea'],
                    ['key' => 'split_3_alt', 'label' => 'Görsel açıklaması', 'type' => 'text'],
                ],
                'images' => [
                    ['key' => 'split_3_image', 'label' => 'Görsel', 'fallback' => 'staff'],
                ],
            ],
            [
                'title' => '5 neden',
                'fields' => array_merge(
                    [
                        ['key' => 'reasons_title', 'label' => 'Başlık', 'type' => 'text'],
                        ['key' => 'split_4_alt', 'label' => 'Görsel açıklaması', 'type' => 'text'],
                    ],
                    $this->reasonFields()
                ),
                'images' => [
                    ['key' => 'split_4_image', 'label' => 'Görsel', 'fallback' => 'buffet'],
                ],
            ],
            [
                'title' => 'Sorglos-Service',
                'fields' => [
                    ['key' => 'service_title', 'label' => 'Başlık', 'type' => 'text'],
                    ['key' => 'service_1_title', 'label' => '1. kart başlığı', 'type' => 'text'],
                    ['key' => 'service_1_text', 'label' => '1. kart metni', 'type' => 'textarea'],
                    ['key' => 'service_2_title', 'label' => '2. kart başlığı', 'type' => 'text'],
                    ['key' => 'service_2_text', 'label' => '2. kart metni', 'type' => 'textarea'],
                    ['key' => 'service_3_title', 'label' => '3. kart başlığı', 'type' => 'text'],
                    ['key' => 'service_3_text', 'label' => '3. kart metni', 'type' => 'textarea'],
                ],
            ],
            [
                'title' => 'Galeri başlıkları',
                'hint' => 'Fotoğraflar Galeri menüsünden gelir. Buradan yalnızca başlık ve buton değişir.',
                'fields' => [
                    ['key' => 'gallery_title', 'label' => 'Başlık', 'type' => 'text'],
                    ['key' => 'gallery_lead', 'label' => 'Alt metin', 'type' => 'text'],
                    ['key' => 'gallery_btn', 'label' => 'Buton', 'type' => 'text'],
                ],
            ],
            [
                'title' => 'Konsept ve SEO metni',
                'fields' => [
                    ['key' => 'concepts_title', 'label' => 'Başlık', 'type' => 'text'],
                    ['key' => 'concepts_lead', 'label' => 'Açıklama', 'type' => 'textarea'],
                    ['key' => 'seo_title', 'label' => 'SEO başlığı', 'type' => 'text'],
                    ['key' => 'seo_p1', 'label' => 'SEO paragraf 1', 'type' => 'textarea'],
                    ['key' => 'seo_p2', 'label' => 'SEO paragraf 2', 'type' => 'textarea'],
                ],
            ],
            [
                'title' => 'Yorumlar ve kapanış',
                'hint' => 'Yorum kartlarının içeriği Yorumlar menüsünden gelir.',
                'fields' => [
                    ['key' => 'reviews_kicker', 'label' => 'Küçük etiket', 'type' => 'text'],
                    ['key' => 'reviews_title', 'label' => 'Başlık', 'type' => 'text'],
                    ['key' => 'reviews_btn', 'label' => 'Yorum butonu', 'type' => 'text'],
                    ['key' => 'cta_title', 'label' => 'Kapanış başlığı', 'type' => 'text'],
                    ['key' => 'cta_lead', 'label' => 'Kapanış metni', 'type' => 'textarea'],
                    ['key' => 'cta_btn', 'label' => 'Kapanış butonu', 'type' => 'text'],
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function imageKeys(): array
    {
        return [
            'hero_image',
            'cat_1_image',
            'cat_2_image',
            'cat_3_image',
            'cat_4_image',
            'cat_5_image',
            'cat_6_image',
            'split_1_image',
            'split_2_image',
            'split_3_image',
            'split_4_image',
        ];
    }

    public function forget(string $key): void
    {
        Setting::query()->where('group', self::GROUP)->where('key', $key)->delete();
        Cache::forget('settings.'.self::GROUP.'.'.$key);
        Cache::forget('settings.group.'.self::GROUP);
    }

    /**
     * @return list<array{key: string, label: string, type: string}>
     */
    protected function reasonFields(): array
    {
        $fields = [];
        for ($i = 1; $i <= 5; $i++) {
            $fields[] = ['key' => "reason_{$i}_icon", 'label' => "{$i}. ikon (emoji)", 'type' => 'text'];
            $fields[] = ['key' => "reason_{$i}_title", 'label' => "{$i}. başlık", 'type' => 'text'];
            $fields[] = ['key' => "reason_{$i}_text", 'label' => "{$i}. metin", 'type' => 'textarea'];
        }

        return $fields;
    }
}
