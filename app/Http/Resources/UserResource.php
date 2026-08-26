<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $username
 * @property string $name
 * @property string $email
 * @property string $role
 * @property string $status
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Single reusable source of truth for access resolution — see
        // App\Models\User::effectiveSiteAccess()/effectiveModuleAccess().
        // Not re-derived here.
        $siteAccess = $this->effectiveSiteAccess();
        $moduleAccess = $this->effectiveModuleAccess();

        $siteCount = count($siteAccess['sites']);
        $moduleCount = count($moduleAccess['modules']);

        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            // Password is never included — hidden on the model, and not
            // referenced here either.

            // Compact display strings for the user list table.
            'siteAccessSummary' => $siteAccess['mode'] === 'all'
                ? 'All Sites'
                : $siteCount.' '.($siteCount === 1 ? 'site' : 'sites'),
            'moduleAccessSummary' => $moduleAccess['mode'] === 'all'
                ? 'All Modules'
                : ($moduleCount === 0 ? 'No modules' : $moduleCount.' '.($moduleCount === 1 ? 'module' : 'modules')),

            // Structured data the edit form needs to pre-populate its
            // site/module checkboxes. For 'all' mode the id list is not
            // needed by the UI (it just shows the fixed "All Sites" /
            // "All Modules" state), so it is omitted rather than sending
            // every catalogue id on every response.
            'siteAccess' => [
                'mode' => $siteAccess['mode'],
                'siteIds' => $siteAccess['mode'] === 'selected'
                    ? collect($siteAccess['sites'])->pluck('id')->all()
                    : [],
                // Codes (not just ids) so any authenticated user's own
                // effective access — not just what the admin picker
                // needs — can be matched against site-identity keyed
                // frontend data without a second super_admin-gated
                // catalogue call. See resources/js/composables/
                // useEffectiveAccess.js.
                'siteCodes' => $siteAccess['mode'] === 'selected'
                    ? collect($siteAccess['sites'])->pluck('code')->all()
                    : [],
            ],
            'moduleAccess' => [
                'mode' => $moduleAccess['mode'],
                'moduleIds' => $moduleAccess['mode'] === 'selected'
                    ? collect($moduleAccess['modules'])->pluck('id')->all()
                    : [],
                'moduleKeys' => $moduleAccess['mode'] === 'selected'
                    ? collect($moduleAccess['modules'])->pluck('key')->all()
                    : [],
            ],
        ];
    }
}
