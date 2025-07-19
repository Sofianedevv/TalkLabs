<?php

namespace App\Repository;

use App\Entity\Payment;
use App\Enum\PaymentStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * Get current month revenue
     */
    public function getCurrentMonthRevenue(): float
    {
        $startOfMonth = new \DateTime('first day of this month');
        $endOfMonth = new \DateTime('last day of this month');

        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->andWhere('p.createdAt BETWEEN :start AND :end')
            ->andWhere('p.status = :status')
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->setParameter('status', PaymentStatusEnum::COMPLETED)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * Get monthly statistics
     */
    public function getMonthlyStats(): array
    {
        // Simplified: return last 6 months
        $stats = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime();
            $date->modify("-$i months");
            $startOfMonth = $date->format('Y-m-01');
            $endOfMonth = $date->format('Y-m-t');

            $revenue = $this->createQueryBuilder('p')
                ->select('SUM(p.amount)')
                ->andWhere('p.createdAt BETWEEN :start AND :end')
                ->andWhere('p.status = :status')
                ->setParameter('start', $startOfMonth)
                ->setParameter('end', $endOfMonth)
                ->setParameter('status', PaymentStatusEnum::COMPLETED)
                ->getQuery()
                ->getSingleScalarResult();

            $stats[] = [
                'month' => $date->format('Y-m'),
                'revenue' => $revenue ? (float) $revenue : 0.0
            ];
        }

        return $stats;
    }

    /**
     * Get revenue by plan
     */
    public function getRevenueByPlan(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('pl.name, SUM(p.amount) as revenue')
            ->join('p.subscription', 's')
            ->join('s.plan', 'pl')
            ->andWhere('p.status = :status')
            ->setParameter('status', PaymentStatusEnum::COMPLETED)
            ->groupBy('pl.id')
            ->orderBy('revenue', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
