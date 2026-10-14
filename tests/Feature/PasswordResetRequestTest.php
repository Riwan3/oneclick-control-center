<?php

namespace Tests\Feature;

use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetRequestTest extends TestCase
{
    use RefreshDatabase;

    private function payload(string $username): array
    {
        return ['username' => $username, 'full_name' => 'Budi', 'position' => 'Staff IT', 'phone' => '0812-3456-7890'];
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin', 'site_access_scope' => 'all', 'module_access_scope' => 'all']);
    }

    public function test_anyone_can_submit_and_unknown_usernames_get_the_same_answer(): void
    {
        $user = User::factory()->create(['username' => '12345']);

        $this->postJson('/api/password-requests', $this->payload('12345'))->assertCreated();
        $this->postJson('/api/password-requests', $this->payload('nobody'))->assertCreated();

        $this->assertSame($user->id, PasswordResetRequest::where('username', '12345')->value('user_id'));
        $this->assertNull(PasswordResetRequest::where('username', 'nobody')->value('user_id'));
    }

    public function test_duplicate_open_request_is_not_stored_twice(): void
    {
        $this->postJson('/api/password-requests', $this->payload('12345'))->assertCreated();
        $this->postJson('/api/password-requests', $this->payload('12345'))->assertCreated();

        $this->assertSame(1, PasswordResetRequest::count());
    }

    public function test_reset_requires_verification_then_sets_password_to_username(): void
    {
        $user = User::factory()->create(['username' => '12345', 'password' => 'something-else']);
        $this->postJson('/api/password-requests', $this->payload('12345'));
        $request = PasswordResetRequest::first();
        $admin = $this->superAdmin();

        $this->actingAs($admin)->postJson("/api/password-requests/{$request->id}/reset")->assertUnprocessable();

        $this->actingAs($admin)->postJson("/api/password-requests/{$request->id}/verify")->assertOk();
        $this->actingAs($admin)->postJson("/api/password-requests/{$request->id}/reset")->assertOk();

        $this->assertTrue(Hash::check('12345', $user->fresh()->password));
        $this->assertSame('reset', $request->fresh()->status);
    }

    public function test_only_super_admin_can_manage_requests(): void
    {
        $viewer = User::factory()->create(['role' => 'view', 'site_access_scope' => 'selected', 'module_access_scope' => 'selected']);

        $this->actingAs($viewer)->getJson('/api/password-requests')->assertForbidden();
    }
}
