<?php

namespace App\Service;

use App\DTO\ConversationDTO;

use App\DTO\ConversationEditDTO;
use App\Entity\Conversation;
use App\Enum\ConversationStatusEnum;
use App\Mapper\ConversationMapper;
use App\Repository\AccountsRepository;
use App\Repository\CategoryRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
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


    public function __construct(
        EntityManagerInterface $em,
        CategoryRepository     $categoryRepository,
        AccountsRepository     $accountsRepository,
        ConversationMapper     $conversationMapper,
        ConversationRepository $conversationRepository,
    )
    {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->accountsRepository = $accountsRepository;
        $this->conversationMapper = $conversationMapper;
        $this->conversationRepository = $conversationRepository;
    }

    public function createConversation(ConversationDTO $dto): void
    {
        //Statique
        $account = $this->accountsRepository->findOneBy(['email' => 'moussi@gmail.com']);
        if (!$account) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }
        //Statique
        $category = $this->categoryRepository->findOneBy(['name' => "Humour"]);
        if (!$category) {
            throw new \RuntimeException('Catégorie non trouvée');
        }

        $conversation = $this->conversationMapper->dtoToConversation($dto, $account, $category);
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

    public function editMessage($id, ConversationDTO $dto,): Boolean
    {
        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            throw new NotFoundHttpException('Conversation non trouvée');
        }
        //Statique
        $account = $this->accountsRepository->findOneBy(['email' => 'moussi@gmail.com']);
        if (!$account) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }
        //Statique
        $category = $this->categoryRepository->findOneBy(['name' => "Humour"]);
        if (!$category) {
            throw new \RuntimeException('Catégorie non trouvée');
        }
        $conversation = $this->conversationMapper->dtoToConversationEdit($dto, $conversation, $account, $category);

        $this->em->persist($conversation);
        $this->em->flush();
        return true;
    }

    public function deleteConversation($id){
        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            throw new \RuntimeException('Conversation non trouvée');
        }

        $this->em->remove($conversation);
        $this->em->flush();
    }

    public function getConversationById($id){
        /** @var Conversation|null $conversation */
        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            throw new \RuntimeException('Conversation non trouvée');
        }

        return $this->conversationMapper->conversationToDTOConversation($conversation);
    }

}