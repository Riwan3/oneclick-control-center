<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use App\Services\SiteConnectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SiteConnectionTest extends TestCase
{
    use RefreshDatabase;

    private function connectedSite(): Site
    {
        $site = Site::create(['code' => 'TST', 'name' => 'PT Test']);
        $site->forceFill(['base_url' => 'https://site.test', 'api_token' => 'secret-token'])->save();

        return $site;
    }

    public function test_successful_check_stores_telemetry_and_sends_bearer_token(): void
    {
        Http::fake([
            'site.test/api/cc/status' => Http::response(['data' => [
                'status' => 'online', 'version' => 'v1.2.3', 'environment' => 'production',
                'deployed_at' => '2026-10-01T08:00:00+00:00', 'users' => 714, 'modules' => ['one-power'],
                'health' => ['score' => 82, 'cpu_pct' => 10.0, 'memory_pct' => 18.0, 'disk_pct' => 45.0, 'db_latency_ms' => 0.4],
            ]]),
            'site.test/api/cc/activity' => Http::response(['data' => [
                'active_users_today' => 3, 'daily' => [['label' => 'Mon', 'logins' => 5, 'unique_users' => 3]], 'recent' => [['title' => 'Login berhasil']],
            ]]),
            'site.test/api/cc/menus' => Http::response(['data' => ['modules' => ['one-power' => ['version' => 'v1.2.3', 'menus' => [['id' => 'dashboard']]]]]]),
        ]);

        $site = app(SiteConnectionService::class)->check($this->connectedSite());

        $this->assertSame('online', $site->status);
        $this->assertSame('v1.2.3', $site->version);
        $this->assertSame('production', $site->environment);
        $this->assertSame(714, $site->users_count);
        $this->assertSame(82, $site->health['score']);
        $this->assertSame(3, $site->activity['active_users_today']);
        $this->assertSame('dashboard', $site->menus['one-power']['menus'][0]['id']);
        Http::assertSent(fn ($r) => $r->url() === 'https://site.test/api/cc/status'
            && $r->hasHeader('Authorization', 'Bearer secret-token'));
    }

    public function test_site_and_observatory_endpoints_expose_telemetry_within_access(): void
    {
        $site = $this->connectedSite();
        $site->forceFill([
            'status' => 'online', 'health' => ['score' => 82], 'activity' => ['active_users_today' => 3, 'daily' => [], 'recent' => [['title' => 'Login berhasil']]],
            'menus' => ['one-power' => ['version' => 'v1', 'menus' => [['id' => 'dashboard']]]],
        ])->save();
        $other = Site::create(['code' => 'OTH', 'name' => 'PT Other']);
        $viewer = User::factory()->create(['role' => 'viewer', 'site_access_scope' => 'selected', 'module_access_scope' => 'selected']);
        $viewer->sites()->attach($site->id);

        $this->actingAs($viewer)->getJson("/api/site-connections/{$site->id}")
            ->assertOk()->assertJsonPath('data.health.score', 82)->assertJsonPath('data.recent_activity.0.title', 'Login berhasil');
        $this->actingAs($viewer)->getJson("/api/site-connections/{$other->id}")->assertNotFound();

        $this->actingAs($viewer)->getJson('/api/module-observatory/one-power')
            ->assertOk()->assertJsonCount(1, 'data.sites')->assertJsonPath('data.sites.0.code', 'TST')
            ->assertJsonPath('data.sites.0.menus.0.id', 'dashboard');
    }

    public function test_unauthorized_or_unreachable_site_is_offline_without_leaking_token(): void
    {
        Http::fake(['site.test/*' => Http::response([], 401)]);
        $site = app(SiteConnectionService::class)->check($this->connectedSite());

        $this->assertSame('offline', $site->status);
        $this->assertSame('HTTP 401', $site->last_error);
    }

    public function test_token_is_encrypted_at_rest_and_never_serialized(): void
    {
        $site = $this->connectedSite();

        $this->assertNotSame('secret-token', $site->getRawOriginal('api_token'));
        $this->assertArrayNotHasKey('api_token', $site->toArray());
    }

    public function test_connections_are_filtered_by_site_access_and_token_is_not_exposed(): void
    {
        $granted = $this->connectedSite();
        Site::create(['code' => 'OTH', 'name' => 'PT Other']);
        $admin = User::factory()->create(['role' => 'super_admin', 'site_access_scope' => 'all', 'module_access_scope' => 'all']);
        $viewer = User::factory()->create(['role' => 'viewer', 'site_access_scope' => 'selected', 'module_access_scope' => 'selected']);
        $viewer->sites()->attach($granted->id);

        $this->getJson('/api/site-connections')->assertUnauthorized();

        $all = $this->actingAs($admin)->getJson('/api/site-connections')->assertOk();
        $all->assertJsonCount(2, 'data');
        $this->assertStringNotContainsString('secret-token', $all->getContent());

        $scoped = $this->actingAs($viewer)->getJson('/api/site-connections')->assertOk();
        $scoped->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', 'TST')->assertJsonPath('data.0.connected', true);
    }

    public function test_manual_check_is_super_admin_only(): void
    {
        $site = $this->connectedSite();
        $viewer = User::factory()->create(['role' => 'viewer', 'site_access_scope' => 'selected', 'module_access_scope' => 'selected']);

        $this->actingAs($viewer)->postJson("/api/site-connections/{$site->id}/check")->assertForbidden();
    }
}
