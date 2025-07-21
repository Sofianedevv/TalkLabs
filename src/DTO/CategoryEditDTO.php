<?php

namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryEditDTO
{
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private ?string $name = null;

    #[Assert\NotBlank(message: "Le nom court est obligatoire.")]
    private ?string $shortName = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): self
    {
        $this->shortName = $shortName;
        return $this;
    }
}
