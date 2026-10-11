<?php

use App\Http\Controllers\Admin\AbandonedCartController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AnalyticsAdminController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BlockedDateController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BusinessHourController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\EventTypeController;
use App\Http\Controllers\Admin\ExtraController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\HomepageSettingController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuPageController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->middleware('throttle:admin-login');
    });

    Route::post('logout', [LoginController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    Route::middleware(['auth', 'role:Super Admin|Admin|Manager|Editor|Sales|Kitchen'])->group(function () {
        // Kitchen: orders only
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/analiz', [OrderController::class, 'analysis'])->name('orders.analysis');
        Route::get('orders/{order}/ingredients', [OrderController::class, 'ingredientStatus'])->name('orders.ingredients.status');
        Route::post('orders/{order}/ingredients', [OrderController::class, 'analyzeIngredients'])->name('orders.ingredients.analyze');
        Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::put('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::patch('orders/{order}/payment', [OrderController::class, 'markPaid'])->name('orders.mark-paid');
        Route::post('orders/{order}/mutfak', [OrderController::class, 'sendToKitchen'])->name('orders.send-kitchen');
        Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::delete('notifications/all', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
        Route::get('notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

        Route::middleware('role:Super Admin|Admin|Manager|Editor|Sales')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            Route::get('abandoned-carts', [AbandonedCartController::class, 'index'])->name('abandoned-carts.index');
            Route::post('abandoned-carts/{order}/mail', [AbandonedCartController::class, 'sendMail'])->name('abandoned-carts.mail');

            Route::resource('quotes', QuoteController::class)->except(['create', 'store']);
            Route::patch('quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.update-status');
            Route::get('quotes/{quote}/pdf', [QuoteController::class, 'generatePdf'])->name('quotes.pdf');
            Route::post('quotes/{quote}/send-pdf', [QuoteController::class, 'sendPdf'])->name('quotes.send-pdf');
            Route::post('quotes/{quote}/generate', [QuoteController::class, 'generate'])->name('quotes.generate');
            Route::post('quotes/{quote}/convert', [QuoteController::class, 'convert'])->name('quotes.convert');

            Route::resource('customers', CustomerController::class);

            Route::delete('products/bulk', [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
            Route::resource('products', ProductController::class)->except(['show']);
            Route::resource('categories', CategoryController::class)->except(['show']);
            Route::resource('extras', ExtraController::class)->except(['show']);
            Route::get('menu-pages', [MenuPageController::class, 'edit'])->name('menu-pages.edit');
            Route::put('menu-pages', [MenuPageController::class, 'update'])->name('menu-pages.update');
            Route::resource('packages', PackageController::class)->except(['show']);
            Route::resource('events', EventTypeController::class)->except(['show'])->parameters(['events' => 'event']);

            Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
            Route::get('reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
            Route::put('reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
            Route::post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
            Route::post('reviews/{review}/feature', [ReviewController::class, 'feature'])->name('reviews.feature');
            Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

            Route::resource('galleries', GalleryController::class)->except(['show']);
            Route::post('galleries/{gallery}/images', [GalleryController::class, 'storeImage'])->name('galleries.images.store');
            Route::delete('galleries/{gallery}/images/{image}', [GalleryController::class, 'destroyImage'])->name('galleries.images.destroy');

            Route::get('blog-posts/preview', [BlogPostController::class, 'previewEntry'])->name('blog-posts.preview-entry');
            Route::match(['post', 'put'], 'blog-posts/preview', [BlogPostController::class, 'preview'])->name('blog-posts.preview');
            Route::get('blog-posts/preview/{token}', [BlogPostController::class, 'previewLive'])->name('blog-posts.preview-live');
            Route::get('blog-posts/{blog_post}/preview', [BlogPostController::class, 'previewSaved'])->name('blog-posts.preview-saved');
            Route::resource('blog-posts', BlogPostController::class)->except(['show']);
            Route::resource('blog-categories', BlogCategoryController::class)->except(['show']);

            Route::resource('pages', PageController::class)->except(['show']);
            Route::post('pages/{page}/sections/reorder', [PageController::class, 'reorderSections'])->name('pages.sections.reorder');

            Route::resource('faqs', FaqController::class)->except(['show']);
            Route::resource('delivery-zones', DeliveryZoneController::class)->except(['show']);
            Route::resource('cities', CityController::class)->except(['show']);
            Route::resource('coupons', CouponController::class)->except(['show']);

            Route::get('media', [MediaController::class, 'index'])->name('media.index');
            Route::post('media', [MediaController::class, 'store'])
                ->middleware('throttle:20,1')
                ->name('media.store');
            Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

            Route::get('homepage', [HomepageSettingController::class, 'edit'])->name('homepage.edit');
            Route::put('homepage', [HomepageSettingController::class, 'update'])->name('homepage.update');

            Route::get('analytics', [AnalyticsAdminController::class, 'edit'])->name('analytics.edit');
            Route::post('analytics', [AnalyticsAdminController::class, 'update'])->name('analytics.update');

            Route::get('settings/{group?}', [SettingController::class, 'index'])->name('settings.group');
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
            Route::post('settings/telegram-test', [SettingController::class, 'testTelegram'])->name('settings.telegram-test');
            Route::post('settings/mail-test', [SettingController::class, 'testMail'])->name('settings.mail-test');
            Route::post('settings/ai-test', [SettingController::class, 'testAi'])->name('settings.ai-test');
            Route::put('settings/maintenance', [SettingController::class, 'toggleMaintenance'])->name('settings.maintenance');
            Route::put('settings/maintenance/text', [SettingController::class, 'updateMaintenance'])->name('settings.maintenance.text');

            Route::get('payments', [PaymentSettingController::class, 'index'])->name('payments.index');
            Route::put('payments', [PaymentSettingController::class, 'update'])->name('payments.update');

            Route::get('seo', [SeoController::class, 'index'])->name('seo.index');
            Route::put('seo/defaults', [SeoController::class, 'updateDefaults'])->name('seo.defaults');
            Route::get('seo/edit', [SeoController::class, 'edit'])->name('seo.edit');
            Route::put('seo', [SeoController::class, 'update'])->name('seo.update');

            Route::get('email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
            Route::get('email-templates/{emailTemplate}/edit', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
            Route::put('email-templates/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('email-templates.update');

            Route::resource('users', UserController::class)->except(['show']);
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');

            Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

            Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
            Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
            Route::post('contact-messages/{contactMessage}/create-quote', [ContactMessageController::class, 'createQuote'])->name('contact-messages.create-quote');
            Route::post('contact-messages/{contactMessage}/generate-quote', [ContactMessageController::class, 'generateQuote'])->name('contact-messages.generate-quote');
            Route::post('contact-messages/{contactMessage}/send-pdf', [ContactMessageController::class, 'sendPdf'])->name('contact-messages.send-pdf');
            Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

            Route::get('newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
            Route::get('newsletter/export', [NewsletterController::class, 'export'])->name('newsletter.export');
            Route::delete('newsletter/{newsletter}', [NewsletterController::class, 'destroy'])->name('newsletter.destroy');

            Route::get('business-hours', [BusinessHourController::class, 'index'])->name('business-hours.index');
            Route::put('business-hours', [BusinessHourController::class, 'update'])->name('business-hours.update');

            Route::get('blocked-dates', [BlockedDateController::class, 'index'])->name('blocked-dates.index');
            Route::post('blocked-dates', [BlockedDateController::class, 'store'])->name('blocked-dates.store');
            Route::delete('blocked-dates/{blockedDate}', [BlockedDateController::class, 'destroy'])->name('blocked-dates.destroy');

            Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
            Route::get('invoices/manual', [InvoiceController::class, 'create'])->name('invoices.manual');
            Route::post('invoices/manual', [InvoiceController::class, 'storeManual'])->name('invoices.store-manual');
            Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
            Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
            Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
            Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');
            Route::post('invoices/{invoice}/send', [InvoiceController::class, 'sendPdf'])->name('invoices.send');
        });
    });
});
