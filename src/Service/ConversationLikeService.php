<?php 

namespace App\Service;

use App\DTO\ConversationLikeDTO;
use App\Entity\Accounts;
use App\Entity\Conversation;
use App\Entity\ConversationLike;
use App\Repository\ConversationLikeRepository;
use App\Mapper\ConversationLikeMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;




class ConversationLikeService {

    private EntityManagerInterface $em;
    private ConversationLikeRepository $conversationLikeRepository;
    private ConversationLikeMapper $conversationLikeMapper;
    private Security $security;


      public function __construct(EntityManagerInterface $em, ConversationLikeRepository $conversationLikeRepository, ConversationLikeMapper $conversationLikeMapper, Security $security) {
        $this->em = $em;
        $this->conversationLikeRepository = $conversationLikeRepository;
        $this->conversationLikeMapper = $conversationLikeMapper;
        $this->security = $security;
      }
      
    public function addLike (ConversationLikeDTO $dto) : bool
    {
        $account = $this->security->getUser();
        if (!$account) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }
        $conversationLike = $this->conversationLikeMapper->dtoToConversationLike($dto,$account);
        $conversation = $conversationLike->getConversation();
        $existingLike = $this->conversationLikeRepository->findExistingLike($conversation, $account);

        if($existingLike !== null) {
            return false;
        }
        $this->em->persist($conversationLike);
        $this->em->flush();

        return true;
    }



    public function removeLike (Conversation $conversation) : bool
    {
        $account = $this->security->getUser();
        if (!$account) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }
        $existingLike = $this->conversationLikeRepository->findExistingLike($conversation, $account);

        if($existingLike === null) {
            return false;
        }
        $this->em->remove($existingLike);
        $this->em->flush();

        return true;
    }

    public function getAccountsLiked(Conversation $conversation) : array {
        $likes = $this->conversationLikeRepository->findLikesByConversation($conversation) ?? [];
        $accounts = [];

        foreach ($likes ?? [] as $like) {
            $account = $like->getAccount();

            $accounts[] = [
                'id'       => $account->getId(),
                'username' => $account->getUsername(),
            ];
        }

        return $accounts;
    }

    public function countLikes(Conversation $conversation) : int {
        return $this->conversationLikeRepository->countlikesByConversations($conversation->getId());
    }

      


}