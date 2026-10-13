<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin', 'site_access_scope' => 'all', 'module_access_scope' => 'all']);
    }

    private function viewer(): User
    {
        return User::factory()->create(['role' => 'viewer', 'site_access_scope' => 'selected', 'module_access_scope' => 'selected']);
    }

    public function test_super_admin_creates_updates_and_deletes_a_site_with_audit(): void
    {
        $admin = $this->admin();

        $id = $this->actingAs($admin)->postJson('/api/sites', ['code' => 'new1', 'name' => 'PT New', 'location' => 'Aceh'])
            ->assertCreated()->assertJsonPath('data.code', 'NEW1')->json('data.id');

        $this->actingAs($admin)->patchJson("/api/sites/{$id}", ['name' => 'PT New 2', 'location' => 'Riau', 'base_url' => 'https://new.test/'])
            ->assertNoContent();
        $site = Site::find($id);
        $this->assertSame('PT New 2', $site->name);
        $this->assertSame('https://new.test', $site->base_url);

        $this->actingAs($admin)->deleteJson("/api/sites/{$id}")->assertNoContent();
        $this->assertNull(Site::find($id));

        foreach (['site_created', 'site_updated', 'site_deleted'] as $action) {
            $this->assertTrue(AuditLog::where('action', $action)->exists(), $action);
        }
    }

    public function test_duplicate_or_invalid_site_code_is_rejected(): void
    {
        $admin = $this->admin();
        Site::create(['code' => 'DUP', 'name' => 'PT Dup']);

        $this->actingAs($admin)->postJson('/api/sites', ['code' => 'dup', 'name' => 'X'])->assertUnprocessable();
        $this->actingAs($admin)->postJson('/api/sites', ['code' => 'bad code!', 'name' => 'X'])->assertUnprocessable();
    }

    public function test_issue_token_returns_it_once_stores_it_encrypted_and_never_audits_it(): void
    {
        $admin = $this->admin();
        $site = Site::create(['code' => 'TKN', 'name' => 'PT Token']);

        $response = $this->actingAs($admin)->postJson("/api/sites/{$site->id}/token")->assertOk();
        $token = $response->json('data.token');

        $this->assertSame(64, strlen($token));
        $response->assertJsonPath('data.env.CONTROL_CENTER_SITE_CODE', 'TKN')
            ->assertJsonPath('data.env.CONTROL_CENTER_TOKEN', $token);

        $site->refresh();
        $this->assertSame($token, $site->api_token);
        $this->assertNotSame($token, $site->getRawOriginal('api_token'));

        $this->assertStringNotContainsString($token, json_encode(AuditLog::all()->toArray()));
        $this->assertStringNotContainsString($token, $this->actingAs($admin)->getJson('/api/site-connections')->getContent());
    }

    public function test_rotating_the_token_invalidates_the_previous_one(): void
    {
        $admin = $this->admin();
        $site = Site::create(['code' => 'ROT', 'name' => 'PT Rotate']);

        $old = $this->actingAs($admin)->postJson("/api/sites/{$site->id}/token")->json('data.token');
        $new = $this->actingAs($admin)->postJson("/api/sites/{$site->id}/token")->json('data.token');
        $this->assertNotSame($old, $new);

        $payload = ['status' => ['status' => 'online']];
        $this->postJson('/api/cc/report', $payload, ['Authorization' => "Bearer {$old}", 'X-Site-Code' => 'ROT'])->assertUnauthorized();
        $this->postJson('/api/cc/report', $payload, ['Authorization' => "Bearer {$new}", 'X-Site-Code' => 'ROT'])->assertOk();
    }

    public function test_non_super_admin_cannot_manage_sites(): void
    {
        $viewer = $this->viewer();
        $site = Site::create(['code' => 'NO', 'name' => 'PT No']);

        $this->actingAs($viewer)->postJson('/api/sites', ['code' => 'X', 'name' => 'X'])->assertForbidden();
        $this->actingAs($viewer)->patchJson("/api/sites/{$site->id}", ['name' => 'X'])->assertForbidden();
        $this->actingAs($viewer)->deleteJson("/api/sites/{$site->id}")->assertForbidden();
        $this->actingAs($viewer)->postJson("/api/sites/{$site->id}/token")->assertForbidden();
    }

    public function test_connections_list_reports_mode_and_only_admin_sees_base_url(): void
    {
        $pull = Site::create(['code' => 'PUL', 'name' => 'A Pull']);
        $pull->forceFill(['base_url' => 'https://pull.test', 'api_token' => 't'])->save();
        $push = Site::create(['code' => 'PSH', 'name' => 'B Push']);
        $push->forceFill(['api_token' => 't'])->save();
        Site::create(['code' => 'NON', 'name' => 'C None']);

        $viewer = $this->viewer();
        $viewer->sites()->attach([$pull->id]);

        $rows = collect($this->actingAs($this->admin())->getJson('/api/site-connections')->json('data'))->keyBy('code');
        $this->assertSame('pull', $rows['PUL']['mode']);
        $this->assertSame('push', $rows['PSH']['mode']);
        $this->assertSame('none', $rows['NON']['mode']);
        $this->assertSame('https://pull.test', $rows['PUL']['base_url']);

        $scoped = $this->actingAs($viewer)->getJson('/api/site-connections')->json('data.0');
        $this->assertArrayNotHasKey('base_url', $scoped);
    }
}
