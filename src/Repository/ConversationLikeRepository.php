<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\ConversationLike;
use App\Entity\Accounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConversationLike>
 */
class ConversationLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationLike::class);
    }

//    /**
//     * @return ConversationLike[] Returns an array of ConversationLike objects
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

//    public function findOneBySomeField($value): ?ConversationLike
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findExistingLike(Conversation $conversation, Accounts $account) : ?ConversationLike {
        return $this->createQueryBuilder('cl')
            ->andWhere('cl.conversation = :conversation')
            ->andWhere('cl.account = :account')
            ->setParameter('conversation', $conversation)
            ->setParameter('account', $account)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    } 

    public function findLikesByConversation(Conversation $conversation) : array {
        return $this->createQueryBuilder('cl')
            ->andWhere('cl.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->getQuery()
            ->getResult();
    } 
        public function countlikesByConversations(int $conversationId) : ?int {
        return (int) $this->createQueryBuilder('cl')
            ->select('COUNT(cl.id)')
            ->andWhere('cl.conversation = :conversation')
            ->setParameter('conversation', $conversationId)
            ->getQuery()
            ->getSingleScalarResult();
    } 
}
