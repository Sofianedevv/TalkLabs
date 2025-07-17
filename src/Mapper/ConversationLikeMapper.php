<?php


namespace App\Mapper;

use App\Dto\ConversationLikeDTO;
use App\Entity\Accounts;
use App\Entity\Conversation;
use App\Entity\ConversationLike;
use App\Repository\ConversationRepository;

class ConversationLikeMapper {


    private ConversationRepository $conversationRepository;


    public function __construct( ConversationRepository $conversationRepository) {
        $this->conversationRepository = $conversationRepository;
    }
 
    public function dtoToConversationLike(ConversationLikeDTO $dto, Accounts $publisher) : ConversationLike {
        $convLike = new ConversationLike();
        $conversation = $this->conversationRepository->find($dto->getConversationId());
        $convLike->setConversation($conversation);
        $convLike->setAccount($publisher);

        return $convLike;
    }
}
