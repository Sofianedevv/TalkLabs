<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\Tools\Pagination\Paginator;

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


    public function findPublicConversationsByTitleAndCategoryNames(string $title, array $categoryNames): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.categories', 'cat')
            ->where('c.isPublic = :isPublic')
            ->andWhere('LOWER(c.title) LIKE :title')
            ->andWhere('cat.name IN (:names)')
            ->setParameter('isPublic', 'true') 
            ->setParameter('title', '%' . strtolower($title) . '%')
            ->setParameter('names', $categoryNames, ArrayParameterType::STRING)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }

    public function findPublicConversationsByTitle(string $title) : array {
        return $this->createQueryBuilder('c')
            ->where('c.isPublic = :isPublic')
            ->andWhere('LOWER(c.title) LIKE :title')
            ->setParameter('isPublic', 'true') 
            ->setParameter('title', '%' . strtolower($title) . '%')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

    }

        public function findPublicConversationsPaginated(string $title, array $categoryNames, int $page, int $limit): array
    {

        $page = max(1, $page);
        $limit = max(1, min(20, $limit));

        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.categories', 'cat')
            ->addSelect('cat')
            ->where('c.isPublic = :isPublic')
            ->andWhere('LOWER(c.title) LIKE :title')
            ->setParameter('isPublic', 'true') 
            ->setParameter('title', '%' . strtolower($title) . '%');
            if (!empty($categoryNames)) {
                 $qb->andWhere('cat.name IN (:names)')
                    ->setParameter('names', $categoryNames, ArrayParameterType::STRING);
            }
            $qb->orderBy('c.createdAt', 'DESC');
        $query = $qb->getQuery()
                    ->setFirstResult(($page -1 ) * $limit)
                    ->setMaxResults($limit);
        $paginator = new Paginator($query);
        $total = count($paginator);
        $conversations = iterator_to_array($paginator);

        return [
            'total' => $total,
            'conversations' => $conversations,
            'pages' => (int) ceil($total / $limit),
            'currentPage'=> $page,
            'limit' => $limit
        ];

    }
}
