<?php

namespace App\Mapper;

use App\DTO\ConversationDTO;
use App\DTO\ConversationEditDTO;
use App\DTO\ConversationReadDTO;
use App\Entity\Accounts;
use App\Entity\Category;
use App\Entity\Conversation;
use App\Enum\ConversationStatusEnum;

class ConversationMapper
{

    private CategoryMapper $categoryMapper;

    public function __construct(CategoryMapper $categoryMapper) {
        $this->categoryMapper = $categoryMapper;
    }

    public function dtoToConversation(
        ConversationDTO $dto,
        Accounts        $creator,
       array $categories
    ): Conversation {
        $conversation = new Conversation();
        $conversation->setTitle($dto->getTitle());
        $conversation->setDescription($dto->getDescription());
        $conversation->setContent($dto->getContent());
        $conversation->setCreator($creator);
        $conversation->setStatus(ConversationStatusEnum::from($dto->getStatus()));
        $conversation->setIsPublic($dto->getIsPublic() ?? true);
        $conversation->setCreatedAt(new \DateTimeImmutable());
        $conversation->setUpdatedAt(new \DateTimeImmutable());
        foreach($categories as $category) {
            $conversation->addCategory($category);
        }
        return $conversation;
    }

    public function dtoToConversationEdit(
        ConversationDTO $dto,
        Conversation $conversation,
        Accounts        $creator,
        array $categories
    ): Conversation {
        $conversation->setTitle($dto->getTitle());
        $conversation->setDescription($dto->getDescription());
        $conversation->setContent($dto->getContent());
        $conversation->setStatus(ConversationStatusEnum::from($dto->getStatus()));
        $conversation->setIsPublic($dto->getIsPublic());
        $conversation->setUpdatedAt(new \DateTimeImmutable());
        foreach($categories as $category) {
            $conversation->addCategory($category);
        }
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
        $categories = [];
        foreach($conversation->getCategories() as $category){
            $categories[] = $this->categoryMapper->categoryToDTO($category);
        }
        $conversationDTO->setCategoryId($categories);
        return $conversationDTO;
    }

    public function conversationToReadDTO(Conversation $conversation) : ConversationReadDTO {
        $dto = new ConversationReadDTO();
        $dto->setId($conversation->getId());
        $dto->setTitle($conversation->getTitle());
        $dto->setDescription($conversation->getDescription());
        $dto->setContent($conversation->getContent());
        $dto->setStatus($conversation->getStatus()->value);
        $dto->setIsPublic($conversation->isPublic());
        $dto->setCreatedAt($conversation->getCreatedAt()->format('Y-m-d H:i:s'));
        $dto->setUpdatedAt($conversation->getUpdatedAt()->format('Y-m-d H:i:s'));
        $categories = [];
        foreach ($conversation->getCategories() as $category) {
            $categories[] = $this->categoryMapper->categoryToDTO($category);

        }
        $dto->setCategoriesId($categories);

        return $dto;
    }
}