<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Repository\AccountsRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use App\Enum\ConversationStatusEnum;
use App\Repository\ConversationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Hub;
use Symfony\Component\Routing\Attribute\Route;

final class ConversationController extends AbstractController
{
    #[Route('/api/conversation', name: 'app_conversation', methods: ['POST'])]
    public function createFakeConversation(
        Request $request,
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        AccountsRepository $accountsRepository,
        HubInterface $hub
    ): JsonResponse 
    {
        $data = json_decode($request->getContent(), true);

        if(empty($data['title']) || empty($data['category_id']) || empty($data['status']) || empty($data['content'])) {
            return $this->json(['error' => 'Champs manquants'], Response::HTTP_BAD_REQUEST);
        }

        $user = $accountsRepository->findOneBy(['email' => 'moussi@gmail.com']);
        
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], Response::HTTP_BAD_REQUEST);
        }

        $category = $categoryRepository->findOneBy(['name' => 'Humour']);
        
        if (!$category) {
            return $this->json(['error' => 'Categorie non trouvé'], Response::HTTP_BAD_REQUEST);
        }

        $status = $data['status'] ?? 'draft';
        $conversation = new Conversation();
        $conversation->setTitle($data['title']);
        $conversation->setDescription($data['description'] ?? null);
        $conversation->addCategory($category);
        $conversation->setCreator($user);
        $conversation->setStatus(ConversationStatusEnum::from($status));
        $conversation->setContent($data['content']);
        $conversation->setIsPublic($data['is_public'] ?? true);
        $conversation->setCreatedAt(new \DateTimeImmutable());
        $conversation->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($conversation);
        $em->flush();
        $update = new Update(
            'http://localhost:8081/conversations/' . $conversation->getId(), 
            json_encode([
                'id' => $conversation->getId(),
                'title' => $conversation->getTitle(),
                'description' => $conversation->getDescription(),
                'status' => $conversation->getStatus()->value,
                'content' => $conversation->getContent(),
                'is_public' => $conversation->isPublic(),
                'created_at' => $conversation->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $conversation->getUpdatedAt()->format('Y-m-d H:i:s'),
            ])
            );
        $hub->publish($update);   
        return $this->json(['message' => 'Conversation créée avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/api/conversations', name: 'app_get_conversation', methods: ['GET'])]
    public function getConversations(
        EntityManagerInterface $em,
        ConversationRepository $conversationRepository,
        ): JsonResponse
    {   
        $conversations = $conversationRepository->findAll();
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
    return $this->json($data, Response::HTTP_OK);
    }

    #[Route('/api/conversation/{id}/message', name: 'app_send_message', methods: ['POST'])]
    public function sendMessaage(
        Request $request,
        EntityManagerInterface $em,
        ConversationRepository $conversationRepository,
        HubInterface $hub,
        $id
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if(empty($data['author']) || empty($data['message'])) {
            return $this->json(['error' => 'Champs manquants'], Response::HTTP_BAD_REQUEST);
        }

        $conversation = $conversationRepository->find($id);

        if(!$conversation) {
            return $this->json(['error' => 'Conversation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $content = $conversation->getContent() ?? [];
        $messages = $content['messages'] ?? [];

        $messages[] = [
            'author' => $data['author'],
            'message' => $data['message'],
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
        
        $content['messages'] = $messages;

        $conversation->setContent($content);

        $em->flush();

        $update = new Update(
            'http://localhost:8081/conversations/' . $conversation->getId(), 
            json_encode($messages)
            );
        $hub->publish($update);
        return $this->json(['message' => 'Message envoyé avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/update/conversation/{id}', name: 'app_update_conversation', methods: ['PUT'])]
    public function updateConversation(
        Request $request,
        EntityManagerInterface $em,
        ConversationRepository $conversationRepository,
        HubInterface $hub,
        $id
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $conversation = $conversationRepository->find($id);


        if (!$conversation) {
            return $this->json(['error' => 'Conversation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $conversation->setTitle($data['title']);
        $conversation->setDescription($data['description'] ?? null);
        if ($conversation->getStatus() !== ConversationStatusEnum::PUBLISHED) {
            $conversation->setStatus(ConversationStatusEnum::from($data['status']));
        }      
        $conversation->setContent($data['content']);
        $conversation->setIsPublic($data['is_public'] ?? true);
        $conversation->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();
        $update = new Update(
            'http://localhost:8081/conversations/' . $conversation->getId(), 
            json_encode([
                'id' => $conversation->getId(),
                'title' => $conversation->getTitle(),
                'description' => $conversation->getDescription(),
                'status' => $conversation->getStatus()->value,
                'content' => $conversation->getContent(),
                'is_public' => $conversation->isPublic(),
                'created_at' => $conversation->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $conversation->getUpdatedAt()->format('Y-m-d H:i:s'),
            ])
            );
        $hub->publish($update);
        return $this->json(['message' => 'Conversation mise à jour avec succès'], Response::HTTP_OK);
    }


    #[Route('/api/delete/conversation/{id}', name: 'app_delete_conversation', methods: ['DELETE'])]
    public function deleteConversation(
        EntityManagerInterface $em,
        ConversationRepository $conversationRepository,
        $id
    ): JsonResponse {
        $conversation = $conversationRepository->find($id);

        if (!$conversation) {
            return $this->json(['error' => 'Conversation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($conversation);
        $em->flush();

        return $this->json(['message' => 'Conversation supprimée avec succès'], Response::HTTP_OK);
    }
    

    #[Route('/api/conversation/{id}', name: 'app_get_conversation_by_id', methods: ['GET'])]
    public function getConversationById(
        ConversationRepository $conversationRepository,
        $id
    ): JsonResponse {
        $conversation = $conversationRepository->find($id);

        if (!$conversation) {
            return $this->json(['error' => 'Conversation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $conversation->getId(),
            'title' => $conversation->getTitle(),
            'description' => $conversation->getDescription(),
            'status' => $conversation->getStatus()->value,
            'content' => $conversation->getContent(),
            'is_public' => $conversation->isPublic(),
            'created_at' => $conversation->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $conversation->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return $this->json($data, Response::HTTP_OK);
    }

}
