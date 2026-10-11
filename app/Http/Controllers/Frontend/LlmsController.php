<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CateringPackage;
use App\Services\PublicUrlCatalog;
use Illuminate\Http\Response;

class LlmsController extends Controller
{
    public function __construct(
        protected PublicUrlCatalog $catalog
    ) {}

    public function __invoke(): Response
    {
        $entries = collect($this->catalog->entries());

        $lines = [
            '# Monnemtisch Catering',
            '',
            '> Premium-Catering aus Mannheim (Marke der Anatolian Gourmet GmbH). Buffets, Fingerfood, Frühstück, Grillcatering sowie vegetarische und vegane Menüs für Unternehmen, Hochzeiten und private Events in Mannheim und der Metropolregion Rhein-Neckar.',
            '',
            'Monnemtisch Catering liefert in Mannheim kostenlos. Außerhalb der Stadt gelten 1,00 € je Kilometer ab H1, 10, 68159 Mannheim. Anfragen: info@monnemtisch.de – Website: '.url('/'),
            '',
            'Diese Datei folgt der llms.txt-Spezifikation und beschreibt die öffentlichen Inhalte der Website für Sprachmodelle.',
            '',
            '## Wichtige Seiten',
        ];

        $core = [
            ['Startseite', url('/'), 'Catering Mannheim – Menüs, Lieferung, Anfrage'],
        ];
        foreach (CateringPackage::MENUS as $key => $menu) {
            $core[] = [$menu['label'], route('packages.index', $key), $menu['headline']];
        }
        $core = array_merge($core, [
            ['Angebot', url('/angebot'), 'Unverbindliches Catering-Angebot anfordern'],
            ['Kontakt', url('/kontakt'), 'Kontakt zu Monnemtisch Catering'],
            ['Über uns', url('/ueber-uns'), 'Anatolian Gourmet GmbH, Marke Monnemtisch'],
            ['Magazin', url('/blog'), 'Catering-Tipps und Guides für Mannheim'],
            ['Galerie', url('/galerie'), 'Fotos von Buffets und Events'],
            ['Partyservice', url('/partyservice'), 'Partyservice Mannheim'],
            ['Messe-Catering', url('/messe-catering'), 'Catering für Messestände'],
            ['Bewertungen', url('/bewertungen'), 'Gästestimmen'],
            ['Impressum', url('/impressum'), 'Anbieterkennzeichnung'],
            ['Datenschutz', url('/datenschutz'), 'Datenschutzerklärung'],
            ['Allergene', url('/allergene'), 'Allergenkennzeichnung der Speisen'],
            ['Sitemap', url('/sitemap.xml'), 'XML-Sitemap aller indexierbaren URLs'],
        ]);

        foreach ($core as [$title, $url, $hint]) {
            $lines[] = '- ['.$title.']('.$url.'): '.$hint;
        }

        $lines[] = '';
        $lines[] = '## Magazin';
        $blog = $entries->where('section', 'blog')->sortBy('title');
        foreach ($blog as $entry) {
            $lines[] = '- ['.$entry['title'].']('.$entry['loc'].')';
        }

        $lines[] = '';
        $lines[] = '## Menüs';
        foreach ($entries->where('section', 'menus') as $entry) {
            $lines[] = '- ['.$entry['title'].']('.$entry['loc'].')';
        }
        foreach ($entries->where('section', 'packages')->sortBy('title') as $entry) {
            $lines[] = '- ['.$entry['title'].']('.$entry['loc'].')';
        }

        $lines[] = '';
        $lines[] = '## Standorte';
        foreach ($entries->where('section', 'locations')->sortBy('title') as $entry) {
            $lines[] = '- ['.$entry['title'].']('.$entry['loc'].')';
        }

        $lines[] = '';
        $lines[] = '## Optional';
        $lines[] = '- [Vollständige URL-Liste]('.url('/llms-full.txt').'): Alle öffentlichen Seiten als Klartext';
        $lines[] = '';

        $body = implode("\n", $lines);

        return response($body, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function full(): Response
    {
        $lines = [
            '# Monnemtisch Catering – vollständige URL-Liste',
            '',
            '> Alle öffentlichen, indexierbaren Seiten. Kompakte Übersicht: '.url('/llms.txt'),
            '',
        ];

        foreach ($this->catalog->entries() as $entry) {
            $lines[] = '- ['.$entry['title'].']('.$entry['loc'].')';
        }

        $lines[] = '';

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
