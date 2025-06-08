<?php

namespace App\Service;

use App\Entity\Accounts;
use App\Event\Notification\NotificationDeletedAllEvent;
use App\Event\Notification\NotificationDeletedEvent;
use App\Event\Notification\NotificationReadAllEvent;
use App\Event\Notification\NotificationReadEvent;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NotificationService {

    private EntityManagerInterface $em;
    private Security $security;
    private NotificationRepository $notificationRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EntityManagerInterface $em, Security $security, NotificationRepository $notificationRepository, EventDispatcherInterface $eventDispatcher) {
        $this->em = $em;
        $this->security = $security;
        $this->notificationRepository = $notificationRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getAllNotifications() :array {
        
        $user = $this->security->getUser();
        
        if(!$user) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $notifications = $this->notificationRepository->findAllByUser($user);

        $allNotifications = [];

        foreach ($notifications as $notification) {
            $allNotifications[] = [
                'id' => $notification->getId(),
                'message' => $notification->getMessage(),
                'isRead' => $notification->isRead(),
                'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return $allNotifications;
    }

    public function markAsread($notificationId) : void {

        $user = $this->security->getUser();
       
        if(!$user) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $notification = $this->notificationRepository->find($notificationId);

        if(!$notificationId || $notification->getUsers() !== $user) {
            throw new \RuntimeException('Aucune notifcation trouvée');
        }

        $notification->setIsRead(true);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new NotificationReadEvent($notification), NotificationReadEvent::NAME);
    }

    public function markAllAsRead(): void {
        
        $user = $this->security->getUser();
       
        if(!$user) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $notifications = $this->notificationRepository->findUnreadNotificationByUser($user);

        foreach ($notifications as $notification) {
            $notification->setIsRead(true);
        }

        $this->em->flush();

        $this->eventDispatcher->dispatch(new NotificationReadAllEvent($user), NotificationReadAllEvent::NAME);

    }

    public function delete($notificationId): void {

        $user = $this->security->getUser();
       
        if(!$user) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $notification = $this->notificationRepository->find($notificationId);

        if(!$notificationId || $notification->getUsers() !== $user) {
            throw new \RuntimeException('Aucune notifcation trouvée');
        }

        $this->em->remove($notification);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new NotificationDeletedEvent($notification), NotificationDeletedEvent::NAME);

    }

    public function deleteAll(): void {

        $user = $this->security->getUser();
       
        if(!$user) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $this->notificationRepository->deleteAllByUser($user);

        $this->eventDispatcher->dispatch(new NotificationDeletedAllEvent($user), NotificationDeletedAllEvent::NAME);

    }

}