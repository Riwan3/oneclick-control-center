<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'site_access_scope' => 'all',
            'module_access_scope' => 'all',
        ]);
    }

    private function site(string $code, array $connection = []): Site
    {
        $site = Site::create(['code' => $code, 'name' => "PT {$code}"]);
        if ($connection) {
            $site->forceFill($connection)->save();
        }

        return $site;
    }

    public function test_it_describes_the_connection_rules_and_the_live_state_of_the_sites(): void
    {
        $this->site('PUSH', ['api_token' => 'secret-push-token', 'status' => 'online', 'last_checked_at' => '2026-10-14 08:00:00']);
        $this->site('PULL', ['api_token' => 'secret-pull-token', 'base_url' => 'https://pull.example.test', 'status' => 'offline', 'last_checked_at' => '2026-10-14 09:30:00']);
        $this->site('NEW', ['api_token' => 'secret-new-token', 'status' => 'unknown']);
        $this->site('NONE');

        $response = $this->actingAs($this->user('super_admin'))->getJson('/api/integration-settings');

        $response->assertOk();
        $response->assertJsonPath('data.endpoint.report_path', '/api/cc/report');
        $response->assertJsonPath('data.endpoint.method', 'POST');
        $response->assertJsonPath('data.endpoint.rate_limit_per_minute', 30);
        $response->assertJsonPath('data.endpoint.max_payload_kb', 512);
        $response->assertJsonPath('data.authentication.site_header', 'X-Site-Code');
        $response->assertJsonPath('data.timing.pull_timeout_seconds', 10);
        $response->assertJsonPath('data.timing.offline_after_minutes', 15);

        $response->assertJsonPath('data.sites.registered', 4);
        $response->assertJsonPath('data.sites.connected', 3);
        $response->assertJsonPath('data.sites.push', 2);
        $response->assertJsonPath('data.sites.pull', 1);
        $response->assertJsonPath('data.sites.online', 1);
        $response->assertJsonPath('data.sites.offline', 1);
        $response->assertJsonPath('data.sites.awaiting_first_report', 1);
        $this->assertStringStartsWith('2026-10-14T09:30:00', $response->json('data.last_report_at'));

        $this->assertStringNotContainsString('secret-', $response->getContent(), 'A token is never exposed.');
    }

    public function test_there_is_no_last_report_when_nothing_has_reported(): void
    {
        $this->actingAs($this->user('super_admin'))->getJson('/api/integration-settings')
            ->assertOk()
            ->assertJsonPath('data.sites.registered', 0)
            ->assertJsonPath('data.last_report_at', null);
    }

    public function test_only_a_super_admin_can_read_it(): void
    {
        $this->getJson('/api/integration-settings')->assertUnauthorized();

        foreach (['management', 'view'] as $role) {
            $this->actingAs($this->user($role))->getJson('/api/integration-settings')->assertForbidden();
        }
    }
}
