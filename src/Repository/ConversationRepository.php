<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\ArrayParameterType;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

//    /**
//     * @return Conversation[] Returns an array of Conversation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Conversation
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findAllPublicConversations(): array {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isPublic = :public')
            ->setParameter('public', true)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublicConversationsByCategory(int $categoryId): array {
        return $this->createQueryBuilder('c')
                ->innerJoin('c.categories', 'cat')
                ->where('cat.id = :categoryId')
                ->andWhere('c.isPublic = :isPublic')
                ->setParameter('categoryId', $categoryId)
                ->setParameter('isPublic', true)
                ->getQuery()
                ->getResult();
    }

    public function findPublicConversationsByCategoryNames(array $categoryNames) : array {
              return $this->createQueryBuilder('c')
                ->innerJoin('c.categories', 'cat')
                ->where('cat.name IN (:names)')
                ->andWhere('c.isPublic = :isPublic')
                ->setParameter('names', $categoryNames, ArrayParameterType::STRING)
                ->setParameter('isPublic', true)
                ->groupBy('c.id')
                ->getQuery()
                ->getResult();
    }

    public function findPublicConversationsByTitleAndCategoryNames(string $title, array $categoryNames): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.categories', 'cat')
            ->where('c.isPublic = :isPublic')
            ->andWhere('LOWER(c.title) LIKE :title')
            ->andWhere('cat.name IN (:names)')
            ->setParameter('status', 'public') 
            ->setParameter('title', '%' . strtolower($title) . '%')
            ->setParameter('names', $categoryNames, ArrayParameterType::STRING)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
}
