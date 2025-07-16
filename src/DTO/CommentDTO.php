<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;


class CommentDTO
{

    #[Assert\NotBlank(message: "Veuillez saisir un commentaire si vous souhaitez ajouter")]
    private string $content;
    private string $status;

    #[Assert\NotNull()]
    private int $conversationId;

    private ?int $parentCommentId = null;

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
        public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getConversationId(): int
    {
        return $this->conversationId;
    }

    public function setConversationId(int $conversationId): self
    {
        $this->conversationId = $conversationId;
        return $this;
    }

    public function getParentCommentId(): ?int
    {
        return $this->parentCommentId;
    }

    public function setParentCommentId(?int $parentCommentId): self
    {
        $this->parentCommentId = $parentCommentId;
        return $this;
    }
}
