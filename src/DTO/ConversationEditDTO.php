<?php

namespace App\DTO;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ConversationEditDTO extends ConversationDTO
{
    #[Assert\NotBlank(message: "L'id ne peut pas être vide.")]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
