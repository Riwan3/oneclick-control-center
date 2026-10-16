<?php

namespace App\Services;

use App\Models\Site;
use App\Support\BaselineKeys;

/**
 * Extensions: whole modules that a site reports but that are not part of the
 * Master Baseline (e.g. a site-specific "one-coal"). The baseline stays
 * locked and is never changed by them; an extension only exists for the
 * site that reports it, and is known only through that site's own report
 * (status.modules and menus.modules). They can be monitored and switched
 * off from the Control Center like a baseline module.
 *
 * Module keys are the ones the site uses. Baseline modules are compared in
 * the site's key space too (one-core-value is reported as one-core), see
 * SiteConfigPayload::MODULE_TO_SITE.
 */
class SiteExtensions
{
    // What a site may call a module / menu: it ends up as a database key and in the UI.
    private const MODULE_KEY_PATTERN = '/^[a-z0-9]+(-[a-z0-9]+)*$/';

    private const MODULE_KEY_MAX = 100;

    private const MENU_KEY_MAX = 150;

    /**
     * @return array<int, array{key: string, label: string, version: ?string, menus: array<int, array{id: string, label: string, parentId: ?string, order: int}>}>
     */
    public static function forSite(Site $site): array
    {
        $baselineSiteKeys = array_map(
            fn (string $key) => SiteConfigPayload::MODULE_TO_SITE[$key] ?? $key,
            BaselineKeys::moduleKeys()
        );

        $reported = array_unique([
            ...array_map('strval', (array) ($site->modules ?? [])),
            ...array_map('strval', array_keys((array) ($site->menus ?? []))),
        ]);

        $keys = array_values(array_filter(
            array_diff($reported, $baselineSiteKeys),
            fn (string $key) => strlen($key) <= self::MODULE_KEY_MAX && preg_match(self::MODULE_KEY_PATTERN, $key) === 1
        ));
        sort($keys);

        return array_map(fn (string $key) => self::describe($site, $key), $keys);
    }

    /** @return array{key: string, label: string, version: ?string, menus: array}|null */
    public static function find(Site $site, string $key): ?array
    {
        foreach (self::forSite($site) as $extension) {
            if ($extension['key'] === $key) {
                return $extension;
            }
        }

        return null;
    }

    /** A menu the extension reports that has no children (parents are derived from their children). */
    public static function hasLeafMenu(array $extension, string $menuKey): bool
    {
        $menus = collect($extension['menus']);

        return $menus->contains('id', $menuKey) && ! $menus->contains('parentId', $menuKey);
    }

    public static function label(string $key): string
    {
        return implode('-', array_map('ucfirst', explode('-', $key)));
    }

    private static function describe(Site $site, string $key): array
    {
        $manifest = (array) (($site->menus ?? [])[$key] ?? []);

        $menus = [];
        foreach (array_values((array) ($manifest['menus'] ?? [])) as $index => $menu) {
            if (! is_array($menu) || ! isset($menu['id'], $menu['label']) || strlen((string) $menu['id']) > self::MENU_KEY_MAX) {
                continue;
            }

            $menus[] = [
                'id' => (string) $menu['id'],
                'label' => (string) $menu['label'],
                'parentId' => isset($menu['parentId']) ? (string) $menu['parentId'] : null,
                'order' => (int) ($menu['order'] ?? $index + 1),
            ];
        }

        return [
            'key' => $key,
            'label' => self::label($key),
            'version' => isset($manifest['version']) ? (string) $manifest['version'] : null,
            'menus' => $menus,
        ];
    }
}
