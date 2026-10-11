<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComingSoon
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        try {
            $enabled = app(SettingService::class)->bool('enabled', false, 'maintenance');
        } catch (\Throwable) {
            return $next($request);
        }

        if (! $enabled) {
            return $next($request);
        }

        $title = setting('title', 'Wir sind bald für Sie da', 'maintenance');
        $message = setting(
            'message',
            'Unsere Website befindet sich gerade im Aufbau. Monnemtisch Catering in Mannheim – bald wieder für Sie erreichbar.',
            'maintenance'
        );

        return response()
            ->view('frontend.coming-soon', [
                'pageTitle' => is_string($title) && $title !== '' ? $title : 'Wir sind bald für Sie da',
                'pageMessage' => is_string($message) && $message !== '' ? $message : 'Unsere Website befindet sich gerade im Aufbau.',
                'phone' => setting('phone', '', 'contact'),
                'email' => setting('email', 'info@monnemtisch.de', 'contact'),
            ], 503)
            ->header('Retry-After', '3600');
    }

    protected function shouldBypass(Request $request): bool
    {
        if ($request->is('admin', 'admin/*', 'mutfak', 'mutfak/*', 'up', 'webhooks/*', 'storage/*', 'media', 'media/*', 'livewire/*', 'branding/logo', 'sitemap.xml', 'robots.txt', 'llms.txt', 'llms-full.txt')) {
            return true;
        }

        if ($request->routeIs('admin.*', 'kitchen.*', 'webhooks.*')) {
            return true;
        }

        $user = $request->user();
        if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole([
            'Super Admin', 'Admin', 'Manager', 'Editor', 'Sales', 'Kitchen',
        ])) {
            return true;
        }

        return false;
    }
}
