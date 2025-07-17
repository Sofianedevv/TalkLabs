<?php
namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;

class ConversationLikeDTO
{
    #[Assert\NotNull()]
    private int $conversationId;



    public function getConversationId(): int
    {
        return $this->conversationId;
    }

    public function setConversationId(int $conversationId): self
    {
        $this->conversationId = $conversationId;
        return $this;
    }

}
