<?php

namespace App\DTO;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ConversationEditDTO extends ConversationDTO
{
    #[Assert\NotBlank(message: "L'id ne peut pas être vide.")]
    private string $id;

    public function getId(): string
    {
        return $this->id;
    }
    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
