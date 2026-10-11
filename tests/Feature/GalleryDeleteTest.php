<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_a_gallery_from_the_list(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $admin->assignRole('Super Admin');

        $gallery = Gallery::query()->create([
            'name' => 'Test Galerie',
            'slug' => 'test-galerie',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.galleries.index'))
            ->assertOk()
            ->assertSee('Sil')
            ->assertSee(route('admin.galleries.destroy', $gallery), false);

        $this->actingAs($admin)
            ->delete(route('admin.galleries.destroy', $gallery))
            ->assertRedirect(route('admin.galleries.index'));

        $this->assertSoftDeleted('galleries', ['id' => $gallery->id]);
    }
}
