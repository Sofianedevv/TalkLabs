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

final class ConversationController extends AbstractController
{
    #[Route('/api/conversation', name: 'app_conversation', methods: ['POST'])]
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

    #[Route('/api/conversations', name: 'app_get_conversation', methods: ['GET'])]
    public function getConversations(ConversationService $service): JsonResponse
    {
        return $this->json($service->getAllConversations(), Response::HTTP_OK);
    }

    #[Route('/api/update/conversation/{id}', name: 'app_update_conversation', methods: ['PUT'])]
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
            return $this->json(['message' => 'Conversation créée avec succès'], 201);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }


    #[Route('/api/delete/conversation/{id}', name: 'app_delete_conversation', methods: ['DELETE'])]
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
    

    #[Route('/api/conversation/{id}', name: 'app_get_conversation_by_id', methods: ['GET'])]
    public function getConversationById(
        ConversationService $service,
        $id
    ): JsonResponse {
        try{
            return $this->json($service->getConversationById($id), Response::HTTP_OK);
        }catch (\RuntimeException $e){
            return $this->json(['error' => $e->getMessage()], 400);
        }

    }

    #[Route('/api/reset/conversation/{id}', name: 'app_reset_conversation', methods: ['PUT'])]
    public function resetConversation(
        $id,
        ConversationService $conversationService,
    )
    {
        try {
            $conversationService->resetConversation($id);
            return $this->json(['message' => 'Conversation réinitialisée avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la réinitialisation de la conversation'], Response::HTTP_BAD_REQUEST);
        }
    }

}


