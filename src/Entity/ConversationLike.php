<?php

namespace App\Entity;

use App\Repository\ConversationLikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationLikeRepository::class)]
class ConversationLike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'conversationLikes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounts $account = null;

    #[ORM\ManyToOne(inversedBy: 'conversationLikes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $conversation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): ?Accounts
    {
        return $this->account;
    }

    public function setAccount(?Accounts $account): static
    {
        $this->account = $account;

        return $this;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;

        return $this;
    }
}
