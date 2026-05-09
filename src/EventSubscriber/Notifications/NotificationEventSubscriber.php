<?php

namespace App\EventSubscriber\Notifications;

use App\Entity\Accounts;
use App\Entity\Notification;
use App\Event\Notification\NotificationDeletedAllEvent;
use App\Event\Notification\NotificationDeletedEvent;
use App\Event\Notification\NotificationReadAllEvent;
use App\Event\Notification\NotificationReadEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationEventSubscriber implements EventSubscriberInterface {

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
        return  [
            NotificationReadEvent::NAME => 'onNotificationRead',
            NotificationReadAllEvent::NAME => 'onNotificationReadAll',
            NotificationDeletedEvent::NAME =>'onNotificationDeleted',
            NotificationDeletedAllEvent::NAME => 'onNotificationDeletedAll'
        ];
        
    }

    private function isMessageNotification(string $message): bool {
        return str_starts_with($message, '[INFO]');
    }

    public function onNotificationRead(NotificationReadEvent $event): void {
        $notification = $event->getNotification();
        $user = $notification->getUsers();

        if ($this->isMessageNotification($notification->getMessage())) {
            return;
        }

        $message = "[INFO]: La notification est marque comme lu";

        $this->createNotification($user, $message);
    }

    public function onNotificationReadAll(NotificationReadAllEvent $event): void {
        
        $user = $event->getUsers();
        $message = "[INFO]: Vous avez marqué toutes vos notifications comme lues";
        $this->createNotification($user, $message);
    }

    public function onNotificationDeleted(NotificationDeletedEvent $event): void {
       $notification = $event->getNotification();
       $user = $notification->getusers();
        
        if ($this->isMessageNotification($notification->getMessage())) {
            return;
        }
        
        $message = "[INFO]: Une notification a été supprimée.";

        $this->createNotification($user, $message);
    }

        public function onNotificationDeletedAll(NotificationDeletedAllEvent $event): void {
        
        $user = $event->getUsers();
        $message = "[INFO]: Toutes vos notifications ont été supprimées.";

        $this->createNotification($user, $message);
    }

    private function createNotification(Accounts $user, string $message): void {
        $notification = (new Notification())
            ->setUsers($user)
            ->setType('system')
            ->setMessage($message)
            ->setIsRead(false)
            ->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($notification);
        $this->em->flush();
    }


}