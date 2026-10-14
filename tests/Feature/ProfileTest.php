<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function viewer(): User
    {
        return User::factory()->create([
            'password' => 'old-password',
            'role' => 'view',
            'site_access_scope' => 'selected',
            'module_access_scope' => 'selected',
        ]);
    }

    public function test_user_can_change_own_name(): void
    {
        $this->actingAs($this->viewer())
            ->patchJson('/api/profile', ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = $this->viewer();

        $this->actingAs($user)
            ->putJson('/api/profile/password', [
                'current_password' => 'wrong',
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertUnprocessable();

        $this->actingAs($user)
            ->putJson('/api/profile/password', [
                'current_password' => 'old-password',
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('new-password-1', $user->fresh()->password));
    }

    public function test_avatar_can_be_uploaded_served_and_removed(): void
    {
        Storage::fake('local');
        $user = $this->viewer();

        $this->actingAs($user)
            ->postJson('/api/profile/avatar', ['avatar' => UploadedFile::fake()->image('me.png')])
            ->assertOk()
            ->assertJsonStructure(['data' => ['avatarUrl']]);

        $path = $user->fresh()->avatar_path;
        Storage::disk('local')->assertExists($path);

        $this->actingAs($user)->get("/api/profile/avatar/{$user->id}")->assertOk();

        $this->actingAs($user)->deleteJson('/api/profile/avatar')->assertOk();
        Storage::disk('local')->assertMissing($path);
    }

    public function test_non_image_avatar_is_rejected(): void
    {
        $this->actingAs($this->viewer())
            ->postJson('/api/profile/avatar', ['avatar' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertUnprocessable();
    }
}
