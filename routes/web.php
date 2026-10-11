<?php

use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\CityController;
use App\Http\Controllers\Frontend\ConfiguratorController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\EventLandingController;
use App\Http\Controllers\Frontend\FavoriteController;
use App\Http\Controllers\Frontend\GalleryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\NewsletterController;
use App\Http\Controllers\Frontend\PackageController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\QuoteController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\Frontend\LlmsController;
use App\Http\Controllers\Frontend\MediaController;
use App\Http\Controllers\Frontend\RobotsController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\Kitchen\KitchenAuthController;
use App\Http\Controllers\Kitchen\KitchenBoardController;
use App\Models\CateringPackage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public frontend routes – Monnemtisch Catering
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/lieferung/pruefen', [HomeController::class, 'checkAvailability'])
    ->middleware('throttle:30,1')
    ->name('delivery.check');

Route::get('/branding/logo', [HomeController::class, 'logo'])->name('branding.logo');
Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media.show');

// Legacy product / category shop → package menus
Route::redirect('/produkte', '/buffets', 301)->name('products.index');
Route::redirect('/products', '/buffets', 301);
Route::redirect('/products/{slug}', '/buffets', 301)->name('products.show');

Route::redirect('/extras', '/warenkorb', 301);
Route::redirect('/getraenke', '/warenkorb', 301);

Route::prefix('mutfak')->name('kitchen.')->group(function () {
    Route::get('/giris', [KitchenAuthController::class, 'showLogin'])->name('login');
    Route::post('/giris', [KitchenAuthController::class, 'login'])
        ->middleware('throttle:kitchen-login')
        ->name('login.store');
    Route::post('/cikis', [KitchenAuthController::class, 'logout'])->name('logout');

    Route::middleware('kitchen')->group(function () {
        Route::get('/', [KitchenBoardController::class, 'index'])->name('board');
        Route::get('/ping', [KitchenBoardController::class, 'ping'])
            ->middleware('throttle:60,1')
            ->name('ping');
        Route::post('/kabul-hepsi', [KitchenBoardController::class, 'acceptAll'])->name('accept-all');
        Route::post('/{order}/kabul', [KitchenBoardController::class, 'accept'])
            ->whereNumber('order')
            ->name('accept');
        Route::post('/{order}/tamamla', [KitchenBoardController::class, 'complete'])
            ->whereNumber('order')
            ->name('complete');
        Route::get('/{order}', [KitchenBoardController::class, 'show'])
            ->whereNumber('order')
            ->name('show');
    });
});

Route::redirect('/catering', '/buffets', 301)->name('catering.index');
Route::get('/catering/{slug}', [PackageController::class, 'redirectLegacyMenu'])->name('catering.show');

// Event / brand landings
Route::redirect('/hochzeiten', '/', 301);
Route::redirect('/firmen-catering', '/', 301);
Route::redirect('/event-catering', '/', 301);
Route::get('/messe-catering', [EventLandingController::class, 'show'])->defaults('slug', 'messe-catering')->name('events.messe');
Route::get('/partyservice', [EventLandingController::class, 'show'])->defaults('slug', 'partyservice')->name('events.partyservice');
Route::get('/ueber-uns', [EventLandingController::class, 'show'])->defaults('slug', 'ueber-uns')->name('about');

// Gallery
Route::get('/galerie', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/galerie/{slug}', [GalleryController::class, 'show'])->name('gallery.show');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/kategorie/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Contact
Route::get('/kontakt', [ContactController::class, 'show'])->name('contact.show');
Route::post('/kontakt', [ContactController::class, 'store'])
    ->middleware('throttle:public-forms')
    ->name('contact.store');

// Quote / Angebot
Route::get('/angebot', [QuoteController::class, 'create'])->name('quote.create');
Route::post('/angebot', [QuoteController::class, 'store'])
    ->middleware('throttle:public-forms')
    ->name('quote.store');
Route::get('/angebot/danke/{quoteNumber}', [QuoteController::class, 'success'])->name('quote.success');

// Cart
Route::get('/warenkorb', [CartController::class, 'index'])->name('cart.index');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:public-forms')
    ->name('checkout.store');
Route::post('/checkout/delivery-distance', [CheckoutController::class, 'quoteDistance'])
    ->middleware('throttle:30,1')
    ->name('checkout.delivery-distance');
Route::get('/checkout/danke/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/zahlung/stripe/erfolg', [PaymentController::class, 'stripeSuccess'])->name('payments.stripe.success');
Route::get('/zahlung/paypal/return', [PaymentController::class, 'paypalReturn'])->name('payments.paypal.return');
Route::get('/zahlung/fortsetzen/{orderNumber}', [PaymentController::class, 'resume'])
    ->middleware('signed')
    ->name('payments.resume');
Route::get('/zahlung/abgebrochen/{order}', [PaymentController::class, 'cancel'])->name('payments.cancel');
Route::get('/webhooks/stripe', [PaymentController::class, 'webhookInfo'])->name('webhooks.stripe.info');
Route::post('/webhooks/stripe', [PaymentController::class, 'stripeWebhook'])
    ->middleware('throttle:60,1')
    ->name('webhooks.stripe');
Route::get('/webhooks/paypal', [PaymentController::class, 'webhookInfo'])->name('webhooks.paypal.info');
Route::post('/webhooks/paypal', [PaymentController::class, 'paypalWebhook'])
    ->middleware('throttle:60,1')
    ->name('webhooks.paypal');

// Configurator (legacy → Buffets)
Route::redirect('/konfigurator', '/buffets', 301)->name('configurator.index');
Route::post('/konfigurator/calculate', [ConfiguratorController::class, 'calculate'])
    ->middleware('throttle:30,1')
    ->name('configurator.calculate');

// Legal pages
Route::get('/datenschutz', [PageController::class, 'legal'])->defaults('slug', 'datenschutz')->name('legal.datenschutz');
Route::get('/impressum', [PageController::class, 'legal'])->defaults('slug', 'impressum')->name('legal.impressum');
Route::get('/agb', [PageController::class, 'legal'])->defaults('slug', 'agb')->name('legal.agb');
Route::get('/widerruf', [PageController::class, 'legal'])->defaults('slug', 'widerruf')->name('legal.widerruf');
Route::get('/allergene', [PageController::class, 'legal'])->defaults('slug', 'allergene')->name('legal.allergene');
Route::get('/legal/{slug}', [PageController::class, 'legal'])
    ->whereIn('slug', ['datenschutz', 'impressum', 'agb', 'widerruf', 'allergene'])
    ->name('legal.show');

// Search
Route::get('/suche', SearchController::class)
    ->middleware('throttle:30,1')
    ->name('search');

// Newsletter
Route::post('/newsletter', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:public-forms')
    ->name('newsletter.subscribe');

// Favorites
Route::post('/favoriten/toggle', [FavoriteController::class, 'toggle'])
    ->middleware('throttle:30,1')
    ->name('favorites.toggle');

// Reviews
Route::get('/bewertungen', [ReviewController::class, 'index'])->name('reviews.index');

// City landings
Route::get('/standort/{slug}', [CityController::class, 'show'])->name('cities.show');

// CMS pages by slug (catch-all after specific routes)
Route::get('/seite/{slug}', [PageController::class, 'show'])->name('pages.show');

// Package menus (catch-all after specific routes)
$menuKeys = CateringPackage::menuKeys();
Route::get('/{menu}', [PackageController::class, 'index'])
    ->whereIn('menu', $menuKeys)
    ->name('packages.index');
Route::get('/{menu}/{slug}', [PackageController::class, 'show'])
    ->whereIn('menu', $menuKeys)
    ->name('packages.show');
Route::post('/{menu}/{slug}/warenkorb', [PackageController::class, 'addToCart'])
    ->middleware('throttle:60,1')
    ->whereIn('menu', $menuKeys)
    ->name('packages.cart');

// SEO
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/llms.txt', LlmsController::class)->name('llms');
Route::get('/llms-full.txt', [LlmsController::class, 'full'])->name('llms.full');

/*
|--------------------------------------------------------------------------
| AJAX cart API (session / web middleware)
|--------------------------------------------------------------------------
*/
Route::prefix('warenkorb')->name('cart.')->middleware('throttle:60,1')->group(function () {
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/item/{key}', [CartController::class, 'update'])->name('update');
    Route::delete('/item/{key}', [CartController::class, 'remove'])->name('remove');
    Route::post('/extras', [CartController::class, 'addExtra'])->name('extras.add');
    Route::patch('/extras/{extraId}', [CartController::class, 'updateExtra'])->name('extras.update');
    Route::delete('/extras/{extraId}', [CartController::class, 'removeExtra'])->name('extras.remove');
    Route::post('/event', [CartController::class, 'setEvent'])->name('event');
    Route::post('/delivery', [CartController::class, 'setDelivery'])->name('delivery');
    Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/coupon', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    Route::post('/leeren', [CartController::class, 'clear'])->name('clear');
});

Route::prefix('api/cart')->name('api.cart.')->middleware('throttle:60,1')->group(function () {
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/item/{key}', [CartController::class, 'update'])->name('update');
    Route::delete('/item/{key}', [CartController::class, 'remove'])->name('remove');
    Route::post('/extras', [CartController::class, 'addExtra'])->name('extras.add');
    Route::patch('/extras/{extraId}', [CartController::class, 'updateExtra'])->name('extras.update');
    Route::delete('/extras/{extraId}', [CartController::class, 'removeExtra'])->name('extras.remove');
    Route::get('/', function () {
        $cart = app(\App\Services\CartService::class);

        return response()->json([
            'success' => true,
            'cart' => $cart->getCart(),
            'totals' => $cart->totals(),
        ]);
    })->name('show');
});
