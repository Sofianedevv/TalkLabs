<?php

namespace App\Controller;

use App\Entity\Plan;
use App\Entity\Subscription;
use App\Entity\Accounts;
use App\Repository\PaymentRepository;
use App\Repository\PlanRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }
    
    #[Route('/plans', name: 'admin_plans')]
    public function plans(PlanRepository $planRepository): Response
    {
        $plans = $planRepository->findAll();
        
        return $this->render('admin/plans.html.twig', [
            'plans' => $plans
        ]);
    }
    
    #[Route('/subscriptions', name: 'admin_subscriptions')]
    public function subscriptions(SubscriptionRepository $subscriptionRepository): Response
    {
        $subscriptions = $subscriptionRepository->findAll();
        
        return $this->render('admin/subscriptions.html.twig', [
            'subscriptions' => $subscriptions
        ]);
    }
    
    #[Route('/users', name: 'admin_users')]
    public function users(EntityManagerInterface $entityManager): Response
    {
        $userRepository = $entityManager->getRepository(Accounts::class);
        $users = $userRepository->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }
    
    #[Route('/reports', name: 'admin_reports')]
    public function reports(
        SubscriptionRepository $subscriptionRepository,
        PaymentRepository $paymentRepository
    ): Response {
        $activeSubscriptions = $subscriptionRepository->countActiveSubscriptions();
        $currentMonthRevenue = $paymentRepository->getCurrentMonthRevenue();
        
        return $this->render('admin/reports.html.twig', [
            'activeSubscriptions' => $activeSubscriptions,
            'currentMonthRevenue' => $currentMonthRevenue
        ]);
    }

    #[Route('/subscription/plans', name: 'admin_subscription_plans_json', methods: ['GET'])]
    public function getPlansJson(PlanRepository $planRepository): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user instanceof Accounts || !in_array('ROLE_ADMIN', $user->getRole())) {
                return $this->json(['message' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
            }

            $plans = $planRepository->findAll();
            
            $debugInfo = [
                'total_plans' => count($plans),
                'user_role' => $user->getRole(),
                'user_id' => $user->getId()
            ];
            
            $plansData = [];
            foreach ($plans as $plan) {
                $planData = [
                    'id' => $plan->getId(),
                    'name' => $plan->getName(),
                    'price' => $plan->getPrice(),
                    'description' => $plan->getDescription(),
                ];
                
                $debugInfo['plan_' . $plan->getId()] = [
                    'id' => $plan->getId(),
                    'name' => $plan->getName(),
                    'price' => $plan->getPrice(),
                    'description' => $plan->getDescription(),
                    'class' => get_class($plan)
                ];
                
                $plansData[] = $planData;
            }
            
            return $this->json([
                'plans' => $plansData,
                'debug' => $debugInfo
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur serveur: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 