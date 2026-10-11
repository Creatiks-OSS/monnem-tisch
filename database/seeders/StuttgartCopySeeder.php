<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class StuttgartCopySeeder extends Seeder
{
    /**
     * @var array<string, array<int, string>>
     */
    protected array $tables = [
        'pages' => ['title', 'content', 'excerpt', 'slug'],
        'page_sections' => ['title', 'subtitle', 'content', 'settings'],
        'seo_meta' => [
            'title', 'description', 'keywords', 'canonical', 'og_title', 'og_description',
            'twitter_title', 'twitter_description', 'schema_json', 'focus_keyword',
        ],
        'settings' => ['value'],
        'blog_posts' => ['title', 'excerpt', 'content', 'slug'],
        'blog_categories' => ['name', 'description', 'slug'],
        'product_categories' => ['name', 'short_description', 'description', 'seo_title', 'seo_description', 'seo_keywords'],
        'products' => ['name', 'short_description', 'description', 'ingredients', 'seo_title', 'seo_description', 'slug'],
        'reviews' => ['title', 'body'],
        'faqs' => ['question', 'answer'],
        'cities' => ['name', 'content', 'meta_title', 'meta_description', 'slug'],
        'delivery_zones' => ['name', 'city'],
        'email_templates' => ['name', 'subject', 'body_html', 'body_text'],
        'catering_packages' => ['name', 'description', 'slug'],
        'event_types' => ['name', 'description', 'seo_title', 'seo_description'],
        'extras' => ['name', 'description'],
        'galleries' => ['name', 'description', 'category'],
        'gallery_images' => ['title', 'description', 'alt'],
        'tags' => ['name', 'slug'],
    ];

    public function run(): void
    {
        $pairs = [
            ['Stuttgarts', 'Mannheims'],
            ['Stuttgart', 'Mannheim'],
            ['stuttgarts', 'mannheims'],
            ['stuttgart', 'mannheim'],
        ];

        foreach ($this->tables as $table => $columns) {
            foreach ($columns as $column) {
                foreach ($pairs as [$from, $to]) {
                    try {
                        DB::update(
                            "UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, ?, ?) WHERE `{$column}` LIKE ?",
                            [$from, $to, '%'.$from.'%']
                        );
                    } catch (Throwable) {
                        continue;
                    }
                }
            }
        }

        Cache::flush();
    }
}
