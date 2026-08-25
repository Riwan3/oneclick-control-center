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
 * @property string $site_access_scope
 * @property int|null $sites_count
 * @property int|null $modules_count
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sitesCount = $this->sites_count ?? $this->sites()->count();
        $modulesCount = $this->modules_count ?? $this->modules()->count();

        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            // Password is never included — hidden on the model, and not
            // referenced here either.
            'siteAccessSummary' => $this->site_access_scope === 'all'
                ? 'All Sites'
                : $sitesCount.' '.($sitesCount === 1 ? 'site' : 'sites'),
            'moduleAccessSummary' => $modulesCount === 0
                ? 'No modules'
                : $modulesCount.' '.($modulesCount === 1 ? 'module' : 'modules'),
        ];
    }
}
