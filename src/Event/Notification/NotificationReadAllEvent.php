<?php

namespace App\Event\Notification;

use App\Entity\Accounts;
use Symfony\Contracts\EventDispatcher\Event;

class NotificationReadAllEvent extends Event {

    public const NAME = 'notification.read_all';

    public function __construct(private Accounts $user) {}

    public function getUsers(): Accounts {
        return $this->user;
    }
}