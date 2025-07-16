<?php

namespace App\Repository;

use App\Entity\Subscription;
use App\Enum\SubsciptionStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscription>
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    /**
     * Count active subscriptions
     */
    public function countActiveSubscriptions(): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.status = :status')
            ->setParameter('status', SubsciptionStatusEnum::ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get conversion rate (simplified calculation)
     */
    public function getConversionRate(): float
    {
        $totalSubscriptions = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $activeSubscriptions = $this->countActiveSubscriptions();

        if ($totalSubscriptions == 0) {
            return 0.0;
        }

        return round(($activeSubscriptions / $totalSubscriptions) * 100, 1);
    }
}
