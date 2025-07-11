<?php

namespace App\DTO;

class ConversationReadDTO
{
    private int $id;
    private string $title;
    private ?string $description;
    private array $content;
    private string $status;
    private bool $isPublic;
    private array $categoriesId;
    private string $createdAt;
    private ?string $updatedAt;
    private string $author;


    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getContent(): array
    {
        return $this->content;
    }
    public function setContent(array $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getIsPublic(): bool
    {
        return $this->isPublic;
    }
    public function setIsPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }

    public function getCategoriesId(): array
    {
        return $this->categoriesId;
    }
    public function setCategoriesId(array $categoriesId): void
    {
        $this->categoriesId = $categoriesId;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }
    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }
}
