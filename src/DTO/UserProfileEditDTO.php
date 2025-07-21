<?php

namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;


class UserProfileEditDTO {

    public ?string $username = null;
    public ?string $email = null;
    public ?string $newPassword;
    public ?string $newPasswordConfirm;

    public function __construct(?string $username = null, ?string $email = null, ?string $newPassword = null, ?string $newPasswordConfirm = null)
    {
        $this->username = $username;
        $this->email = $email;
        $this->newPassword = $newPassword;
        $this->newPasswordConfirm = $newPasswordConfirm;
    }


}