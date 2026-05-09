<?php 

namespace App\EventListener;

use App\Repository\RefreshTokenRepository;
use App\Service\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTRefreshTokenListener {


    private RefreshTokenRepository $refreshTokenRepository;
    private RefreshTokenService $refreshTokenService;
    private JWTTokenManagerInterface $jwtManager;
    private EntityManagerInterface $em;
    private Security $security;
    private TokenStorageInterface $tokenStorage;


        public function __construct(
            RefreshTokenRepository $refreshTokenRepository,
            RefreshTokenService $refreshTokenService,
            JWTTokenManagerInterface $jwtManager,
            EntityManagerInterface $em,
            Security $security,
            TokenStorageInterface $tokenStorage
        ) {
            $this->refreshTokenRepository = $refreshTokenRepository;
            $this->refreshTokenService = $refreshTokenService;
            $this->jwtManager = $jwtManager;
            $this->em = $em;
            $this->security = $security;
            $this->tokenStorage = $tokenStorage;
        }


        public function onKernelRequest(RequestEvent $event): void {

            $request = $event->getRequest();

            if(!str_starts_with($request->getPathInfo(), '/api')) {
                return;
            }

            if($request->getMethod() === 'OPTIONS') {
                return;
            }

            $refreshTokenValue = $request->cookies->get('REFRESH_TOKEN');
            
            if(!$refreshTokenValue) {
                return;
            }

            $refreshToken = $this->refreshTokenRepository->findOneBy(['refreshToken' => $refreshTokenValue]);
            
            if (!$refreshToken || $refreshToken->isRevoked() || $refreshToken->getExpiresAt() < new \DateTimeImmutable()) {
            return;
            }

            $user = $refreshToken->getAccount();   
            
            if(!$user instanceof UserInterface) {
                return;
            }

            $token = $request->cookies->get('BEARER');
            if ($token) {
                try {
                    $this->jwtManager->parse($token);
                    $this->authenticatedUser($user);

                } catch(\Exception $e) {

                }
            }

            $refreshToken->setIsRevoked(true);
            $this->em->persist($refreshToken);

            $newRefreshToken = $this->refreshTokenService->createRefreshToken($user);
            $refreshJwt = $this->jwtManager->create($user);

            $this->em->flush();

            $this->authenticatedUser($user);

             $request->attributes->set('new_jwt', $refreshJwt);
             $request->attributes->set('new_refresh_token', $newRefreshToken->getRefreshToken());
        }

        public function onKernelResponse(ResponseEvent $event): void {

            $request = $event->getRequest();
            $response = $event->getResponse();
            
            $newJwt = $request->attributes->get('new_jwt');
            $newRefreshToken = $request->attributes->get('new_refresh_token');

            if (!$newJwt || !$newRefreshToken) {
                return;
            }

            $jwtCookie = Cookie::create('BEARER')
                ->withValue($newJwt)
                ->withExpires(new \DateTime('+1 hour'))
                ->withPath('/')
                ->withSecure(false)
                ->withHttpOnly(true)
                ->withSameSite('Strict');

            $refreshTokenCookie = Cookie::create('REFRESH_TOKEN')
                ->withValue($newRefreshToken)
                ->withExpires(new \DateTime('+2 days'))
                ->withPath('/')
                ->withSecure(false) 
                ->withHttpOnly(true)
                ->withSameSite('Strict');

            $response->headers->setCookie($jwtCookie);
            $response->headers->setCookie($refreshTokenCookie);

        }

        private function authenticatedUser(UserInterface $user): void {
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);
        }
}

