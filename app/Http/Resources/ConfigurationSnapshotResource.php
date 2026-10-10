<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * `modules`/`menus` are only included when eager-loaded (see
 * ConfigurationSnapshotController::show() vs. index()) — the list
 * endpoint stays light; the detail endpoint returns the full frozen
 * state.
 */
class ConfigurationSnapshotResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'snapshotNumber' => $this->snapshot_number,
            'site' => [
                'id' => $this->site->id,
                'code' => $this->site->code,
                'name' => $this->site->name,
            ],
            'sourceBaselineVersion' => $this->source_baseline_version,
            'moduleCount' => $this->module_count,
            'menuCount' => $this->menu_count,
            'status' => $this->status,
            'createdBy' => $this->when($this->createdBy, fn () => [
                'id' => $this->createdBy->id,
                'username' => $this->createdBy->username,
                'name' => $this->createdBy->name,
            ]),
            'createdAt' => $this->created_at?->toIso8601String(),
            'modules' => $this->whenLoaded('modules', fn () => $this->modules->map(fn ($module) => [
                'moduleKey' => $module->module_key,
                'enabled' => $module->enabled,
            ])->values()),
            'menus' => $this->whenLoaded('menus', fn () => $this->menus->map(fn ($menu) => [
                'moduleKey' => $menu->module_key,
                'menuKey' => $menu->menu_key,
                'enabled' => $menu->enabled,
            ])->values()),
        ];
    }
}
