<?php

namespace Tests\Feature;

use App\Models\Release;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteConfigSyncTest extends TestCase
{
    use RefreshDatabase;

    private function site(): Site
    {
        $site = Site::create(['code' => 'CFG', 'name' => 'PT Config']);
        $site->forceFill(['api_token' => 'cfg-token'])->save();

        return $site;
    }

    private function report(): array
    {
        return $this->postJson(
            '/api/cc/report',
            ['status' => ['status' => 'online', 'config_sync' => ['apply' => true, 'synced_at' => '2026-10-11T10:00:00+00:00']]],
            ['Authorization' => 'Bearer cfg-token', 'X-Site-Code' => 'CFG']
        )->assertOk()->json('config');
    }

    public function test_report_reply_carries_disabled_modules_menus_and_target_version(): void
    {
        $site = $this->site();
        $site->moduleConfigs()->create(['module_key' => 'one-core-value', 'enabled' => false]);
        $site->moduleConfigs()->create(['module_key' => 'one-gmp', 'enabled' => false]);
        $site->menuConfigs()->create(['module_key' => 'one-power', 'menu_key' => 'roster-personal', 'menu_label' => 'Roster Personal', 'enabled' => false]);
        $site->menuConfigs()->create(['module_key' => 'one-procedur', 'menu_key' => 'x-menu', 'menu_label' => 'Prosedur X', 'enabled' => false]);
        $site->menuConfigs()->create(['module_key' => 'one-power', 'menu_key' => 'legacy-menu', 'enabled' => false]);
        $site->menuConfigs()->create(['module_key' => 'one-power', 'menu_key' => 'still-on', 'menu_label' => 'Still On', 'enabled' => true]);
        Release::create(['version' => 'v2.4.1', 'title' => 'a', 'status' => 'released', 'release_date' => '2026-09-01']);
        Release::create(['version' => 'v2.10.0', 'title' => 'b', 'status' => 'released', 'release_date' => '2026-10-01']);
        Release::create(['version' => 'v3.0.0', 'title' => 'draft', 'status' => 'draft']);

        $config = $this->report();

        // Baseline keys are translated to the site's own module keys.
        $this->assertEqualsCanonicalizing(['one-core', 'one-gmp'], $config['disabled_modules']);
        $this->assertSame(['Roster Personal'], $config['disabled_menus']['one-power']);
        $this->assertSame(['Prosedur X'], $config['disabled_menus']['one-procedure']);
        // Overrides recorded before labels existed are reported, not guessed.
        $this->assertSame(1, $config['unmapped_menus']);
        // Numeric (not string) version comparison; drafts are ignored.
        $this->assertSame('v2.10.0', $config['target_version']);
    }

    public function test_nothing_disabled_means_an_empty_policy(): void
    {
        $this->site();

        $config = $this->report();

        $this->assertSame([], $config['disabled_modules']);
        $this->assertSame([], $config['disabled_menus']);
        $this->assertNull($config['target_version']);
    }

    public function test_site_reported_config_sync_is_stored_and_listed(): void
    {
        $this->site();
        $this->report();

        $admin = User::factory()->create(['role' => 'super_admin', 'site_access_scope' => 'all', 'module_access_scope' => 'all']);
        $row = collect($this->actingAs($admin)->getJson('/api/site-connections')->json('data'))->firstWhere('code', 'CFG');

        $this->assertTrue($row['config_sync']['apply']);
    }

    public function test_menu_toggle_stores_the_label_the_site_matches_on(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'site_access_scope' => 'all', 'module_access_scope' => 'all']);
        $site = $this->site();

        $this->actingAs($admin)->patchJson("/api/site-configurations/{$site->id}/menus", [
            'module_key' => 'one-power', 'menu_key' => 'roster-personal', 'menu_label' => 'Roster Personal', 'enabled' => false,
        ])->assertNoContent();

        $this->assertSame('Roster Personal', $site->menuConfigs()->first()->menu_label);
    }
}
