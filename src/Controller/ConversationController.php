<?php

namespace App\Controller;

use App\DTO\ConversationDTO;
use App\DTO\ConversationEditDTO;
use App\Entity\Conversation;
use App\Repository\AccountsRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\ConversationStatusEnum;
use App\Repository\ConversationRepository;
use App\Service\ConversationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'app_')]
final class ConversationController extends AbstractController
{
    #[Route('/create-conversation', name: 'conversation', methods: ['POST'])]
    public function createConversation(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ConversationService $service
    ): JsonResponse {
        $dto = $serializer->deserialize($request->getContent(), ConversationDTO::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }
        try {
           $service->createConversation($dto);
            return $this->json(['message' => 'Conversation créée avec succès'], 201);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/get-conversations-public', name: 'get_public_conversation', methods: ['GET'])]
    public function getConversations(ConversationService $service): JsonResponse
    {
        try {
            return $this->json($service->getAllPublicConversations(), Response::HTTP_OK);
        }catch (\RuntimeException $e){
            return $this->json(['error' => $e->getMessage()], 400);
        }

    }

    #[Route('/update-conversation/{id}', name: 'update_conversation', methods: ['PUT'])]
    public function updateConversation(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ConversationService $service,
        $id
    ): JsonResponse {

        $dto = $serializer->deserialize($request->getContent(), ConversationEditDTO::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }
        try {
            $service->editMessage($id, $dto);
            return $this->json(['message' => 'Conversation modifié avec succès'], 201);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }


    #[Route('/delete-conversation/{id}', name: 'delete_conversation', methods: ['DELETE'])]
    public function deleteConversation(
        ConversationService $service,
        $id
    ): JsonResponse {
        try{
            $service->deleteConversation($id);
            return $this->json(['message' => 'Conversation supprimé avec succès'], 201);
        }catch (\RuntimeException $e){
            return $this->json(['error' => $e->getMessage()], 400);

        }
    }
    

    #[Route('/get-conversation/{id}', name: 'get_conversation_by_id', methods: ['GET'])]
    public function getConversationById(
        ConversationService $service,
        $id
    ): JsonResponse {
        try{
            return $this->json($service->getConversationById($id), Response::HTTP_OK);
        } catch (\RuntimeException $e){
            return $this->json(['error' => $e->getMessage()], 400);
        }

    }

    #[Route('/get-conversations-by-user', name: 'get_conversation_by_user', methods: ['GET'])]
    public function getConversationByUser(ConversationService $service): JsonResponse {
        try{
            return $this->json($service->getConversationsByUser(), Response::HTTP_OK);
        }catch (\RuntimeException $e){
            return $this->json(['error' => $e->getMessage()], 400);
        }

    }

    #[Route('/get-conversations-by-category/{categoryId}', name: 'get_conversation_by_category', methods: ['GET'])]
    public function getPublicConversationByCategory(ConversationService $conversationService, $categoryId) : JsonResponse {
        try {
            return $this->json($conversationService->getPublicConversationsByCategory($categoryId), Response::HTTP_OK);

        } catch (\RuntimeException $e){
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }



    #[Route('/messages/upload', name: 'upload_media', methods: ['POST', 'OPTIONS'])]
    public function upload(Request $request, ConversationService $service): JsonResponse
    {
        $result = $service->uploadMedia($request);
        return new JsonResponse($result);
    }
}

