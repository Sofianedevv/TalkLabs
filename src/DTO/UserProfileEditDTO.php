<?php

namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;


class UserProfileEditDTO {

    public ?string $username = null;
    public ?string $email = null;


    public function __construct(?string $username = null, ?string $email = null)
    {
        $this->username = $username;
        $this->email = $email;
    }


}