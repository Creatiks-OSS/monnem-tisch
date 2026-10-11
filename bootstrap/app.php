<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->prependToGroup('web', [
            \App\Http\Middleware\ForceHttps::class,
            \App\Http\Middleware\UseRequestRootUrl::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SetLocale::class,
            SecurityHeaders::class,
            \App\Http\Middleware\ComingSoon::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe',
            'webhooks/paypal',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'kitchen' => \App\Http\Middleware\EnsureKitchenAuthenticated::class,
        ]);

        $middleware->redirectGuestsTo(fn ($request) => route('admin.login'));
        $middleware->redirectUsersTo(function ($request) {
            $user = $request->user();
            if ($user && method_exists($user, 'hasRole') && $user->hasRole('Kitchen')) {
                return route('admin.orders.index');
            }

            return route('admin.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->booted(function (Application $app): void {
        $app->setLocale(config('app.locale', 'de'));
    })
    ->create();
