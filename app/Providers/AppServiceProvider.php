<?php

namespace App\Providers;

use App\Services\ActivityLogService;
use App\Services\AnalyticsService;
use App\Services\AvailabilityService;
use App\Services\BusinessNotificationService;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\DeliveryService;
use App\Services\EmailTemplateService;
use App\Services\GoogleReviewsService;
use App\Services\MediaService;
use App\Services\NewsletterService;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\PdfService;
use App\Services\PricingService;
use App\Services\QuoteService;
use App\Services\AllergenCatalog;
use App\Services\SchemaService;
use App\Services\SeoService;
use App\Services\SettingService;
use App\Services\TelegramService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->useLangPath(base_path('lang'));

        $this->app->singleton(SettingService::class);
        $this->app->singleton(DeliveryService::class);
        $this->app->singleton(CouponService::class);
        $this->app->singleton(PricingService::class);
        $this->app->singleton(CartService::class);
        $this->app->singleton(AvailabilityService::class);
        $this->app->singleton(MediaService::class);
        $this->app->singleton(AllergenCatalog::class);
        $this->app->singleton(SchemaService::class);
        $this->app->singleton(SeoService::class);
        $this->app->singleton(PdfService::class);
        $this->app->singleton(EmailTemplateService::class);
        $this->app->singleton(AnalyticsService::class);
        $this->app->singleton(ActivityLogService::class);
        $this->app->singleton(GoogleReviewsService::class);
        $this->app->singleton(NewsletterService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(TelegramService::class);
        $this->app->singleton(BusinessNotificationService::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(QuoteService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->setLocale(config('app.locale', 'de'));

        Password::defaults(function () {
            return $this->app->environment('production')
                ? Password::min(12)->mixedCase()->numbers()
                : Password::min(8);
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            Config::set('app.debug', false);
            Config::set('session.secure', true);
            Config::set('session.http_only', true);
            Config::set('session.same_site', 'lax');
        }

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('kitchen-login', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });

        RateLimiter::for('public-forms', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });

        try {
            $settings = app(SettingService::class);
            $fromAddress = $settings->string('from_address', '', 'mail')
                ?: $settings->string('from_address', '', 'smtp')
                ?: $settings->string('email', 'info@monnemtisch.de', 'contact');
            $fromName = $settings->string('from_name', '', 'mail')
                ?: $settings->string('from_name', '', 'smtp')
                ?: (string) config('app.name');
            if (filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
                Config::set('mail.from.address', $fromAddress);
                Config::set('mail.from.name', $fromName !== '' ? $fromName : (string) config('app.name'));
            }

            $host = $settings->string('host', '', 'smtp');
            if ($host !== '') {
                $encryption = strtolower($settings->string('encryption', 'tls', 'smtp'));
                $port = $settings->int('port', 587, 'smtp');
                Config::set('mail.default', 'smtp');
                Config::set('mail.mailers.smtp.host', $host);
                Config::set('mail.mailers.smtp.port', $port);
                Config::set('mail.mailers.smtp.username', $settings->string('username', '', 'smtp') ?: null);
                Config::set('mail.mailers.smtp.password', $settings->string('password', '', 'smtp') ?: null);
                Config::set('mail.mailers.smtp.scheme', $encryption === 'ssl' ? 'smtps' : null);
                Config::set('mail.mailers.smtp.encryption', in_array($encryption, ['tls', 'ssl'], true) ? $encryption : null);
            }
        } catch (\Throwable) {
        }

        Gate::before(function ($user, $ability) {
            if (is_object($user) && method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
                return true;
            }

            return null;
        });

        View::composer('layouts.frontend', function ($view) {
            $cartCount = 0;
            $cartTotals = [
                'subtotal' => 0,
                'delivery_fee' => 0,
                'discount' => 0,
                'tax_amount' => 0,
                'total' => 0,
                'item_count' => 0,
            ];

            try {
                $cart = app(CartService::class);
                $cartTotals = array_merge($cartTotals, $cart->totals());
                $cartCount = (int) ($cartTotals['item_count'] ?? 0);
            } catch (\Throwable $e) {
                $sessionCart = session('cart', []);
                $cartCount = is_array($sessionCart['items'] ?? null) ? count($sessionCart['items']) : 0;
            }

            $phone = '';
            $email = '';
            try {
                $settings = app(SettingService::class);
                $phone = $settings->string('phone', '+49 711 12345678', 'contact');
                $email = $settings->string('email', 'info@monnemtisch.de', 'contact');
            } catch (\Throwable $e) {
                $phone = '+49 711 12345678';
                $email = 'info@monnemtisch.de';
            }

            $view->with([
                'cartCount' => $cartCount,
                'cartTotals' => $cartTotals,
                'sitePhone' => $phone,
                'siteEmail' => $email,
                'isHome' => request()->routeIs('home'),
            ]);
        });

        View::composer('admin.layouts.app', function ($view) {
            $unread = 0;
            $items = collect();
            try {
                if (auth()->check()) {
                    $query = \App\Models\AdminNotification::query()->where('user_id', auth()->id());
                    $unread = (clone $query)->unread()->count();
                    $items = (clone $query)->latest()->limit(8)->get();
                }
            } catch (\Throwable) {
            }
            $view->with([
                'adminUnreadCount' => $unread,
                'adminNotifications' => $items,
            ]);
        });
    }
}
