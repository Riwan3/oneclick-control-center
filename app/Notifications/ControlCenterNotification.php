<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * One generic, parameterized database notification for every Control
 * Center event type, rather than a class per type — the type itself is
 * just data (see App\Services\NotificationService::TYPES). Stored via
 * Laravel's native "database" channel, no custom schema.
 */
class ControlCenterNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $type,
        private readonly string $title,
        private readonly string $message,
        private readonly string $severity = 'info',
        private readonly ?string $url = null,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'severity' => $this->severity,
            'url' => $this->url,
        ];
    }

    /**
     * Overrides the stored `type` column — without this, Laravel would
     * store the notification's own class name there instead of our
     * semantic type (e.g. 'user_created').
     */
    public function databaseType(object $notifiable): string
    {
        return $this->type;
    }
}
