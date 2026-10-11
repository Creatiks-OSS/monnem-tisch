<?php

namespace Tests\Unit;

use Tests\TestCase;

class MailImageHelperTest extends TestCase
{
    public function test_mail_image_local_path_resolves_public_package_photo(): void
    {
        $url = asset('images/packages/package-fingerfood-deluxe.webp');
        $file = mail_image_local_path($url);

        $this->assertNotNull($file);
        $this->assertFileExists($file);
    }

    public function test_mail_image_jpeg_converts_webp_for_email_clients(): void
    {
        $file = mail_image_local_path(asset('images/packages/package-fingerfood-deluxe.webp'));
        $this->assertNotNull($file);

        $jpeg = mail_image_jpeg($file, 48);

        $this->assertNotNull($jpeg);
        $this->assertStringStartsWith("\xFF\xD8\xFF", $jpeg);
    }

    public function test_mail_public_url_rewrites_localhost_to_app_url(): void
    {
        config(['app.url' => 'https://monnemtisch.de']);

        $this->assertSame(
            'https://monnemtisch.de/images/packages/package-fingerfood-deluxe.webp',
            mail_public_url('http://127.0.0.1:8000/images/packages/package-fingerfood-deluxe.webp')
        );
    }
}
