<?php

namespace App\Support;

use App\Models\Module;
use App\Models\Site;
use App\Models\User;

/**
 * Reusable server-side authorization primitive for site/module-scoped
 * data. Built on top of User::effectiveSiteAccess()/effectiveModuleAccess()
 * — it never re-derives the all-vs-selected rule itself, only answers
 * "is this specific site/module inside that already-resolved set?".
 *
 * Intended for any future controller that returns site- or module-scoped
 * operational data (e.g. api/v1/* endpoints): call userCanAccessSite()/
 * userCanAccessModule() before returning data for a given site/module id
 * or code, instead of hand-rolling a new check per controller.
 */
class AccessScope
{
    /**
     * @param  Site|int|string  $site  a Site model, its numeric id, or its code (case-insensitive)
     */
    public static function userCanAccessSite(User $user, Site|int|string $site): bool
    {
        $access = $user->effectiveSiteAccess();

        if ($access['mode'] === 'all') {
            return true;
        }

        [$id, $code] = self::identify($site, 'code');

        foreach ($access['sites'] as $allowed) {
            if (($id !== null && $allowed['id'] === $id) || ($code !== null && strcasecmp($allowed['code'], $code) === 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Module|int|string  $module  a Module model, its numeric id, or its key (case-insensitive)
     */
    public static function userCanAccessModule(User $user, Module|int|string $module): bool
    {
        $access = $user->effectiveModuleAccess();

        if ($access['mode'] === 'all') {
            return true;
        }

        [$id, $key] = self::identify($module, 'key');

        foreach ($access['modules'] as $allowed) {
            if (($id !== null && $allowed['id'] === $id) || ($key !== null && strcasecmp($allowed['key'], $key) === 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{0: int|null, 1: string|null}
     */
    private static function identify(Site|Module|int|string $subject, string $codeAttribute): array
    {
        if ($subject instanceof Site || $subject instanceof Module) {
            return [$subject->id, $subject->{$codeAttribute}];
        }

        if (is_int($subject) || ctype_digit((string) $subject)) {
            return [(int) $subject, null];
        }

        return [null, (string) $subject];
    }
}
