<?php

namespace App\Controller;

use App\DTO\FavoriteConversationDTO;
use App\Service\FavoriteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/favorites')]
final class FavoriteController extends AbstractController
{
    private FavoriteService $favoriteService;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    
    public function __construct(FavoriteService $favoriteService, SerializerInterface $serializer, ValidatorInterface $validator){
        $this->favoriteService = $favoriteService;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }
    
    #[Route('/add/{conversationId}', name: 'app_add_favorite')]
    public function addFavorite($conversationId): JsonResponse
    {

        try {
            $this->favoriteService->addConversationToFavorite($conversationId);
            return $this->json(['message' => 'Conversation ajoutée aux favoris avec succès'], 200);
        } catch(\RuntimeException $e){
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/remove/{conversationId}', name: 'app_remove_favorite')]
    public function removeFromFavorites($conversationId): JsonResponse
    {


        try {
            $this->favoriteService->removeFavorites($conversationId);
            return $this->json(['message' => 'Conversation retirée des favoris avec succès'], 200);
        } catch(\RuntimeException $e){
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('', name: 'app_get_all_favorites')]
    public function getFavorites(): JsonResponse
    {
        try {
            $data = $this->favoriteService->getFavoritesOfUser();
            return $this->json($data, 200);
        } catch(\RuntimeException $e){
            return $this->json(['error' => $e->getMessage()], 401);
        }
    }

    
}
