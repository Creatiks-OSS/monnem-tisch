<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BusinessHour;
use App\Models\CateringPackage;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Support\Str;

class SchemaService
{
    /** @var list<array<string, mixed>> */
    protected array $pageNodes = [];

    protected ?string $pageHeadline = null;

    protected ?string $pageDescription = null;

    protected ?string $pageImage = null;

    protected ?string $pageUrl = null;

    protected ?string $pagePublished = null;

    protected ?string $pageModified = null;

    public function organization(): array
    {
        $name = Setting::get('company_name', 'Monnemtisch Catering', 'general');
        $legal = Setting::get('legal_name', 'Anatolian Gourmet GmbH', 'general');
        $url = rtrim((string) Setting::get('website_url', config('app.url'), 'general'), '/') ?: url('/');
        $logo = site_logo_url();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => url('/#organization'),
            'name' => $name,
            'legalName' => $legal ?: 'Anatolian Gourmet GmbH',
            'alternateName' => 'Monnemtisch',
            'url' => $url,
            'description' => 'Catering in Mannheim: Buffets, Fingerfood, Frühstück und Grillcatering.',
            'address' => $this->postalAddress(),
        ];

        if ($logo) {
            $schema['logo'] = $this->imageObject($logo, $name.' Logo');
            $schema['image'] = $schema['logo'];
        }

        $email = Setting::get('email', null, 'contact');
        $phone = Setting::get('phone', null, 'contact');

        if ($email) {
            $schema['email'] = $email;
        }
        if ($phone) {
            $schema['telephone'] = $phone;
        }

        $sameAs = $this->sameAsLinks();
        if ($sameAs !== []) {
            $schema['sameAs'] = $sameAs;
        }

        return $schema;
    }

    public function localBusiness(): array
    {
        $name = Setting::get('company_name', 'Monnemtisch Catering', 'general');
        $legal = Setting::get('legal_name', 'Anatolian Gourmet GmbH', 'general');
        $url = rtrim((string) Setting::get('website_url', config('app.url'), 'general'), '/') ?: url('/');
        $logo = site_logo_url();
        $email = Setting::get('email', null, 'contact');
        $phone = Setting::get('phone', null, 'contact');
        $menuUrl = url('/buffets');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FoodEstablishment',
            '@id' => url('/#business'),
            'name' => $name,
            'legalName' => $legal ?: 'Anatolian Gourmet GmbH',
            'alternateName' => 'Monnemtisch',
            'url' => $url,
            'description' => 'Catering in Mannheim: Buffets, Fingerfood, Frühstück und Grillcatering.',
            'parentOrganization' => ['@id' => url('/#organization')],
            'servesCuisine' => ['Türkisch', 'Orientalisch', 'International'],
            'priceRange' => Setting::get('price_range', '€€', 'general') ?: '€€',
            'currenciesAccepted' => 'EUR',
            'paymentAccepted' => 'Cash, Credit Card, Invoice',
            'address' => $this->postalAddress(),
            'areaServed' => [
                '@type' => 'City',
                'name' => 'Mannheim',
                'containedInPlace' => [
                    '@type' => 'AdministrativeArea',
                    'name' => 'Baden-Württemberg',
                ],
            ],
            'hasMenu' => $menuUrl,
            'menu' => $menuUrl,
        ];

        if ($logo) {
            $schema['image'] = $this->imageObject($logo, $name);
        }
        if ($email) {
            $schema['email'] = $email;
        }
        if ($phone) {
            $schema['telephone'] = $phone;
        }

        $lat = Setting::get('latitude', null, 'contact');
        $lng = Setting::get('longitude', null, 'contact');
        if ($lat && $lng) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
            ];
        }

        $hours = $this->openingHours();
        if ($hours['openingHoursSpecification'] !== []) {
            $schema['openingHoursSpecification'] = $hours['openingHoursSpecification'];
            $schema['openingHours'] = $hours['openingHours'];
        }

        $sameAs = $this->sameAsLinks();
        if ($sameAs !== []) {
            $schema['sameAs'] = $sameAs;
        }

        $rating = $this->aggregateRatingData();
        if ($rating) {
            $schema['aggregateRating'] = $rating;
        }

        $reviews = $this->reviews(
            Review::query()->approved()->latest('id')->limit(5)->get(),
            $name
        );
        if ($reviews !== []) {
            $schema['review'] = $reviews;
        }

        return $schema;
    }

    public function product(Product $product): array
    {
        $url = url('/produkte/'.($product->slug ?? $product->id));
        $image = $product->image
            ? (Str::startsWith($product->image, ['http://', 'https://']) ? $product->image : url($product->image))
            : null;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->short_description ?? Str::limit(strip_tags((string) $product->description), 300),
            'sku' => (string) $product->id,
            'url' => $url,
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'EUR',
                'price' => number_format((float) $product->price, 2, '.', ''),
                'availability' => ($product->is_active ?? true)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => $url,
            ],
        ];

        if ($image) {
            $schema['image'] = $this->imageObject($image, $product->name);
        }

        $nutrition = $this->nutrition($product);
        if ($nutrition) {
            $schema['additionalProperty'] = $nutrition;
        }

        $reviews = $this->reviews($product->relationLoaded('reviews') ? $product->reviews : $product->reviews()->approved()->limit(5)->get(), $product->name);
        if ($reviews !== []) {
            $schema['review'] = $reviews;
        }

        $stats = Review::query()
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->first();

        if ($stats && (int) $stats->review_count > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $stats->avg_rating, 1),
                'reviewCount' => (int) $stats->review_count,
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        return $schema;
    }

    public function faqPage(iterable $faqs): array
    {
        $entities = [];

        foreach ($faqs as $faq) {
            $question = $faq instanceof Faq ? $faq->question : ($faq['question'] ?? null);
            $answer = $faq instanceof Faq ? $faq->answer : ($faq['answer'] ?? null);

            if (! $question || ! $answer) {
                continue;
            }

            $entities[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags((string) $answer),
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'name' => 'Häufige Fragen zum Catering in Mannheim',
            'headline' => 'Häufige Fragen zum Catering in Mannheim',
            'url' => $this->pageUrl ?: url()->current(),
            'mainEntity' => $entities,
        ];
    }

    /**
     * @param  array<int, array{name: string, url?: string}|string>  $items
     */
    public function breadcrumb(array $items): array
    {
        $list = [];
        $position = 1;

        foreach ($items as $item) {
            if (is_string($item)) {
                $list[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $item,
                ];
                continue;
            }

            $entry = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $item['name'] ?? '',
            ];

            if (! empty($item['url'])) {
                $entry['item'] = $item['url'];
            }

            $list[] = $entry;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            '@id' => $this->fragmentId($this->pageUrl ?: url()->current(), 'breadcrumb'),
            'itemListElement' => $list,
        ];
    }

    public function article(BlogPost $post): array
    {
        $url = url('/blog/'.($post->slug ?? $post->id));
        $image = $post->imageAbsoluteUrl();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $post->excerpt ?? Str::limit(strip_tags((string) $post->content), 200),
            'inLanguage' => 'de-DE',
            'datePublished' => optional($post->published_at)->toIso8601String(),
            'dateModified' => optional($post->updated_at)->toIso8601String(),
            'wordCount' => $post->wordCount(),
            'articleSection' => $post->category?->name,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $url,
                'url' => $url,
            ],
            'author' => $this->person($post->authorName(), $post->authorRole()),
            'speakable' => $this->speakable(['h1', '.prose-velora']),
            'publisher' => $this->publisherNode(),
            'about' => [
                '@type' => 'Place',
                'name' => 'Mannheim',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => Setting::get('street', 'H1, 10', 'contact'),
                    'postalCode' => Setting::get('postal_code', '68159', 'contact'),
                    'addressLocality' => 'Mannheim',
                    'addressRegion' => 'Baden-Württemberg',
                    'addressCountry' => 'DE',
                ],
            ],
        ];

        if ($image) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $image,
                'caption' => $post->imageCaption(),
            ];
        }

        return $schema;
    }

    public function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => url('/#website'),
            'url' => url('/'),
            'name' => Setting::get('company_name', 'Monnemtisch Catering', 'general'),
            'inLanguage' => 'de-DE',
            'publisher' => $this->publisherNode(),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => url('/suche').'?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public function webPage(string $url, string $name, ?string $description = null, array $speakableSelectors = ['h1'], ?string $image = null): array
    {
        $imageNode = $this->imageObject($image ?: $this->pageImageUrl(), $name);

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            '@id' => $this->fragmentId($url, 'webpage'),
            'url' => $url,
            'name' => $name,
            'headline' => $name,
            'inLanguage' => 'de-DE',
            'isPartOf' => ['@id' => url('/#website')],
            'about' => ['@id' => url('/#business')],
            'image' => $imageNode,
            'primaryImageOfPage' => $imageNode,
            'breadcrumb' => ['@id' => $this->fragmentId($url, 'breadcrumb')],
            'speakable' => $this->speakable($speakableSelectors),
            'author' => $this->person(primary: true),
            'publisher' => $this->publisherNode(),
            'datePublished' => $this->publishedAt(),
            'dateModified' => $this->modifiedAt(),
        ];

        if ($description) {
            $schema['description'] = $description;
        }

        return $schema;
    }

    public function person(string $name = 'Monnemtisch Küche', string $jobTitle = 'Catering-Team Mannheim', bool $primary = false): array
    {
        $schema = [
            '@type' => 'Person',
            'name' => $name,
            'jobTitle' => $jobTitle,
            'description' => 'Küchenteam von Monnemtisch Catering in Mannheim.',
            'url' => url('/'),
            'worksFor' => ['@id' => url('/#organization')],
            'sameAs' => $this->sameAsLinks(),
        ];

        $photo = function_exists('velora_photo') ? velora_photo('chef') : site_logo_url();
        if ($photo) {
            $schema['image'] = $this->imageObject($photo, $name);
        }

        if ($primary) {
            $schema['@id'] = url('/#person');
        }

        return $schema;
    }

    public function authorName(): string
    {
        return 'Monnemtisch Küche';
    }

    public function publisherName(): string
    {
        return (string) Setting::get('company_name', 'Monnemtisch Catering', 'general');
    }

    public function publishedAt(): string
    {
        return $this->pagePublished ?: '2026-08-13T10:00:00+02:00';
    }

    public function modifiedAt(): string
    {
        return $this->pageModified ?: now('Europe/Berlin')->toIso8601String();
    }

    public function speakable(array $cssSelectors): array
    {
        return [
            '@type' => 'SpeakableSpecification',
            'cssSelector' => array_values($cssSelectors),
        ];
    }

    public function imageObject(string $url, ?string $caption = null): array
    {
        $absolute = $this->absoluteUrl($url);
        $schema = [
            '@type' => 'ImageObject',
            'url' => $absolute,
            'contentUrl' => $absolute,
        ];

        if ($caption) {
            $schema['caption'] = $caption;
            $schema['name'] = $caption;
        }

        return $schema;
    }

    public function videoObject(string $url, string $name, ?string $thumbnail = null): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $name,
            'contentUrl' => $url,
            'embedUrl' => $url,
        ];

        if ($thumbnail) {
            $schema['thumbnailUrl'] = $this->absoluteUrl($thumbnail);
        }

        return $schema;
    }

    /**
     * @param  list<array{name: string, url: string, image?: string|null}>  $items
     */
    public function itemList(string $name, array $items): array
    {
        $elements = [];
        foreach (array_values($items) as $i => $item) {
            $entry = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'url' => $item['url'],
            ];
            if (! empty($item['image'])) {
                $entry['image'] = $this->absoluteUrl((string) $item['image']);
            }
            $elements[] = $entry;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'numberOfItems' => count($elements),
            'itemListElement' => $elements,
        ];
    }

    public function howToOrder(): array
    {
        $headline = 'Catering in Mannheim bestellen';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $headline,
            'headline' => $headline,
            'url' => url('/'),
            'description' => 'So bestellen Sie Buffets, Fingerfood oder Grillcatering bei Monnemtisch.',
            'image' => $this->imageObject($this->pageImageUrl(), $headline),
            'step' => [
                [
                    '@type' => 'HowToStep',
                    'position' => 1,
                    'name' => 'Auswählen',
                    'text' => 'Paket entdecken und nach Personen zusammenstellen.',
                ],
                [
                    '@type' => 'HowToStep',
                    'position' => 2,
                    'name' => 'Termin festlegen',
                    'text' => 'Datum, Adresse und Personenzahl angeben.',
                ],
                [
                    '@type' => 'HowToStep',
                    'position' => 3,
                    'name' => 'Online bezahlen',
                    'text' => 'Sicher bezahlen – wir kochen frisch und liefern pünktlich.',
                ],
            ],
        ];
    }

    public function recipe(Product $product): ?array
    {
        $ingredients = $this->ingredientList($product);
        if ($ingredients === []) {
            return null;
        }

        $url = url('/produkte/'.($product->slug ?? $product->id));
        $image = $product->image_url ?: $product->displayImage() ?: $product->image;
        $instructions = $this->recipeInstructions($product);

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Recipe',
            'name' => $product->name,
            'description' => $product->short_description ?? Str::limit(strip_tags((string) $product->description), 300),
            'recipeCuisine' => 'Catering',
            'recipeCategory' => $product->category?->name ?? 'Catering',
            'recipeIngredient' => $ingredients,
            'recipeInstructions' => $instructions,
            'recipeYield' => max(1, (int) ($product->minimum_persons ?? 1)).' Portionen',
            'author' => $this->person(),
            'publisher' => ['@id' => url('/#organization')],
            'url' => $url,
        ];

        if ($image) {
            $schema['image'] = $this->imageObject((string) $image, $product->name);
        }

        $nutrition = $this->nutrition($product);
        if ($nutrition) {
            $schema['nutrition'] = $nutrition;
        }

        return $schema;
    }

    public function nutrition(Product $product): ?array
    {
        $info = is_array($product->nutritional_info) ? $product->nutritional_info : [];
        $calories = $product->calories ?? ($info['calories'] ?? $info['kcal'] ?? null);

        $schema = array_filter([
            '@type' => 'NutritionInformation',
            'calories' => $calories ? $calories.' kcal' : null,
            'proteinContent' => $this->nutritionValue($info, ['protein', 'eiweiss', 'eiweiß']),
            'fatContent' => $this->nutritionValue($info, ['fat', 'fett']),
            'carbohydrateContent' => $this->nutritionValue($info, ['carbs', 'carbohydrates', 'kohlenhydrate']),
            'fiberContent' => $this->nutritionValue($info, ['fiber', 'ballaststoffe']),
            'sugarContent' => $this->nutritionValue($info, ['sugar', 'zucker']),
            'sodiumContent' => $this->nutritionValue($info, ['sodium', 'salz', 'salt']),
        ]);

        return count($schema) > 1 ? $schema : null;
    }

    public function reviews(iterable $reviews, ?string $itemName = null): array
    {
        $items = [];
        foreach ($reviews as $review) {
            if (! $review instanceof Review || ! $review->is_approved) {
                continue;
            }
            $entry = [
                '@type' => 'Review',
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => (int) $review->rating,
                    'bestRating' => 5,
                    'worstRating' => 1,
                ],
                'author' => [
                    '@type' => 'Person',
                    'name' => $review->name ?: 'Gast',
                ],
                'reviewBody' => $review->body ?: $review->title,
            ];
            if ($itemName) {
                $entry['itemReviewed'] = [
                    '@type' => 'Thing',
                    'name' => $itemName,
                ];
            }
            $items[] = $entry;
        }

        return $items;
    }

    public function menuPackage(CateringPackage $package): array
    {
        $url = $package->publicUrl();
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $package->name,
            'description' => filled($package->seo_text)
                ? Str::limit(preg_replace('/\s+/', ' ', trim($package->seo_text)), 300)
                : ($package->subtitle ?: Str::limit(strip_tags((string) $package->description), 300)),
            'url' => $url,
            'brand' => ['@id' => url('/#organization')],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'EUR',
                'price' => number_format((float) $package->price_per_person, 2, '.', ''),
                'availability' => 'https://schema.org/InStock',
                'url' => $url,
            ],
        ];

        $image = $package->imageUrl();
        if ($image) {
            $schema['image'] = $this->imageObject($image, $package->name);
        }

        return $schema;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function siteGraph(): array
    {
        return [
            $this->withoutContext($this->localBusiness()),
            $this->withoutContext($this->organization()),
            $this->withoutContext($this->website()),
            $this->person(primary: true),
        ];
    }

    public function setPageMeta(?string $headline = null, ?string $description = null, ?string $image = null, ?string $url = null, ?string $published = null, ?string $modified = null): void
    {
        $this->pageHeadline = $headline ? html_entity_decode($headline, ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
        $this->pageDescription = $description ? html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
        $this->pageImage = $image ?: null;
        $this->pageUrl = $url ?: null;
        if ($published) {
            $this->pagePublished = $published;
        }
        if ($modified) {
            $this->pageModified = $modified;
        }
    }

    /**
     * @param  array<string, mixed>  $node
     */
    public function pushJson(?string $json): void
    {
        $json = trim((string) $json);
        if ($json === '') {
            return;
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return;
        }

        if (isset($decoded['@graph']) && is_array($decoded['@graph'])) {
            $this->pushMany($decoded['@graph']);

            return;
        }

        if (array_is_list($decoded)) {
            $this->pushMany($decoded);

            return;
        }

        $this->push($decoded);
    }

    public function push(array $node): void
    {
        if ($node === []) {
            return;
        }

        $this->pageNodes[] = $this->withoutContext($node);
    }

    /**
     * @param  list<array<string, mixed>>  $nodes
     */
    public function pushMany(array $nodes): void
    {
        foreach ($nodes as $node) {
            if (is_array($node) && $node !== []) {
                $this->push($node);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $extra
     * @return array{ '@context': string, '@graph': list<array<string, mixed>> }
     */
    public function document(array $extra = []): array
    {
        $graph = [];
        $shared = $this->isHomeUrl($this->pageUrl) ? $this->siteGraph() : [];

        foreach (array_merge($shared, $this->pageNodes, $extra) as $node) {
            if (! is_array($node) || $node === []) {
                continue;
            }

            $graph[] = $this->withoutContext($node);
        }

        $this->pageNodes = [];

        return [
            '@context' => 'https://schema.org',
            '@graph' => $this->ensureSchemaDepth($graph),
        ];
    }

    /**
     * @param  iterable<int, array{label?: string, key?: string, url?: string}|object>  $menus
     */
    public function homeGraph(iterable $menus = [], iterable $packages = []): array
    {
        $menuItems = [];
        foreach ($menus as $key => $menu) {
            if (is_array($menu)) {
                $menuItems[] = [
                    'name' => $menu['label'] ?? (string) $key,
                    'url' => $menu['url'] ?? route('packages.index', $menu['key'] ?? $key),
                    'image' => isset($menu['photo']) ? velora_photo($menu['photo']) : null,
                ];
            }
        }

        $packageItems = [];
        foreach ($packages as $package) {
            if ($package instanceof CateringPackage) {
                $packageItems[] = [
                    'name' => $package->name,
                    'url' => $package->publicUrl(),
                    'image' => $package->imageUrl(),
                ];
            }
        }

        $homeUrl = url('/');
        $graph = [
            $this->withoutContext($this->webPage(
                $homeUrl,
                'Catering Mannheim – Buffets & Fingerfood',
                'Catering in Mannheim: Buffets, Fingerfood, Frühstück und Grillcatering ab 10 Personen.',
                ['h1', '.lead'],
                velora_photo('hero')
            )),
            $this->withoutContext($this->howToOrder()),
            array_merge($this->speakable(['h1', '.lead']), [
                '@id' => $this->fragmentId($homeUrl, 'speakable'),
            ]),
        ];

        $productCount = 0;
        foreach ($packages as $package) {
            if ($package instanceof CateringPackage && $productCount < 3) {
                $graph[] = $this->withoutContext($this->menuPackage($package));
                $productCount++;
            }
        }

        foreach (
            Product::query()
                ->active()
                ->whereNotNull('ingredients')
                ->where('ingredients', '!=', '')
                ->orderByDesc('is_featured')
                ->limit(4)
                ->get() as $dish
        ) {
            $recipe = $this->recipe($dish);
            if ($recipe) {
                $graph[] = $this->withoutContext($recipe);
            }
        }

        if ($menuItems !== []) {
            $graph[] = $this->withoutContext($this->itemList('Catering-Menüs Mannheim', $menuItems));
        }
        if ($packageItems !== []) {
            $graph[] = $this->withoutContext($this->itemList('Beliebte Catering-Pakete', $packageItems));
        }

        return $graph;
    }

    /**
     * @return list<string>
     */
    protected function ingredientList(Product $product): array
    {
        $raw = $product->ingredients;
        if (is_array($raw)) {
            return array_values(array_filter(array_map(fn ($item) => trim((string) $item), $raw)));
        }

        $text = trim((string) $raw);
        if ($text === '') {
            return [];
        }

        $parts = preg_split('/[\r\n,;]+/', $text) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }

    /**
     * @return list<array{ '@type': string, text: string }>
     */
    protected function recipeInstructions(Product $product): array
    {
        $text = trim(strip_tags((string) ($product->description ?: $product->short_description)));
        if ($text === '') {
            $text = $product->name.' wird frisch in Mannheim zubereitet und zum Event geliefert.';
        }

        $chunks = preg_split('/(?<=[.!?])\s+/', $text) ?: [$text];
        $steps = [];
        foreach (array_slice($chunks, 0, 6) as $chunk) {
            $chunk = trim($chunk);
            if ($chunk !== '') {
                $steps[] = [
                    '@type' => 'HowToStep',
                    'text' => $chunk,
                ];
            }
        }

        return $steps !== [] ? $steps : [['@type' => 'HowToStep', 'text' => $text]];
    }

    /**
     * @param  array<string, mixed>  $info
     * @param  list<string>  $keys
     */
    protected function nutritionValue(array $info, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($info[$key]) && $info[$key] !== '' && $info[$key] !== null) {
                $value = $info[$key];

                return is_numeric($value) ? $value.' g' : (string) $value;
            }
        }

        return null;
    }

    protected function absoluteUrl(string $url): string
    {
        return str_starts_with($url, 'http') ? $url : url($url);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    /**
     * @param  list<array<string, mixed>>  $graph
     * @return list<array<string, mixed>>
     */
    protected function ensureSchemaDepth(array $graph): array
    {
        $url = $this->pageUrl ?: url()->current();
        $headline = $this->pageHeadline
            ?: Setting::get('company_name', 'Monnemtisch Catering', 'general').' – Catering Mannheim';
        $description = $this->pageDescription
            ?: 'Catering in Mannheim: Buffets, Fingerfood, Frühstück und Grillcatering.';

        $hasWebPage = false;
        foreach ($graph as $index => $node) {
            if (($node['@type'] ?? null) !== 'WebPage') {
                continue;
            }

            $hasWebPage = true;
            $graph[$index]['headline'] = $node['headline'] ?? ($node['name'] ?? $headline);
            $graph[$index]['url'] = $node['url'] ?? $url;
            $graph[$index]['name'] = $node['name'] ?? $graph[$index]['headline'];
            if (empty($node['image'])) {
                $image = $this->imageObject($this->pageImageUrl(), $graph[$index]['headline']);
                $graph[$index]['image'] = $image;
                $graph[$index]['primaryImageOfPage'] = $image;
            } elseif (empty($node['primaryImageOfPage'])) {
                $graph[$index]['primaryImageOfPage'] = $node['image'];
            }
            $graph[$index]['speakable'] = $node['speakable'] ?? $this->speakable(['h1']);
            $graph[$index]['author'] = $node['author'] ?? $this->person(primary: true);
            $graph[$index]['publisher'] = $node['publisher'] ?? $this->publisherNode();
            $graph[$index]['datePublished'] = $node['datePublished'] ?? $this->publishedAt();
            $graph[$index]['dateModified'] = $node['dateModified'] ?? $this->modifiedAt();
        }

        if (! $hasWebPage) {
            $graph[] = $this->withoutContext($this->webPage($url, $headline, $description));
        }

        $hasBreadcrumb = $this->graphHasType($graph, 'BreadcrumbList');
        foreach ($graph as $index => $node) {
            if (($node['@type'] ?? null) === 'WebPage' && ! $hasBreadcrumb) {
                unset($graph[$index]['breadcrumb']);
            }
        }
        $graph = array_values($graph);

        if ($this->isHomeUrl($url) && ! $this->graphHasType($graph, 'SpeakableSpecification', nested: false)) {
            $graph[] = array_merge($this->speakable(['h1']), [
                '@id' => $this->fragmentId($url, 'speakable'),
            ]);
        }

        if ($this->isHomeUrl($url)) {
            if (! $this->graphHasType($graph, 'HowTo')) {
                $graph[] = $this->withoutContext($this->howToOrder());
            }
            if (! $this->graphHasType($graph, 'FAQPage')) {
                $graph[] = $this->withoutContext($this->faqPage($this->fallbackFaqs()));
            }
            if (! $this->graphHasType($graph, 'Product')) {
                $graph[] = $this->catalogProduct();
            }
        }

        return $graph;
    }

    /**
     * @param  list<array<string, mixed>>  $graph
     */
    protected function graphHasType(array $graph, string $type, bool $nested = true): bool
    {
        foreach ($graph as $node) {
            if ($this->nodeHasType($node, $type, $nested)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected function nodeHasType(array $node, string $type, bool $nested = true): bool
    {
        $nodeType = $node['@type'] ?? null;
        if ($nodeType === $type || (is_array($nodeType) && in_array($type, $nodeType, true))) {
            return true;
        }

        if (! $nested) {
            return false;
        }

        foreach ($node as $value) {
            if (is_array($value) && $value !== [] && array_is_list($value)) {
                foreach ($value as $child) {
                    if (is_array($child) && $this->nodeHasType($child, $type)) {
                        return true;
                    }
                }
            } elseif (is_array($value) && isset($value['@type']) && $this->nodeHasType($value, $type)) {
                return true;
            }
        }

        return false;
    }

    protected function pageImageUrl(): string
    {
        if ($this->pageImage) {
            return $this->pageImage;
        }

        $logo = site_logo_url();

        return function_exists('velora_photo')
            ? velora_photo('hero')
            : ($logo ?: url('/images/placeholders/hero.svg'));
    }

    protected function fragmentId(string $url, string $fragment): string
    {
        return rtrim($url, '/').'#'.$fragment;
    }

    protected function isHomeUrl(?string $url = null): bool
    {
        return rtrim((string) ($url ?: url()->current()), '/') === rtrim(url('/'), '/');
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    protected function fallbackFaqs(): array
    {
        return [
            [
                'question' => 'Liefern Sie in Mannheim?',
                'answer' => 'Ja. Innerhalb Mannheims ist die Lieferung kostenlos, außerhalb berechnen wir 1 € pro Kilometer.',
            ],
            [
                'question' => 'Ab wie vielen Personen kann ich bestellen?',
                'answer' => 'Unsere Catering-Pakete sind ab 10 Personen buchbar.',
            ],
            [
                'question' => 'Wie weit im Voraus sollte ich bestellen?',
                'answer' => 'Bitte mindestens 48 Stunden vor dem Termin bestellen, damit wir frisch kochen können.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function catalogProduct(): array
    {
        $url = url('/buffets');

        return [
            '@type' => 'Product',
            'name' => 'Buffet Catering Mannheim',
            'description' => 'Buffet Catering in Mannheim ab 10 Personen – Festpreis, frei kombinierbar.',
            'url' => $url,
            'image' => $this->imageObject(velora_photo('buffet'), 'Buffet Catering Mannheim'),
            'brand' => ['@id' => url('/#organization')],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'EUR',
                'availability' => 'https://schema.org/InStock',
                'url' => $url,
                'eligibleQuantity' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => 10,
                    'unitText' => 'Personen',
                ],
            ],
        ];
    }

    protected function withoutContext(array $schema): array
    {
        unset($schema['@context']);

        return $schema;
    }

    /**
     * @return array<string, string>
     */
    protected function postalAddress(): array
    {
        $street = Setting::get('street', null, 'contact')
            ?: Setting::get('address', 'H1, 10', 'contact')
            ?: 'H1, 10';

        return [
            '@type' => 'PostalAddress',
            'streetAddress' => $street,
            'postalCode' => Setting::get('postal_code', '68159', 'contact') ?: '68159',
            'addressLocality' => Setting::get('city', 'Mannheim', 'contact') ?: 'Mannheim',
            'addressRegion' => 'Baden-Württemberg',
            'addressCountry' => $this->countryCode(Setting::get('country', 'DE', 'contact')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function publisherNode(): array
    {
        $name = $this->publisherName();
        $logo = site_logo_url() ?: $this->pageImageUrl();

        return [
            '@type' => 'Organization',
            '@id' => url('/#organization'),
            'name' => $name,
            'legalName' => Setting::get('legal_name', 'Anatolian Gourmet GmbH', 'general') ?: 'Anatolian Gourmet GmbH',
            'url' => url('/'),
            'logo' => $this->imageObject($logo, $name.' Logo'),
        ];
    }

    /**
     * @return list<string>
     */
    protected function sameAsLinks(): array
    {
        $defaults = [
            'facebook' => 'https://www.facebook.com/monnemtischcatering',
            'instagram' => 'https://www.instagram.com/monnemtischcatering',
            'linkedin' => 'https://www.linkedin.com/company/monnemtisch-catering',
        ];

        $links = [url('/')];

        foreach ($defaults as $key => $fallback) {
            $url = trim((string) Setting::get($key, $fallback, 'social')) ?: $fallback;
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $links[] = $url;
            }
        }

        foreach (['youtube', 'tiktok'] as $key) {
            $url = trim((string) Setting::get($key, '', 'social'));
            if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
                $links[] = $url;
            }
        }

        return array_values(array_unique($links));
    }

    /**
     * @return array{openingHoursSpecification: list<array<string, mixed>>, openingHours: list<string>}
     */
    protected function openingHours(): array
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        $specs = [];
        $compact = [];

        foreach (BusinessHour::query()->orderBy('day_of_week')->get() as $hour) {
            if (! $hour->is_open) {
                continue;
            }

            $open = $hour->formattedOpen();
            $close = $hour->formattedClose();
            $day = $days[(int) $hour->day_of_week] ?? null;

            if (! $open || ! $close || ! $day) {
                continue;
            }

            $specs[] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => 'https://schema.org/'.$day,
                'opens' => $open,
                'closes' => $close,
            ];
            $compact[] = substr($day, 0, 2).' '.$open.'-'.$close;
        }

        return [
            'openingHoursSpecification' => $specs,
            'openingHours' => $compact,
        ];
    }

    protected function countryCode(mixed $country): string
    {
        $value = strtolower(trim((string) $country));

        return match (true) {
            $value === '', $value === 'de', $value === 'deutschland', $value === 'germany' => 'DE',
            default => strtoupper(substr((string) $country, 0, 2)),
        };
    }

    public function aggregateRating(): array
    {
        $data = $this->aggregateRatingData();

        if (! $data) {
            return [];
        }

        return array_merge(['@context' => 'https://schema.org'], $data);
    }

    protected function aggregateRatingData(): ?array
    {
        $stats = Review::query()
            ->where('is_approved', true)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->first();

        if (! $stats || (int) $stats->review_count === 0) {
            return null;
        }

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => round((float) $stats->avg_rating, 1),
            'reviewCount' => (int) $stats->review_count,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }
}
