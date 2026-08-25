<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\ControlCenterNotification;

/**
 * Single entry point for creating notifications, so raw notification
 * creation doesn't get scattered across controllers. Every producer
 * (user management today; deployment/site/system/audit events later)
 * should call send()/sendToMany() rather than instantiating
 * ControlCenterNotification directly.
 */
class NotificationService
{
    /**
     * Controlled taxonomy — keep this list small and intentional rather
     * than growing it per feature.
     *
     * @var array<int, string>
     */
    public const TYPES = ['user_created', 'deployment_update', 'site_warning', 'system', 'audit'];

    /**
     * @var array<int, string>
     */
    public const SEVERITIES = ['info', 'success', 'warning', 'danger'];

    public function send(
        User $recipient,
        string $type,
        string $title,
        string $message,
        string $severity = 'info',
        ?string $url = null
    ): void {
        $recipient->notify(new ControlCenterNotification($type, $title, $message, $severity, $url));
    }

    /**
     * @param iterable<User> $recipients
     */
    public function sendToMany(
        iterable $recipients,
        string $type,
        string $title,
        string $message,
        string $severity = 'info',
        ?string $url = null
    ): void {
        foreach ($recipients as $recipient) {
            $this->send($recipient, $type, $title, $message, $severity, $url);
        }
    }
}
