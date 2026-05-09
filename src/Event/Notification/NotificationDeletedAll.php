<?php

namespace App\Event\Notification;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\Accounts;

class NotificationDeletedAllEvent extends Event {

    public const NAME = 'notification.deleted_all';

    public function __construct(private Accounts $user) {}

    public function getUsers(): Accounts {
        return $this->user;
    }
}