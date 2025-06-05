<?php

namespace App\Service;

use App\Entity\Accounts;
use App\Entity\Conversation;
use App\Entity\Favorite;
use App\Repository\ConversationRepository;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class FavoriteService {

    private EntityManagerInterface $em;
    private ConversationRepository $conversationRepository;
    private FavoriteRepository $favoriteRepository;
    private Security $security;

    public function __construct(EntityManagerInterface $em, ConversationRepository $conversationRepository, FavoriteRepository $favoriteRepository,Security $security)
    {
        $this->em = $em;
        $this->conversationRepository = $conversationRepository;
        $this->favoriteRepository = $favoriteRepository;
        $this->security = $security;
    }

    public function addConversationToFavorite(int $conversationId): void {

        $user = $this->security->getUser();

        if(!$user) {
            throw new \RuntimeException('Utilisateur non authentifié');
        }

        $conversation = $this->conversationRepository->find($conversationId);

        if(!$conversation) {
            throw new \RuntimeException('Conversation introuvable');
        }

        $favorite = $this->favoriteRepository->findOneBy(['accountUser' => $user]);

        if(!$favorite) {
            $favorite = new Favorite();
            $favorite->setAccountUser($user);
        }

        if(!$favorite->getConversation()->contains($conversation)) {
            $favorite->addConversation($conversation);
            $this->em->persist($favorite);
            $this->em->flush();
        }
    }

    public function removeFavorites(int $conversationId): void {
       
        $user = $this->security->getUser();

        if(!$user) {
            throw new \RuntimeException('Utilisateur non authentifié');
        }

        $conversation = $this->conversationRepository->find($conversationId);

        if(!$conversation) {
            throw new \RuntimeException('Conversation introuvable');
        }

        $favorite = $this->favoriteRepository->findOneBy(['accountUser' => $user]);

        if($favorite && $favorite->getConversation()->contains($conversation)) {
            $favorite->removeConversation($conversation);
            $this->em->persist($favorite);
            $this->em->flush();
        }
    }


    public function getFavoritesOfUser(): array {

        $user = $this->security->getUser();

        if(!$user) {
            throw new \RuntimeException('Utilisateur non authentifié');
        }

        $favorites = $this->favoriteRepository->findBy(['accountUser' => $user]);
        
        $conversations = [];

        foreach ($favorites as $favorite) {
            foreach( $favorite->getConversation() as $conversation) {
                $conversations[] = [
                    'id' => $conversation->getId(),
                    'title' => $conversation->getTitle(),
                    'description' => $conversation->getDescription(),
                    'content' => $conversation->getContent(),
                    'createdAt' => $conversation->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $conversation->getUpdatedAt()->format('Y-m-d H:i:s'),
                ];
            } 
        }
        return [
            'count' => count($conversations),
            'conversations' => $conversations
        ];
    }





}