<?php

namespace App\Service;

use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConversationService {

    private EntityManagerInterface $em;
    private ConversationRepository $conversationRepository;

    public function __construct(EntityManagerInterface $em, ConversationRepository $conversationRepository) {
        $this->em = $em;
        $this->conversationRepository = $conversationRepository;
    }

    public function resetConversation(int $id):void {

        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            throw new NotFoundHttpException('Conversation non trouvée');
        }

        $actualConversation = clone $conversation;

        $conversation->setTitle($actualConversation->getTitle());
        $conversation->setDescription($actualConversation->getDescription());
        $conversation->setStatus($actualConversation->getStatus());
        $conversation->setContent($actualConversation->getContent());
        $conversation->setIsPublic($actualConversation->getIsPublic());
        $conversation->setCreatedAt($actualConversation->getCreatedAt());
        $conversation->setUpdatedAt(new \DateTimeImmutable());

        $this->em->flush();
    }


}