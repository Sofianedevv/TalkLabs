<?php

namespace App\EventSubscriber\Notifications;

use App\Entity\Notification;
use App\Entity\Accounts;
use App\Event\Favorite\FavoriteCreatedEvent;
use App\Event\Favorite\FavoriteDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FavoriteEventSubscriber implements EventSubscriberInterface {

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
        return  [
            FavoriteCreatedEvent::NAME => 'onFavoriteCreated',
            FavoriteDeletedEvent::NAME =>'onFavoriteDeleted',
        ];
        
    }


    public function onFavoriteCreated(FavoriteCreatedEvent $event): void {
        $conversation = $event->getConversation();
        $user = $event->getFavorite()->getAccountUser();
        $message = sprintf('Vous avez ajouté la conversation "%s" à vos favoris.', $conversation->getTitle());
       
        $this->createNotification($user, $message);
    }

    public function onFavoriteDeleted(FavoriteDeletedEvent $event): void {
        $conversation = $event->getConversation();
        $user = $event->getFavorite()->getAccountUser();
        $message = sprintf('Vous avez retiré la conversation "%s" de vos favoris.', $conversation->getTitle());


        $this->createNotification($user, $message);
    }

private function createNotification(?Accounts $user, string $message): void {
    if (!$user) {
        throw new \RuntimeException('Aucun utilisateur trouvé');
    }

    $notification = (new Notification())
        ->setUsers($user)
        ->setType('info')
        ->setMessage($message)
        ->setIsRead(false)
        ->setCreatedAt(new \DateTimeImmutable());

    $this->em->persist($notification);
    $this->em->flush();
}

}