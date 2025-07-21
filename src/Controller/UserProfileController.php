<?php

namespace App\Controller;

use App\DTO\UpdatePasswordDTO;
use App\DTO\UserProfileEditDTO;
use App\Entity\Accounts;
use App\Service\UserProfileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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


    #[Route('/user/profile/edit', name: 'user_profile_edit', methods: ['POST'])]

    public function edit(Request $request): JsonResponse
    
    {
        try {
            $formData = $request->request->all();

            $dto = new UserProfileEditDTO(
                $formData['username'] ?? null,
                $formData['email'] ?? null
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
                'name' => $updateProfile->getName(),
                'username' => $updateProfile->getUsername(),
                'email' => $updateProfile->getEmail(),
                'avatarUrl' => $updateProfile->getAvatarUrl()
            ]
        ];
        return new JsonResponse($data, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/user/profile/password/update', name: 'password_profile_edit')]
    public function updatePassword(Request $request, UserPasswordHasherInterface $pwdHasher, EntityManagerInterface $em, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Accounts) {
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $dto = $serializer->deserialize($request->getContent(), UpdatePasswordDTO::class, 'json');

            $errors = $validator->validate($dto);
            if(count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], 400);
            }
            if ($dto->newPassword !== $dto->confirmPassword) {
                return $this->json(['message' => 'Les mots de passe ne correspond pas.'], Response::HTTP_BAD_REQUEST);
            }
            $hashedPassword = $pwdHasher->hashPassword($user, $dto->newPassword);
            $user->setPassword($hashedPassword);
            $em->persist($user);
            $em->flush();
            return $this->json(['message' => 'Mot de passe modifié avec succès.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}