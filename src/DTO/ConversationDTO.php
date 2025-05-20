<?php

namespace App\DTO;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ConversationDTO
{
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    private string $title;

    #[Assert\Length(
        max: 500,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description;

    #[Assert\NotBlank(message: "L'ID de la catégorie ne peut pas être vide.")]
    #[Assert\Count(min: 1, minMessage: "Vous devez spécifier au moins un ID de catégorie.")]
    private array $categoriesId;

    #[Assert\NotBlank(message: "L'ID du créateur ne peut pas être vide.")]
    #[Assert\Type(
        type: "integer",
        message: "L'ID du créateur doit être un entier."
    )]
    private int $creatorId;

    #[Assert\NotBlank(message: "Le statut ne peut pas être vide.")]
    private string $status;

    #[Assert\NotBlank(message: "Le contenu ne peut pas être vide.")]
    private array $content;

    private bool $isPublic = true;


    // Getters and setters for all properties
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCategoryId(): array
    {
        return $this->categoriesId;
    }

    public function setCategoryId(array $categoriesId): self
    {
        $this->categoriesId = $categoriesId;
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

    public function getContent(): array
    {
        return $this->content;
    }

    public function setContent(array $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getIsPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    // Getter et Setter pour creatorId
    public function getCreatorId(): int
    {
        return $this->creatorId;
    }

    public function setCreatorId(int $creatorId): void
    {
        $this->creatorId = $creatorId;
    }

    // Getter et Setter pour categoriesId
    public function getCategoriesId(): array
    {
        return $this->categoriesId;
    }

    public function setCategoriesId(array $categoriesId): void
    {
        $this->categoriesId = $categoriesId;
    }
}
