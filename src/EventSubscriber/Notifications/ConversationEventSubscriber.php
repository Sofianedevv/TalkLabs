<?php

namespace App\EventSubscriber\Notifications;

use App\Entity\Notification;
use App\Entity\Accounts;
use App\Event\Conversation\ConversationCreatedEvent;
use App\Event\Conversation\ConversationDeletedEvent;
use App\Event\Conversation\ConversationEditedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConversationEventSubscriber implements EventSubscriberInterface {

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
        return  [
            ConversationCreatedEvent::NAME => 'onConversationCreated',
            ConversationEditedEvent::NAME => 'onConversationEdited',
            ConversationDeletedEvent::NAME =>'onConversationDeleted',
        ];
        
    }


    public function onConversationCreated(ConversationCreatedEvent $event): void {
        $conversation = $event->getConversation();
        $user = $conversation->getCreator();
        $message = "Votre conversation a été créé avec succès";
       
        $this->createNotification($user, $message);
    }

    public function onConversationEdited(ConversationEditedEvent $event): void {
        $conversation = $event->getConversation();
        $user = $conversation->getCreator();
        $message = "Votre conversation a été modifié avec succès";

        $this->createNotification($user, $message);
    }

    public function onConversationDeleted(ConversationDeletedEvent $event): void {
        $conversation = $event->getConversation();
        $user = $conversation->getCreator();
        $message= "Votre conversation a été supprimée avec succès";

        $this->createNotification($user, $message);
    }

private function createNotification(?Accounts $user, string $message): void {
    if (!$user) {
        throw new \RuntimeException('User null dans createNotification');
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