<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepageSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_homepage_copy_and_image(): void
    {
        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create([
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.homepage.edit'))
            ->assertOk()
            ->assertSee('Anasayfa ayarları')
            ->assertSee('Catering in Mannheim für');

        $this->actingAs($admin)
            ->put(route('admin.homepage.update'), [
                'text' => [
                    'hero_line_1' => 'Catering in Mannheim',
                    'hero_line_2' => 'für jedes Fest',
                ],
                'images' => [
                    'hero_image' => UploadedFile::fake()->image('hero.jpg', 1200, 800),
                ],
            ])
            ->assertRedirect(route('admin.homepage.edit'));

        $this->assertSame('Catering in Mannheim', Setting::get('hero_line_1', null, 'homepage'));
        $this->assertSame('für jedes Fest', Setting::get('hero_line_2', null, 'homepage'));
        $this->assertNotSame('', (string) Setting::get('hero_image', '', 'homepage'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Catering in Mannheim', false)
            ->assertSee('für jedes Fest', false);
    }
}
