<?php

namespace Tests\Unit;

use App\Support\BaselineKeys;
use PHPUnit\Framework\TestCase;

/**
 * The baseline is locked, and it lives in two places: the frontend registry
 * (content) and BaselineKeys (the backend's key mirror, used to validate and
 * to compute snapshots). This keeps them from drifting apart.
 */
class BaselineKeysTest extends TestCase
{
    private function read(string $path): string
    {
        return file_get_contents(dirname(__DIR__, 2).'/resources/js/data/'.$path);
    }

    /** @return array{modules: string[], menus: string[]} */
    private function frontendBaseline(): array
    {
        $registry = $this->read('mock/baselineRegistry.js');
        $power = $this->read('onePowerBaseline.js');

        preg_match_all("/\{ module_key: '([^']+)'/", $registry, $m);
        $modules = $m[1];

        $menus = [];
        preg_match_all("/menu_key: '([^']+)', module_key: '([^']+)', label: '(?:[^'\\\\]|\\\\.)*', parent_key: (?:null|'([^']+)')/", $registry, $rows, PREG_SET_ORDER);
        foreach ($rows as $r) {
            $menus[] = $r[2].'|'.$r[1].'|'.($r[3] ?? '');
        }
        preg_match_all("/id: '([^']+)', label: '(?:[^'\\\\]|\\\\.)*', parentId: (?:null|'([^']+)')/", $power, $rows, PREG_SET_ORDER);
        foreach ($rows as $r) {
            $menus[] = 'one-power|'.$r[1].'|'.($r[2] ?? '');
        }

        sort($modules);
        sort($menus);

        return ['modules' => $modules, 'menus' => $menus];
    }

    public function test_backend_key_mirror_matches_the_frontend_registry(): void
    {
        $frontend = $this->frontendBaseline();

        $modules = BaselineKeys::MODULE_KEYS;
        sort($modules);

        $menus = array_map(fn (array $m) => $m['module_key'].'|'.$m['menu_key'].'|'.($m['parent_key'] ?? ''), BaselineKeys::MENUS);
        sort($menus);

        $this->assertNotEmpty($frontend['menus'], 'The frontend registry could not be parsed.');
        $this->assertSame($frontend['modules'], $modules, 'Module keys differ between baselineRegistry.js and BaselineKeys.');
        $this->assertSame($frontend['menus'], $menus, 'Menu keys/hierarchy differ between the frontend registry and BaselineKeys.');
    }

    public function test_menu_keys_are_unique_within_a_module_and_parents_exist(): void
    {
        $seen = [];
        foreach (BaselineKeys::MENUS as $menu) {
            $id = $menu['module_key'].'|'.$menu['menu_key'];
            $this->assertArrayNotHasKey($id, $seen, "Duplicate baseline menu $id");
            $seen[$id] = true;
        }
        foreach (BaselineKeys::MENUS as $menu) {
            if ($menu['parent_key'] !== null) {
                $this->assertArrayHasKey($menu['module_key'].'|'.$menu['parent_key'], $seen, "Orphan parent for {$menu['menu_key']}");
            }
        }
    }
}
