<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->data['title'] ?? null,
            'message' => $this->data['message'] ?? null,
            'severity' => $this->data['severity'] ?? 'info',
            'url' => $this->data['url'] ?? null,
            'read_at' => optional($this->read_at)->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
