<?php

namespace App\Event\Notification;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\Notification;

class NotificationDeletedEvent extends Event {

    public const NAME = 'notification.deleted';

    public function __construct(private Notification $notification) {}

    public function getNotification(): Notification {
        return $this->notification;
    }
}