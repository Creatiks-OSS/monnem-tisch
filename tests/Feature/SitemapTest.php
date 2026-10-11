<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_returns_ok(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $body = $response->getContent();
        $this->assertStringContainsString('xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', $body);
        $this->assertStringNotContainsString('www.w3.org/schemas/sitemap', $body);
        $this->assertStringContainsString('<urlset', $body);
        $this->assertStringContainsString(url('/'), $body);
        $this->assertStringContainsString(url('/impressum'), $response->getContent());
        $this->assertStringContainsString(url('/allergene'), $response->getContent());
        $this->assertStringContainsString(url('/blog'), $response->getContent());
        $this->assertStringContainsString(url('/buffets'), $response->getContent());
        $this->assertStringContainsString(url('/vegetarisch'), $response->getContent());
        $this->assertStringContainsString(url('/vegan'), $response->getContent());
    }

    public function test_llms_txt_returns_markdown_overview(): void
    {
        $response = $this->get('/llms.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $body = $response->getContent();
        $this->assertStringContainsString('# Monnemtisch Catering', $body);
        $this->assertStringContainsString(url('/sitemap.xml'), $body);
        $this->assertStringContainsString(url('/vegetarisch'), $body);
        $this->assertStringContainsString(url('/vegan'), $body);
        $this->assertStringContainsString('vegetarische und vegane Menüs', $body);
    }

    public function test_llms_full_txt_lists_urls(): void
    {
        $response = $this->get('/llms-full.txt');

        $response->assertOk();
        $this->assertStringContainsString(url('/'), $response->getContent());
        $this->assertStringContainsString(url('/vegetarisch'), $response->getContent());
        $this->assertStringContainsString(url('/vegan'), $response->getContent());
    }

    public function test_robots_txt_points_to_sitemap_and_llms(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $this->assertStringContainsString('Sitemap: '.url('/sitemap.xml'), $response->getContent());
        $this->assertStringContainsString('LLMs-Txt: '.url('/llms.txt'), $response->getContent());
    }
}
