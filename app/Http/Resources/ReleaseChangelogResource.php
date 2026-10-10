<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReleaseChangelogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'title' => $this->title,
            'description' => $this->description,
            'position' => $this->position,
            'createdBy' => $this->when($this->createdBy, fn () => [
                'id' => $this->createdBy->id,
                'username' => $this->createdBy->username,
                'name' => $this->createdBy->name,
            ]),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
