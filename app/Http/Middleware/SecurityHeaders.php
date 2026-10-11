<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(self)'
        );
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        if (app()->environment('production') && $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    protected function contentSecurityPolicy(): string
    {
        $script = [
            "'self'",
            "'unsafe-inline'",
            // Alpine evaluates x-data and @click with new Function().
            "'unsafe-eval'",
            'https://js.stripe.com',
            'https://www.paypal.com',
            'https://www.sandbox.paypal.com',
            'https://www.googletagmanager.com',
            'https://connect.facebook.net',
        ];

        $connect = [
            "'self'",
            'https://api.stripe.com',
            'https://api.paypal.com',
            'https://api-m.paypal.com',
            'https://api-m.sandbox.paypal.com',
            'https://www.google-analytics.com',
            'https://region1.google-analytics.com',
            'https://www.googletagmanager.com',
            'https://maps.googleapis.com',
        ];

        $style = [
            "'self'",
            "'unsafe-inline'",
            'https://fonts.googleapis.com',
        ];

        if (app()->environment('local')) {
            $vite = ['http://127.0.0.1:5173', 'http://localhost:5173'];
            $script = array_merge($script, $vite);
            $connect = array_merge($connect, $vite, [
                'ws://127.0.0.1:5173',
                'ws://localhost:5173',
            ]);
            $style = array_merge($style, $vite);
        }

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self' https://checkout.stripe.com https://billing.stripe.com https://www.paypal.com https://www.sandbox.paypal.com",
            "frame-ancestors 'self'",
            "object-src 'none'",
            'script-src '.implode(' ', $script),
            'style-src '.implode(' ', $style),
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob: https:",
            'connect-src '.implode(' ', $connect),
            "frame-src 'self' https://js.stripe.com https://hooks.stripe.com https://www.paypal.com https://www.sandbox.paypal.com https://www.googletagmanager.com",
        ];

        return implode('; ', $directives);
    }
}
