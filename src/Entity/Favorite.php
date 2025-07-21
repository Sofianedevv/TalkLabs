<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
class Favorite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'favorites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounts $accountUser = null;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\ManyToMany(targetEntity: Conversation::class, inversedBy: 'favorites')]
    private Collection $conversation;

    public function __construct()
    {
        $this->conversation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccountUser(): ?Accounts
    {
        return $this->accountUser;
    }

    public function setAccountUser(?Accounts $accountUser): static
    {
        $this->accountUser = $accountUser;

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversation(): Collection
    {
        return $this->conversation;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversation->contains($conversation)) {
            $this->conversation->add($conversation);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        $this->conversation->removeElement($conversation);

        return $this;
    }
}
