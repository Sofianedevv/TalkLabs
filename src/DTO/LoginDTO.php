<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class LoginDTO
{
    #[Assert\NotBlank(message: "L'email est requis.")]
    #[Assert\Email(message: "Format d'email invalide.")]
    public ?string $email;

    #[Assert\NotBlank(message: "Le mot de passe est requis.")]
    public ?string $password;

    public ?string $totpCode;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getTotpCode(): ?string
    {
        return $this->totpCode;
    }

    public function setTotpCode(?string $totpCode): self
    {
        $this->totpCode = $totpCode;
        return $this;
    }
}
