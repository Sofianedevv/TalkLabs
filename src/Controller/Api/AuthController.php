<?php

namespace App\Controller\Api;

use App\Entity\Accounts;
use App\Repository\AccountsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/api', name: 'api_')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST', 'OPTIONS'])]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): JsonResponse {
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse([], Response::HTTP_OK);
        }

        try {
            $data = json_decode($request->getContent(), true);
            
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                return $this->json([
                    'message' => 'Données manquantes pour l\'inscription'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $existingUser = $entityManager->getRepository(Accounts::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return $this->json([
                    'message' => 'Cette adresse email est déjà utilisée'
                ], Response::HTTP_CONFLICT);
            }
            
            $user = new Accounts();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            
            if (!empty($data['username'])) {
                $user->setUsername($data['username']);
            }
            
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
            
            $user->setRole(['ROLE_USER']);
            $user->setIsVerified(false);
            $user->setCreatedAt(new \DateTimeImmutable());
            
            $entityManager->persist($user);
            $entityManager->flush();
            
            return $this->json([
                'message' => 'Utilisateur créé avec succès',
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail()
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $logger->error('Erreur lors de l\'inscription: ' . $e->getMessage());
            return $this->json([
                'message' => 'Erreur lors de l\'inscription',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/login', name: 'login', methods: ['POST', 'OPTIONS'])]
    public function login(
        Request $request, 
        AccountsRepository $accountsRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $JWTManager,
        LoggerInterface $logger
    ): JsonResponse {
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse([], Response::HTTP_OK);
        }

        try {
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'message' => 'Erreur de décodage JSON: ' . json_last_error_msg()
                ], Response::HTTP_BAD_REQUEST);
            }
            
            if (!isset($data['email']) || !isset($data['password'])) {
                return $this->json([
                    'message' => 'Email et mot de passe requis'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $user = $accountsRepository->findOneBy(['email' => $data['email']]);
            
            if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
                return $this->json([
                    'message' => 'Identifiants invalides'
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            $token = $JWTManager->create($user);
            
            return $this->json([
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername()
                ]
            ]);
        } catch (\Exception $e) {
            $logger->error('Erreur lors de la connexion: ' . $e->getMessage());
            return $this->json([
                'message' => 'Erreur lors de la connexion',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(Request $request, AccountsRepository $accountsRepository): JsonResponse
    {
        try {
            $authHeader = $request->headers->get('Authorization');
            
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->json(['message' => 'Token manquant ou invalide'], Response::HTTP_UNAUTHORIZED);
            }
            
            $token = str_replace('Bearer ', '', $authHeader);
            $tokenParts = explode('.', $token);
            if (count($tokenParts) !== 3) {
                return $this->json(['message' => 'Format de token invalide'], Response::HTTP_UNAUTHORIZED);
            }
            
            $payload = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', $tokenParts[1]))), true);
            
            if (!isset($payload['email'])) {
                return $this->json(['message' => 'Token invalide: email manquant'], Response::HTTP_UNAUTHORIZED);
            }
            
            $user = $accountsRepository->findOneBy(['email' => $payload['email']]);
            if (!$user) {
                return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_UNAUTHORIZED);
            }
            
            return $this->json([
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return $this->json([
            'message' => 'API is working',
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
    }
} 