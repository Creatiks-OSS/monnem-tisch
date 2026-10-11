<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class UseRequestRootUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        $root = $request->root();
        if ($root !== '') {
            URL::forceRootUrl($root);
        }

        return $next($request);
    }
}
