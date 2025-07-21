<?php

namespace App\Controller;

use App\Entity\Accounts;
use App\Service\TwoFactorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/twofactor', name: 'app_')]
final class TwoFactorController extends AbstractController
{

    private TwoFactorService $twoFactorService;
    private Security $security;

    public function __construct(TwoFactorService $twoFactorService, Security $security) {
        $this->twoFactorService = $twoFactorService;
        $this->security = $security;
    }

    #[Route('/config', name: 'config2FA')]
    public function config2FA(): JsonResponse
    {
        $user = $this->security->getUser();

        if(!$user instanceof Accounts) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $secret = $this->twoFactorService->generateSecretKey();
        $url = $this->twoFactorService->generateOtpAuthUrl($user, $secret);

        return new JsonResponse([
            'secret' => $secret,
            'otpauth_url' => $url,
        ]);
    }

    #[Route('/enable', name: 'enable')]
    public function enable2FA(Request $request) : JsonResponse {
        $user = $this->security->getUser();

        if (!$user) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $data = json_decode($request->getContent(), true);
        $secret = $data['secret'] ?? null;
        $code = $data['code'] ?? null;

        if(!$secret || !$code) {
            return $this->json(['message' => 'Secret et code requis'], 400);
        }

        if(!$this->twoFactorService->verifyTotpCode($secret, $code)) {
            return $this->json(['message' => 'Code invalide avec ce secret'], 401);
        }

        $this->twoFactorService->enable2FA($user, $secret);

        return $this->json(['message' => 'le 2FA est maintenant active'], 200);
    }

     #[Route('/disable', name: 'disable2FA')] 
     public function disable2FA(): JsonResponse {

        $user = $this->security->getUser();
        if (!$user) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $this->twoFactorService->disable2FA($user);
        return $this->json(['message' => 'le 2FA est maintenant innactif'], 200);
     }



    #[Route('/check', name: 'check')]
    public function verify2Fa(Request $request) : JsonResponse
     {

        $user = $this->security->getUser();
        
        if (!$user) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }
        
        $data = json_decode($request->getContent(), true);
        
        $code = $data['code'] ?? '';
        if(!$code) {
            return $this->json(['message' => 'Code requis'], 400);
        }

        $secret = $this->twoFactorService->getTotpSecret($user);

        if($secret === null) {
            return $this->json(['message' => 'le 2FA est maintenant innactif'], 200);
        }

        if ($this->twoFactorService->verifyTotpCode($secret, $code)) {
            return new JsonResponse(['success' => true, 'message' => 'Code valide']);
        }
        return new JsonResponse(['success' => false, 'message' => 'Code invalide'], 401);

    }


}
