<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\AllergenCatalog;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->with(['sections' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->firstOrFail();

        return view('frontend.pages.show', [
            'page' => $page,
            'sections' => $page->sections,
        ]);
    }

    public function legal(string $slug): View
    {
        $allowed = ['datenschutz', 'impressum', 'agb', 'widerruf', 'allergene'];

        if (! in_array($slug, $allowed, true)) {
            abort(404);
        }

        if ($slug === 'allergene') {
            return view('frontend.pages.allergens', [
                'allergens' => app(AllergenCatalog::class)->all(),
            ]);
        }

        $page = Page::query()
            ->published()
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('type', 'legal_'.$slug)
                    ->orWhere('type', $slug);
            })
            ->with(['sections' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->first();

        if (! $page) {
            $page = new Page([
                'title' => match ($slug) {
                    'datenschutz' => 'Datenschutz',
                    'impressum' => 'Impressum',
                    'agb' => 'AGB',
                    'widerruf' => 'Widerrufsbelehrung',
                    default => ucfirst($slug),
                },
                'slug' => $slug,
                'content' => $slug === 'agb'
                    ? (string) @file_get_contents(database_path('seeders/legal/agb.html'))
                    : '',
                'type' => 'legal',
            ]);
        }

        return view('frontend.pages.show', [
            'page' => $page,
            'sections' => $page->relationLoaded('sections') ? $page->sections : collect(),
        ]);
    }
}
