<?php

namespace App\Mapper;

use App\DTO\ConversationDTO;
use App\DTO\ConversationEditDTO;
use App\Entity\Accounts;
use App\Entity\Category;
use App\Entity\Conversation;
use App\Enum\ConversationStatusEnum;

class ConversationMapper
{
    public function dtoToConversation(
        ConversationDTO $dto,
        Accounts        $creator,
        Category        $category
    ): Conversation {
        $conversation = new Conversation();
        $conversation->setTitle($dto->getTitle());
        $conversation->setDescription($dto->getDescription());
        $conversation->setContent($dto->getContent());
        $conversation->setCreator($creator);
        $conversation->addCategory($category);
        $conversation->setStatus(ConversationStatusEnum::from($dto->getStatus()));
        $conversation->setIsPublic($dto->getIsPublic() ?? true);
        $conversation->setCreatedAt(new \DateTimeImmutable());
        $conversation->setUpdatedAt(new \DateTimeImmutable());
        return $conversation;
    }

    public function dtoToConversationEdit(
        ConversationDTO $dto,
        Conversation $conversation,
        Accounts        $creator,
        Category        $category
    ): Conversation {
        $conversation->setTitle($dto->getTitle());
        $conversation->setDescription($dto->getDescription());
        $conversation->setContent($dto->getContent());
        $conversation->addCategory($category);
        $conversation->setStatus(ConversationStatusEnum::from($dto->getStatus()));
        $conversation->setIsPublic($dto->getIsPublic());
        $conversation->setUpdatedAt(new \DateTimeImmutable());
        return $conversation;
    }

    public function conversationToDTOConversation(Conversation $conversation): ConversationEditDTO{
        $conversationDTO = new ConversationEditDTO();
        $conversationDTO->setId($conversation->getId());
        $conversationDTO->setTitle($conversation->getTitle());
        $conversationDTO->setDescription($conversation->getDescription());
        $conversationDTO->setContent($conversation->getContent());
        $conversationDTO->setStatus($conversation->getStatus()->value);
        $conversationDTO->setIsPublic($conversation->isPublic() );
        $conversationDTO->setCategoriesId([1] ); //TODO mettre la logique pour la gestion des id pour les categories
        return $conversationDTO;
    }
}