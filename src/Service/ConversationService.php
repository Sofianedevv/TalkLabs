<?php

namespace App\Service;

use App\DTO\ConversationDTO;

use App\DTO\ConversationEditDTO;
use App\Entity\Conversation;
use App\Entity\Accounts;
use App\Enum\ConversationStatusEnum;
use App\Mapper\ConversationMapper;
use App\Repository\AccountsRepository;
use App\Repository\CategoryRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ConversationService
{

    private EntityManagerInterface $em;
    private categoryRepository $categoryRepository;
    private AccountsRepository $accountsRepository;
    private ConversationMapper $conversationMapper;
    private ConversationRepository $conversationRepository;
    private Security $security;

    public function __construct(
        EntityManagerInterface $em,
        CategoryRepository     $categoryRepository,
        AccountsRepository     $accountsRepository,
        ConversationMapper     $conversationMapper,
        ConversationRepository $conversationRepository,
        Security $security
    )
    {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->accountsRepository = $accountsRepository;
        $this->conversationMapper = $conversationMapper;
        $this->conversationRepository = $conversationRepository;
        $this->security = $security;
    }

    public function createConversation(ConversationDTO $dto): void
    {
        
        $creator = $this->security->getUser();
        if (!$creator) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }
        
        //Statique
        $category = $this->categoryRepository->findOneBy(['name' => "Humour"]);
        if (!$category) {
            throw new \RuntimeException('Catégorie non trouvée');
        }

        $conversation = $this->conversationMapper->dtoToConversation($dto, $creator, $category);
        $this->em->persist($conversation);
        $this->em->flush();
    }

    public function getAllConversations(): array
    {
        $conversations = $this->conversationRepository->findAll();
        $data = [];
        foreach ($conversations as $conversation) {
            $data[] = [
                'id' => $conversation->getId(),
                'title' => $conversation->getTitle(),
                'description' => $conversation->getDescription(),
                'status' => $conversation->getStatus()->value,
                'content' => $conversation->getContent(),
                'is_public' => $conversation->isPublic(),
                'created_at' => $conversation->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $conversation->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        return $data;
    }

    public function editMessage($id, ConversationDTO $dto): bool
    {
        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            throw new NotFoundHttpException('Conversation non trouvée');
        }
        
        /** @var Accounts|null $creator */
        $creator = $this->security->getUser();
        if (!$creator) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }
        if ($conversation->getCreator()->getId() !== $creator->getId()) {
            throw new \RuntimeException('Vous n\'êtes pas autorisé à modifier cette conversaiton');
        }

        //Statique
        $category = $this->categoryRepository->findOneBy(['name' => "Humour"]);
        if (!$category) {
            throw new \RuntimeException('Catégorie non trouvée');
        }
        $conversation = $this->conversationMapper->dtoToConversationEdit($dto, $conversation, $creator, $category);

        $this->em->persist($conversation);
        $this->em->flush();
        return true;
    }

    public function deleteConversation($id){
        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            throw new \RuntimeException('Conversation non trouvée');
        }

        /** @var Accounts|null $creator */
        $creator = $this->security->getUser(); 
        if (!$creator) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }
        if ($conversation->getCreator()->getId() !== $creator->getId()) {
            throw new \RuntimeException('Vous n\'êtes pas autorisé à modifier cette conversaiton');
        }

        $this->em->remove($conversation);
        $this->em->flush();
    }

    public function getConversationById($id) {
        /** @var Conversation|null $conversation */
        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            throw new \RuntimeException('Conversation non trouvée');
        }

        return $this->conversationMapper->conversationToDTOConversation($conversation);
    }

    public function getConversationsByUser(): array {
        /** @var Accounts|null $user */
        $user = $this->security->getUser(); 
        if (!$user) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $conversations = $this->conversationRepository->findBy(['creator' => $user]);
        $data = [];
        foreach ($conversations as $conversation) {
            $data[] = [
                'id' => $conversation->getId(),
                'title' => $conversation->getTitle(),
                'description' => $conversation->getDescription(),
                'status' => $conversation->getStatus()->value,
                'content' => $conversation->getContent(),
                'is_public' => $conversation->isPublic(),
                'created_at' => $conversation->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $conversation->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        return $data;

    }

}