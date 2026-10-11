<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __construct(
        protected SettingService $settings
    ) {}

    public function __invoke(): Response
    {
        $custom = trim($this->settings->string('robots_txt', '', 'seo'));

        $sitemap = url('/sitemap.xml');
        $llms = url('/llms.txt');
        $appendix = implode("\n", [
            'Sitemap: '.$sitemap,
            'LLMs-Txt: '.$llms,
            '',
        ]);

        if ($custom !== '') {
            $content = rtrim($custom)."\n";
            if (! str_contains(mb_strtolower($content), 'sitemap:')) {
                $content .= "\n".$appendix;
            }
        } else {
            $content = implode("\n", [
                'User-agent: *',
                'Allow: /',
                'Disallow: /admin',
                'Disallow: /warenkorb',
                'Disallow: /checkout',
                'Disallow: /api/',
                'Disallow: /mutfak',
                '',
            ]).$appendix;
        }

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
