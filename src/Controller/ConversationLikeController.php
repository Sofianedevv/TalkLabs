<?php

namespace App\Controller;

use App\DTO\ConversationLikeDTO;
use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\Service\ConversationLikeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name:'app_')]
final class ConversationLikeController extends AbstractController
{
    #[Route('/conversation/like', name: 'app_conversation_like')]
    public function addLike(Request $request, SerializerInterface $serializer,ValidatorInterface $validator, ConversationLikeService $conversationLikeService): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), ConversationLikeDTO::class, 'json');
        $errors = $validator->validate($dto);
        if(count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        try {
            $conversationLikeService->addLike($dto);
            return $this->json(['message' => 'Vous avez liker la conversation']);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/conversation/remove-like', name: 'app_conversation_remove-like')]
    public function removeLikeConversation(Request $request, SerializerInterface $serializer,ValidatorInterface $validator, ConversationLikeService $conversationLikeService, ConversationRepository $conversationRepository): JsonResponse
     {
        $dto = $serializer->deserialize($request->getContent(), ConversationLikeDTO::class, 'json');
        $errors = $validator->validate($dto);
        if(count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        try {
             $conversation = $conversationRepository->find($dto->getConversationId());
            $conversationLikeService->removeLike($conversation);
            return $this->json(['message' => 'Votre like a été retiré pour cette conversation']);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }


    #[Route('/conversation/{id}/likes', name: 'conversation_like_number')]
    public function getNbLikes(Conversation $conversation, ConversationLikeService $conversationLikeService): JsonResponse
    {
       try {      
            return $this->json($conversationLikeService->countLikes($conversation), Response::HTTP_OK);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
     #[Route('/conversation/{id}/likers', name: 'conversation_like_accounts', methods: ['GET'])]
    public function getAccountsLiked(
        Conversation $conversation,
        ConversationLikeService $conversationLikeService
    ): JsonResponse {
       try {    
        
            return $this->json($conversationLikeService->getAccountsLiked($conversation), Response::HTTP_OK);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }    }


}
