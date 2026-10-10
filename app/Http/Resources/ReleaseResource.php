<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * `changelogs` (the full array) is only included when eager-loaded (see
 * ReleaseController::show() vs. index()) — the list endpoint stays
 * light, using `withCount('changelogs')` for just the count instead.
 */
class ReleaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'title' => $this->title,
            'status' => $this->status,
            'releaseDate' => $this->release_date?->toDateString(),
            'description' => $this->description,
            'changelogCount' => $this->when(
                $this->changelogs_count !== null || $this->relationLoaded('changelogs'),
                fn () => $this->changelogs_count ?? $this->changelogs->count()
            ),
            'createdBy' => $this->when($this->createdBy, fn () => [
                'id' => $this->createdBy->id,
                'username' => $this->createdBy->username,
                'name' => $this->createdBy->name,
            ]),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'changelogs' => ReleaseChangelogResource::collection($this->whenLoaded('changelogs')),
        ];
    }
}
