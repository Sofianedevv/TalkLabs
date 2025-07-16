<?php

namespace App\Repository;

use App\Entity\Accounts;
use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

//    /**
//     * @return Notification[] Returns an array of Notification objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Notification
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findAllByUser(Accounts $user) {
        return $this->createQueryBuilder('n')
        ->andWhere('n.users = :user')
        ->andWhere('n.type = :type')
        ->setParameter('user' , $user)
        ->setParameter('type', 'info')
        ->orderBy('n.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
    }

    public function findUnreadNotificationByUser(Accounts $user) {
        return $this->createQueryBuilder('n')
        ->andWhere('n.users = :user')
        ->andWhere('n.type = :type')
        ->andWhere('n.isRead = false')
        ->setParameter('user', $user)
        ->setParameter('type', 'info')
        ->orderBy('n.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
    }
    
    public function deleteAllByUser(Accounts $user) {
        return $this->createQueryBuilder('n')
        ->delete()
        ->where('n.users = :user')
        ->andWhere('n.type = :type')
        ->setParameter('user', $user)
        ->setParameter('type', 'info')
        ->getQuery()
        ->execute();
    } 
}
