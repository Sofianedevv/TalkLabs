<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePasswordDTO
{
    #[Assert\NotBlank(message: "Le nouveau mot de passe est obligatoire.")]
    #[Assert\Length(min: 8, minMessage: "Le mot de passe doit contenir au moins 6 caractères.")]
    public ?string $newPassword = null;

    #[Assert\NotBlank(message: "La confirmation du mot de passe est obligatoire.")]
    public ?string $confirmPassword = null;

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): self
    {
        $this->newPassword = $newPassword;
        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(?string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;
        return $this;
    }

}
