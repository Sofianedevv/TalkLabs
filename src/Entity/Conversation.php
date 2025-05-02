<?php

namespace App\Entity;

use App\Enum\ConversationStatusEnum;
use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isPublic = null;

    #[ORM\Column(length: 50)]
    private ?string $interlocutorName = null;

    #[ORM\Column(length: 30)]
    private ?string $interlocutorUsername = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $interlocutorAvatarUrl = null;

    #[ORM\Column]
    private ?\DateTime $startAt = null;

    #[ORM\Column]
    private ?int $batteryLevel = null;

    #[ORM\Column(length: 10)]
    private ?string $networkType = null;

    #[ORM\Column(length: 10)]
    private ?string $signalQuality = null;

    #[ORM\Column(enumType: ConversationStatusEnum::class)]
    private ?ConversationStatusEnum $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'conversations')]
    private Collection $categories;

    #[ORM\ManyToOne(inversedBy: 'conversations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounts $creator = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'conversation')]
    private Collection $comments;

    /**
     * @var Collection<int, Favorite>
     */
    #[ORM\ManyToMany(targetEntity: Favorite::class, mappedBy: 'conversation')]
    private Collection $favorites;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'conversations')]
    private Collection $tags;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'conversation')]
    private Collection $reports;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function getInterlocutorName(): ?string
    {
        return $this->interlocutorName;
    }

    public function setInterlocutorName(string $interlocutorName): static
    {
        $this->interlocutorName = $interlocutorName;

        return $this;
    }

    public function getInterlocutorUsername(): ?string
    {
        return $this->interlocutorUsername;
    }

    public function setInterlocutorUsername(string $interlocutorUsername): static
    {
        $this->interlocutorUsername = $interlocutorUsername;

        return $this;
    }

    public function getInterlocutorAvatarUrl(): ?string
    {
        return $this->interlocutorAvatarUrl;
    }

    public function setInterlocutorAvatarUrl(?string $interlocutorAvatarUrl): static
    {
        $this->interlocutorAvatarUrl = $interlocutorAvatarUrl;

        return $this;
    }

    public function getStartAt(): ?\DateTime
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTime $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getBatteryLevel(): ?int
    {
        return $this->batteryLevel;
    }

    public function setBatteryLevel(int $batteryLevel): static
    {
        $this->batteryLevel = $batteryLevel;

        return $this;
    }

    public function getNetworkType(): ?string
    {
        return $this->networkType;
    }

    public function setNetworkType(string $networkType): static
    {
        $this->networkType = $networkType;

        return $this;
    }

    public function getSignalQuality(): ?string
    {
        return $this->signalQuality;
    }

    public function setSignalQuality(string $signalQuality): static
    {
        $this->signalQuality = $signalQuality;

        return $this;
    }

    public function getStatus(): ?ConversationStatusEnum
    {
        return $this->status;
    }

    public function setStatus(ConversationStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getCreator(): ?Accounts
    {
        return $this->creator;
    }

    public function setCreator(?Accounts $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setConversation($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getConversation() === $this) {
                $comment->setConversation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favorite>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Favorite $favorite): static
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
            $favorite->addConversation($this);
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): static
    {
        if ($this->favorites->removeElement($favorite)) {
            $favorite->removeConversation($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addConversation($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeConversation($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): static
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setConversation($this);
        }

        return $this;
    }

    public function removeReport(Report $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getConversation() === $this) {
                $report->setConversation(null);
            }
        }

        return $this;
    }
}
