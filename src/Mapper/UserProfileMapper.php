<?php

namespace App\Mapper;

use App\DTO\UserProfileEditDTO;
use App\Entity\Accounts;

class UserProfileMapper {
    
    
    public function dtoToAccountProfile(UserProfileEditDTO $dto, Accounts $user): Accounts {
        

        if($dto->username !== null) {
            $user->setUsername($dto->username);
        }

        if($dto->email !== null) {
            $user->setEmail($dto->email);
        }

        return $user;

    }
}