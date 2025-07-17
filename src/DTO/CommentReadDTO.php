<?php

namespace App\DTO;

class CommentReadDTO
{
    private int $id;
    private string $content;
    private string $createdAt;
    private int $conversationId;
    private string $status;
    private array $publisher;
    private ?int $parentCommentId = null;
    private array $childComments = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }


        public function getPublisher(): array
    {
        return $this->publisher;

    }
    public function setPublisher(array $publisher ): void
    {
        $this->publisher = $publisher;

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

    public function getChildComments(): array
    {
        return $this->childComments;
    }

    public function setChildComments(array $childComments): self
    {
        $this->childComments = $childComments;
        return $this;
    }
}
