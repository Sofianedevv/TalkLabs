<?php

namespace App\Controller;

use App\DTO\UserProfileEditDTO;
use App\Service\UserProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'app_')]
final class UserProfileController extends AbstractController
{
    private UserProfileService $userProfileService;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;

    public function __construct(UserProfileService $userProfileService, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $this->userProfileService = $userProfileService;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }


    #[Route('/user/profile/edit', name: 'app_user_profile_edit', methods: ['POST'])]

    public function edit(Request $request): JsonResponse
    
    {
        try {
            $formData = $request->request->all();

            $dto = new UserProfileEditDTO(
                $formData['username'] ?? null,
                $formData['email'] ?? null,
            );
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $updateProfile = $this->userProfileService->updateUserProfile($dto, $request);
            $data = [
            'message' => 'Profil mis à jour avec succès.',
            'updatedUser' => [
                'id' => $updateProfile->getId(),
                'name' => $updateProfile->getName(),
                'username' => $updateProfile->getUsername(),
                'email' => $updateProfile->getEmail(),
                'avatarUrl' => $updateProfile->getAvatarUrl(),
            ]
        ];

        return new JsonResponse($data, Response::HTTP_OK);

        
        
        
        
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

            return new JsonResponse(['message' => 'Profil mis à jour avec succès.'], Response::HTTP_OK);


        }
    }
