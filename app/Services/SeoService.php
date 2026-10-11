<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Product;
use App\Models\SeoMeta;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SeoService
{
    public function __construct(
        protected SchemaService $schema
    ) {}

    /**
     * @return array{
     *     title: string,
     *     description: string,
     *     keywords: ?string,
     *     canonical: ?string,
     *     og_title: string,
     *     og_description: string,
     *     og_image: ?string,
     *     twitter_title: string,
     *     twitter_description: string,
     *     twitter_image: ?string,
     *     robots: ?string,
     *     schema: array
     * }
     */
    public function forModel(mixed $model): array
    {
        $siteName = Setting::get('company_name', 'Monnemtisch Catering', 'general');
        $defaultDesc = Setting::get('meta_description', 'Premium Catering für Ihre Events.', 'seo');
        $defaultImage = Setting::get('og_image', null, 'seo');

        $meta = null;
        if ($model instanceof Model) {
            if (method_exists($model, 'seo')) {
                $meta = $model->seo;
            } elseif (method_exists($model, 'seoMeta')) {
                $meta = $model->seoMeta;
            } else {
                $meta = SeoMeta::query()
                    ->where('seoable_type', $model->getMorphClass())
                    ->where('seoable_id', $model->getKey())
                    ->first();
            }
        }

        $title = $meta?->title
            ?? ($model->seo_title ?? null)
            ?? ($model->title ?? null)
            ?? ($model->name ?? null)
            ?? $siteName;

        $description = $meta?->description
            ?? ($model->seo_description ?? null)
            ?? ($model->short_description ?? null)
            ?? ($model->excerpt ?? null)
            ?? $defaultDesc;

        $description = Str::limit(strip_tags((string) $description), 160);
        $ogImage = $meta?->og_image
            ?? ($model->featured_image ?? null)
            ?? ($model->image ?? null)
            ?? $defaultImage;

        $schemas = $this->generateJsonLd($model);

        return [
            'title' => trim($title.' | '.$siteName, ' |'),
            'description' => $description,
            'keywords' => $meta?->keywords ?? ($model->seo_keywords ?? null),
            'canonical' => $meta?->canonical,
            'og_title' => $meta?->og_title ?? $title,
            'og_description' => $meta?->og_description ?? $description,
            'og_image' => $ogImage,
            'twitter_title' => $meta?->twitter_title ?? $title,
            'twitter_description' => $meta?->twitter_description ?? $description,
            'twitter_image' => $meta?->twitter_image ?? $ogImage,
            'robots' => $meta?->robots ?? 'index,follow',
            'schema' => $schemas,
        ];
    }

    /**
     * Build JSON-LD graph based on model context.
     *
     * @return array<int, array<string, mixed>>
     */
    public function generateJsonLd(mixed $context = null): array
    {
        $graph = [
            $this->schema->organization(),
            $this->schema->website(),
            $this->schema->localBusiness(),
        ];

        if ($context instanceof Product) {
            $graph[] = $this->schema->product($context);
            $recipe = $this->schema->recipe($context);
            if ($recipe) {
                $graph[] = $recipe;
            }
        }

        if ($context instanceof BlogPost) {
            $graph[] = $this->schema->article($context);
        }

        if ($context instanceof Faq || (is_iterable($context) && ! $context instanceof Model)) {
            // handled via faqPage helper
        }

        $rating = $this->schema->aggregateRating();
        if ($rating) {
            $graph[] = $rating;
        }

        return $graph;
    }

    /**
     * @return array{meta: array, json_ld: array}
     */
    public function render(mixed $model = null): array
    {
        if ($model) {
            $meta = $this->forModel($model);

            return [
                'meta' => $meta,
                'json_ld' => $meta['schema'],
            ];
        }

        return [
            'meta' => [
                'title' => Setting::get('company_name', 'Monnemtisch Catering', 'general'),
                'description' => Setting::get('meta_description', 'Premium Catering für Ihre Events.', 'seo'),
                'keywords' => Setting::get('meta_keywords', null, 'seo'),
                'canonical' => null,
                'og_title' => Setting::get('company_name', 'Monnemtisch Catering', 'general'),
                'og_description' => Setting::get('meta_description', 'Premium Catering für Ihre Events.', 'seo'),
                'og_image' => Setting::get('og_image', null, 'seo'),
                'twitter_title' => Setting::get('company_name', 'Monnemtisch Catering', 'general'),
                'twitter_description' => Setting::get('meta_description', 'Premium Catering für Ihre Events.', 'seo'),
                'twitter_image' => Setting::get('og_image', null, 'seo'),
                'robots' => 'index,follow',
                'schema' => $this->generateJsonLd(),
            ],
            'json_ld' => $this->generateJsonLd(),
        ];
    }

    public function renderFaqPage(iterable $faqs): array
    {
        return [
            'meta' => $this->render()['meta'],
            'json_ld' => [$this->schema->faqPage($faqs)],
        ];
    }

    public function renderBreadcrumb(array $items): array
    {
        return $this->schema->breadcrumb($items);
    }

    public function jsonLdScript(array $schemas): string
    {
        $payload = count($schemas) === 1
            ? $schemas[0]
            : ['@context' => 'https://schema.org', '@graph' => $schemas];

        return '<script type="application/ld+json">'.json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        ).'</script>';
    }
}
