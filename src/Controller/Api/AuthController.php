<?php

namespace App\Controller\Api;

use App\DTO\RegisterDTO;
use App\Dto\UpdatePasswordDto;
use App\Entity\Accounts;
use App\Enum\SubsciptionStatusEnum;
use App\Repository\AccountsRepository;
use App\Repository\RefreshTokenRepository;
use App\Service\RefreshTokenService;
use App\Service\TwoFactorService;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client as GoogleClient;
use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Uid\Uuid;

#[Route('/api', name: 'app_')]
class AuthController extends AbstractController
{

    private TwoFactorService $twoFactorService;

   public function __construct(TwoFactorService $twoFactorService) {
        $this->twoFactorService = $twoFactorService;
    }

    #[Route('/register', name: 'register', methods: ['POST', 'OPTIONS'])]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        AccountsRepository $accountsRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        Serializerinterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse([], Response::HTTP_OK);
        }

        try {
            $dto = $serializer->deserialize($request->getContent(), RegisterDTO::class, 'json');
            
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
            
            $existingUser = $accountsRepository->findOneBy(['email' => $dto->getEmail()]);
            if ($existingUser) {
                return $this->json([
                    'message' => 'Cette adresse email est déjà utilisée'
                ], Response::HTTP_CONFLICT);
            }
            
            $user = new Accounts();
            $user->setName($dto->getName());
            $user->setEmail($dto->getEmail());
            $user->setUsername($dto->getUsername());
            $hashedPassword = $passwordHasher->hashPassword($user, $dto->getPassword());
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
        LoggerInterface $logger,
        RefreshTokenService $refreshTokenService
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

            if($user->isTwoFactorEnabled()) {
                if (empty($data['totpCode'])) {
                    return $this->json(['message' => 'Code 2FA requis', 'isTwoFactorEnabled' => true], Response::HTTP_OK);
                }

                if(!$this->twoFactorService->validateTotpCodeAfterLogin($user, $data['totpCode'])) {
                    return $this->json(['message' => 'Code 2FA invalide'], Response::HTTP_UNAUTHORIZED);
                }
            }
            $token = $JWTManager->create($user);
            $refreshToken = $refreshTokenService->createRefreshToken($user);
            //Si on passe le JWT via un cookie à voir
            $jwtCookie = Cookie::create('BEARER')
                ->withValue($token)
                ->withExpires(new \DateTime('+1 hour'))
                ->withPath('/')
                ->withSecure()
                ->withHttpOnly()
                ->withSameSite('Strict');

            $refreshTokenCookie = Cookie::create('REFRESH_TOKEN')
                ->withValue($refreshToken->getRefreshToken())
                ->withExpires(new \DateTime('+2 days'))
                ->withPath('/')
                ->withSecure()
                ->withHttpOnly()
                ->withSameSite('Strict');

            $response = $this->json([
                'message' => 'Connexion réussie',
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'isTwofactorEnabled' => $user->isTwoFactorEnabled()

                ]
            ]);

            $response->headers->setCookie($jwtCookie);
            $response->headers->setCookie($refreshTokenCookie);

            return $response;
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
             $user = $this->getUser();
             if(!$user instanceof Accounts) {
                return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
             }

            $currentSubscription = $user->getCurrentSubscription();
            error_log('DEBUG /me - currentSubscription: ' . ($currentSubscription ? 'exists' : 'null'));
            if ($currentSubscription) {
                error_log('DEBUG /me - subscription status: ' . $currentSubscription->getStatus()->value);
            }
            $hasSubscription = $currentSubscription !== null && $currentSubscription->getStatus() === SubsciptionStatusEnum::ACTIVE;
            $subscriptionStatus = $currentSubscription ? $currentSubscription->getStatus()->value : null;
            error_log('DEBUG /me - hasSubscription: ' . ($hasSubscription ? 'true' : 'false'));
            error_log('DEBUG /me - subscriptionStatus: ' . ($subscriptionStatus ?? 'null'));
            
            return $this->json([
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'avatarUrl' => $user->getAvatarUrl(),
                    'role' => $user->getRole(),
                    'isTwofactorEnabled' => $user->isTwoFactorEnabled(),
                    'hasSubscription' => $hasSubscription,
                    'subscriptionStatus' => $subscriptionStatus
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

    #[Route('/login-google', name:"login-google")]
    public function authWithGoogle(
        Request $request,
        AccountsRepository $accountsRepository,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwt,
        RefreshTokenService $refreshTokenService,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['id_token'])) {
            return $this->json(['message' => 'Access token manquant'], Response::HTTP_BAD_REQUEST);
        }

        $idToken = $data['id_token'];

        $googleClient = new GoogleClient(['client_id' => $_ENV['GOOGLE_CLIENT_ID'],]);
        $payload = $googleClient->verifyIdToken($idToken);
        if (!$payload) {
            return $this->json(['message' => 'Token invalide'], Response::HTTP_UNAUTHORIZED);
        }

        $email = $payload['email'] ?? null;
        if (!$email) {
            return $this->json(['message' => 'Email manquant dans le token'], Response::HTTP_BAD_REQUEST);
        }

        $user = $accountsRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $user = new Accounts();
            $user->setEmail($email);
            $user->setName($payload['name'] ?? '');
            $user->setUsername($payload['given_name'] ?? 'utilisateur_google_' . Uuid::v4());
            $user->setRole(['ROLE_USER']);
            $user->setIsVerified(true);
            $user->setCreatedAt(new \DateTimeImmutable());

            $randomPassword = bin2hex(random_bytes(20));
            $hashedPassword = $passwordHasher->hashPassword($user, $randomPassword);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();
        }

            $token = $jwt->create($user);
            $refreshToken = $refreshTokenService->createRefreshToken($user);


            //Si on passe le JWT via un cookie à voir
            $jwtCookie = Cookie::create('BEARER')
                ->withValue($token)
                ->withExpires(new \DateTime('+1 hour'))
                ->withPath('/')
                ->withSecure()
                ->withHttpOnly(true)
                ->withSameSite('Strict');

            $refreshTokenCookie = Cookie::create('REFRESH_TOKEN')
                ->withValue($refreshToken->getRefreshToken())
                ->withExpires(new \DateTime('+2 days'))
                ->withPath('/')
                ->withSecure()
                ->withHttpOnly(true)
                ->withSameSite('Strict');

            $response = $this->json([
                'message' => 'Connexion réussie',
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername()
                ]
            ]);

            $response->headers->setCookie($jwtCookie);
            $response->headers->setCookie($refreshTokenCookie);

            return $response;


    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(
        Request $request,
        RefreshTokenRepository $refreshTokenRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $tokenValue = $request->cookies->get('REFRESH_TOKEN');

        if (!$tokenValue) {
            return $this->json(['message' => 'Refresh token manquant'], Response::HTTP_BAD_REQUEST);
        }

        $refreshToken = $refreshTokenRepository->findOneBy(['refreshToken' => $tokenValue]);
        
        if (!$refreshToken) {
            return $this->json(['message' => 'Refresh token invalide'], Response::HTTP_UNAUTHORIZED);
        }

        $refreshToken->setIsRevoked(true);
        $entityManager->persist($refreshToken);
        $entityManager->flush();

        // Supprimer les cookies
        $response = new JsonResponse(['message' => 'Déconnexion réussie']);
        // Alternative : forcer l'expiration des cookies
        $response->headers->clearCookie('BEARER', '/',);
        $response->headers->clearCookie('REFRESH_TOKEN', '/');
        return $response;
    }
} 