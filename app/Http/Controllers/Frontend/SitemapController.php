<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Services\PublicUrlCatalog;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(
        protected PublicUrlCatalog $catalog
    ) {}

    public function __invoke(): Response
    {
        $entries = $this->catalog->entries();
        $galleryImages = $this->galleryImagesByAlbum();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";

        foreach ($entries as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($entry['loc'])."</loc>\n";
            $xml .= '    <lastmod>'.$entry['lastmod']->toW3cString()."</lastmod>\n";
            $xml .= '    <changefreq>'.$entry['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$entry['priority']."</priority>\n";

            $slug = basename(parse_url($entry['loc'], PHP_URL_PATH) ?: '');
            foreach ($galleryImages[$slug] ?? [] as $image) {
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>'.e($image['loc'])."</image:loc>\n";
                if ($image['title'] !== '') {
                    $xml .= '      <image:title>'.e($image['title'])."</image:title>\n";
                }
                $xml .= "    </image:image>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * @return array<string, list<array{loc: string, title: string}>>
     */
    protected function galleryImagesByAlbum(): array
    {
        $map = [];

        Gallery::query()
            ->active()
            ->with(['images' => fn ($q) => $q->orderBy('sort_order')])
            ->get()
            ->each(function (Gallery $gallery) use (&$map): void {
                $map[$gallery->slug] = $gallery->images
                    ->map(function ($image) {
                        $path = $image->url ?: $image->path;
                        if (! $path) {
                            return null;
                        }
                        $loc = str_starts_with($path, 'http') ? $path : url($path);

                        return [
                            'loc' => $loc,
                            'title' => (string) ($image->title ?: $image->alt ?: ''),
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();
            });

        return $map;
    }
}
