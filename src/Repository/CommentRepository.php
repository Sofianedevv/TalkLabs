<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Conversation;
use App\Enum\CommentStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

//    /**
//     * @return Comment[] Returns an array of Comment objects
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

//    public function findOneBySomeField($value): ?Comment
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findPendingComments() :array {
        return $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->setParameter('status', CommentStatusEnum::PENDING)
            ->getQuery()
            ->getResult();
    }

    public function findAllComment(Conversation $conversation): array {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.publisher', 'p')->addSelect('p')
            ->leftJoin('c.childComments', 'cc')->addSelect('cc')
            ->where('c.conversation = :conversation')
            ->andWhere('c.parentComment IS NULL')
            ->orderBy('c.createdAt', 'ASC')
            ->setParameter('conversation', $conversation)
            ->getQuery()
            ->getResult();
    }








}
