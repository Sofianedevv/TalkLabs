<?php

namespace App\Service;

use App\Entity\Accounts;
use App\Entity\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
class RefreshTokenService {

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }



    public function createRefreshToken(Accounts $accounts, int $days = 2 ) : RefreshToken {

        $refreshToken = Uuid::v4()->toRfc4122();

        $newRefreshToken = new RefreshToken();
        $newRefreshToken->setRefreshToken($refreshToken);
        $newRefreshToken->setAccount($accounts);
        $newRefreshToken->setExpiresAt(new \DateTimeImmutable('+' . $days . ' days'));
        $newRefreshToken->setCreatedAt(new \DateTimeImmutable());
        $newRefreshToken->setIsRevoked(false);

        $this->entityManager->persist($newRefreshToken);
        $this->entityManager->flush();

        return $newRefreshToken;

    }
}