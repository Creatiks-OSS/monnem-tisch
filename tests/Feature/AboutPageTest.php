<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_tells_the_company_story(): void
    {
        $html = $this->get(route('about'))->assertOk()->getContent();

        $this->assertStringContainsString('Über uns', $html);
        $this->assertStringContainsString('Anatolian Gourmet GmbH', $html);
        $this->assertStringContainsString('Monnemtisch Catering', $html);
        $this->assertStringContainsString('H1, 10', $html);
        $this->assertStringContainsString('Mannheim', $html);
        $this->assertStringContainsString('Buffets', $html);
        $this->assertStringContainsString(route('legal.allergene'), $html);
    }
}
