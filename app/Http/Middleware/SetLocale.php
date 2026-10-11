<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin') || $request->is('admin/*')) {
            app()->setLocale('tr');
        } else {
            app()->setLocale(config('app.locale', 'de') ?: 'de');
        }

        return $next($request);
    }
}
