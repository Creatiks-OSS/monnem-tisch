<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\CateringPackage;
use App\Models\City;
use App\Models\Gallery;
use App\Models\Page;
use Illuminate\Support\Carbon;

class PublicUrlCatalog
{
    /**
     * @return list<array{
     *     loc: string,
     *     lastmod: Carbon,
     *     changefreq: string,
     *     priority: string,
     *     title: string,
     *     section: string
     * }>
     */
    public function entries(): array
    {
        $entries = [];

        $add = function (string $loc, mixed $lastmod, string $changefreq, string $priority, string $title, string $section) use (&$entries): void {
            $entries[$loc] = [
                'loc' => $loc,
                'lastmod' => Carbon::parse($lastmod ?? now()),
                'changefreq' => $changefreq,
                'priority' => $priority,
                'title' => $title,
                'section' => $section,
            ];
        };

        $add(url('/'), now(), 'daily', '1.0', 'Startseite – Monnemtisch Catering Mannheim', 'core');

        $menuDates = CateringPackage::query()
            ->selectRaw('menu_group, MAX(updated_at) as last_updated')
            ->whereIn('menu_group', CateringPackage::menuKeys())
            ->groupBy('menu_group')
            ->pluck('last_updated', 'menu_group');

        foreach (CateringPackage::MENUS as $key => $menu) {
            $add(
                route('packages.index', $key),
                $menuDates[$key] ?? now(),
                'weekly',
                '0.9',
                $menu['seo_title'] ?? $menu['label'],
                'menus'
            );
        }
        $add(route('quote.create'), now(), 'monthly', '0.8', 'Angebot anfordern', 'core');
        $add(route('contact.show'), now(), 'monthly', '0.7', 'Kontakt', 'core');
        $add(route('about'), now(), 'monthly', '0.7', 'Über uns', 'core');
        $add(route('reviews.index'), now(), 'weekly', '0.6', 'Bewertungen', 'core');
        $add(route('blog.index'), now(), 'daily', '0.9', 'Magazin', 'blog');
        $add(route('gallery.index'), now(), 'weekly', '0.7', 'Galerie', 'gallery');
        $add(route('events.messe'), now(), 'weekly', '0.8', 'Messe-Catering', 'landings');
        $add(route('events.partyservice'), now(), 'weekly', '0.8', 'Partyservice', 'landings');

        $add(url('/impressum'), now(), 'yearly', '0.3', 'Impressum', 'legal');
        $add(url('/datenschutz'), now(), 'yearly', '0.3', 'Datenschutz', 'legal');
        $add(url('/agb'), now(), 'yearly', '0.3', 'AGB', 'legal');
        $add(url('/widerruf'), now(), 'yearly', '0.3', 'Widerrufsbelehrung', 'legal');
        $add(url('/allergene'), now(), 'yearly', '0.4', 'Allergene', 'legal');

        BlogCategory::query()->active()->orderBy('sort_order')->get()->each(function (BlogCategory $category) use ($add): void {
            $add(
                route('blog.category', $category->slug),
                $category->updated_at,
                'weekly',
                '0.65',
                $category->name,
                'blog'
            );
        });

        CateringPackage::query()->active()->orderBy('id')->chunk(100, function ($packages) use ($add): void {
            foreach ($packages as $package) {
                $add(
                    $package->publicUrl(),
                    $package->updated_at,
                    'weekly',
                    '0.8',
                    $package->name,
                    'packages'
                );
            }
        });

        BlogPost::query()->published()->orderBy('id')->chunk(100, function ($posts) use ($add): void {
            foreach ($posts as $post) {
                $add(
                    route('blog.show', $post->slug),
                    $post->updated_at ?? $post->published_at,
                    'weekly',
                    '0.7',
                    $post->title,
                    'blog'
                );
            }
        });

        Gallery::query()->active()->orderBy('id')->chunk(50, function ($galleries) use ($add): void {
            foreach ($galleries as $gallery) {
                $add(
                    route('gallery.show', $gallery->slug),
                    $gallery->updated_at,
                    'monthly',
                    '0.55',
                    $gallery->name,
                    'gallery'
                );
            }
        });

        City::query()->active()->orderBy('id')->chunk(50, function ($cities) use ($add): void {
            foreach ($cities as $city) {
                $add(
                    route('cities.show', $city->slug),
                    $city->updated_at,
                    'monthly',
                    '0.7',
                    'Catering '.$city->name,
                    'locations'
                );
            }
        });

        $skipPageSlugs = [
            'home', 'startseite',
            'datenschutz', 'impressum', 'agb', 'widerruf',
            'messe-catering', 'event-catering', 'partyservice', 'ueber-uns',
            'hochzeiten', 'firmen-catering',
        ];

        Page::query()->published()->orderBy('id')->chunk(50, function ($pages) use ($add, $skipPageSlugs): void {
            foreach ($pages as $page) {
                if (in_array($page->slug, $skipPageSlugs, true) || $page->type === 'homepage') {
                    continue;
                }
                $add(
                    route('pages.show', $page->slug),
                    $page->updated_at,
                    'monthly',
                    '0.5',
                    $page->title,
                    'pages'
                );
            }
        });

        return array_values($entries);
    }
}
