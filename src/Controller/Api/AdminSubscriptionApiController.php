<?php

namespace App\Controller\Api;

use App\Entity\Plan;
use App\Repository\PaymentRepository;
use App\Repository\PlanRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Subscription;
use App\Entity\Accounts;

#[Route('/api/admin/subscription')]
#[IsGranted('ROLE_ADMIN')]
class AdminSubscriptionApiController extends AbstractController
{
    #[Route('/plans', name: 'api_admin_subscription_plans', methods: ['GET'])]
    public function getPlans(PlanRepository $planRepository): JsonResponse
    {
        $plans = $planRepository->findAll();
        
        $plansData = [];
        foreach ($plans as $plan) {
            $plansData[] = [
                'id' => $plan->getId(),
                'name' => $plan->getName(),
                'price' => $plan->getPrice(),
                'description' => $plan->getDescription(),
                'subscriptions' => $plan->getSubscriptions()->map(function($subscription) {
                    return [
                        'id' => $subscription->getId(),
                        'status' => $subscription->getStatus()->value
                    ];
                })->toArray()
            ];
        }
        
        return $this->json([
            'plans' => $plansData
        ]);
    }
    
    #[Route('/plans', name: 'api_admin_subscription_plan_create', methods: ['POST'])]
    public function createPlan(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $content = $request->getContent();
        $plan = $serializer->deserialize($content, Plan::class, 'json');
        
        $errors = $validator->validate($plan);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return $this->json(['message' => $errorsString], Response::HTTP_BAD_REQUEST);
        }
        
        $entityManager->persist($plan);
        $entityManager->flush();
        
        return $this->json([
            'message' => 'Plan créé avec succès',
            'plan' => $plan
        ], Response::HTTP_CREATED, [], [
            'groups' => ['plan:read']
        ]);
    }
    
    #[Route('/plans/{id}', name: 'api_admin_subscription_plan_update', methods: ['PUT'])]
    public function updatePlan(
        Plan $plan,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $content = $request->getContent();
        $updatedPlan = $serializer->deserialize($content, Plan::class, 'json');
        
        $plan->setName($updatedPlan->getName());
        $plan->setPrice($updatedPlan->getPrice());
        $plan->setDescription($updatedPlan->getDescription());
        
        $errors = $validator->validate($plan);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return $this->json(['message' => $errorsString], Response::HTTP_BAD_REQUEST);
        }
        
        $entityManager->flush();
        
        return $this->json([
            'message' => 'Plan mis à jour avec succès',
            'plan' => $plan
        ], Response::HTTP_OK, [], [
            'groups' => ['plan:read']
        ]);
    }
    
    #[Route('/plans/{id}', name: 'api_admin_subscription_plan_delete', methods: ['DELETE'])]
    public function deletePlan(Plan $plan, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$plan->getSubscriptions()->isEmpty()) {
            return $this->json([
                'message' => 'Ce plan ne peut pas être supprimé car il est utilisé par des abonnements actifs.'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $entityManager->remove($plan);
        $entityManager->flush();
        
        return $this->json([
            'message' => 'Plan supprimé avec succès'
        ], Response::HTTP_OK);
    }
    
    #[Route('/subscriptions', name: 'api_admin_subscriptions', methods: ['GET'])]
    public function getSubscriptions(SubscriptionRepository $subscriptionRepository): JsonResponse
    {
        $subscriptions = $subscriptionRepository->findAll();
        
        $subscriptionsData = [];
        foreach ($subscriptions as $subscription) {
            $plan = $subscription->getPlan();
            $users = $subscription->getUsers();
            
            $subscriptionsData[] = [
                'id' => $subscription->getId(),
                'duration' => $subscription->getDuration(),
                'status' => $subscription->getStatus()->value,
                'plan' => [
                    'id' => $plan->getId(),
                    'name' => $plan->getName(),
                    'price' => $plan->getPrice(),
                    'description' => $plan->getDescription()
                ],
                'users' => $users->map(function($user) {
                    return [
                        'id' => $user->getId(),
                        'name' => $user->getName(),
                        'email' => $user->getEmail()
                    ];
                })->toArray(),
                'subscriptionHistories' => $subscription->getSubscriptionHistories()->map(function($history) {
                    return [
                        'id' => $history->getId(),
                        'startAt' => $history->getStartAt()?->format('Y-m-d H:i:s'),
                        'endAt' => $history->getEndAt()?->format('Y-m-d H:i:s')
                    ];
                })->toArray(),
                'payments' => $subscription->getPayments()->map(function($payment) {
                    return [
                        'id' => $payment->getId(),
                        'amount' => $payment->getAmount(),
                        'status' => $payment->getStatus(),
                        'createdAt' => $payment->getCreatedAt()?->format('Y-m-d H:i:s'),
                        'paymentMethod' => $payment->getPaymentMethod()
                    ];
                })->toArray()
            ];
        }
        
        return $this->json([
            'subscriptions' => $subscriptionsData
        ], Response::HTTP_OK);
    }
    
    #[Route('/payments', name: 'api_admin_payments', methods: ['GET'])]
    public function getPayments(PaymentRepository $paymentRepository): JsonResponse
    {
        $payments = $paymentRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->json([
            'payments' => $payments
        ], Response::HTTP_OK, [], [
            'groups' => ['payment:read', 'subscription:read', 'plan:read']
        ]);
    }
    
    #[Route('/reports', name: 'api_admin_reports', methods: ['GET'])]
    public function getReports(
        SubscriptionRepository $subscriptionRepository,
        PaymentRepository $paymentRepository
    ): JsonResponse {
        $activeSubscriptions = $subscriptionRepository->countActiveSubscriptions();
        $currentMonthRevenue = $paymentRepository->getCurrentMonthRevenue();
        $conversionRate = $subscriptionRepository->getConversionRate();
        
        $monthlyStats = $paymentRepository->getMonthlyStats();
        
        $planDistribution = $paymentRepository->getRevenueByPlan();
        
        return $this->json([
            'activeSubscriptions' => $activeSubscriptions,
            'currentMonthRevenue' => $currentMonthRevenue,
            'conversionRate' => $conversionRate,
            'monthlyStats' => $monthlyStats,
            'planRevenue' => $planDistribution
        ], Response::HTTP_OK);
    }

    #[Route('/subscriptions/{id}/cancel', name: 'api_admin_subscription_cancel', methods: ['POST'])]
    public function cancelUserSubscription(
        Subscription $subscription,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            if ($subscription->getStatus()->value === 'canceled') {
                return $this->json([
                    'message' => 'Cet abonnement est déjà annulé.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $subscription->setStatus(\App\Enum\SubsciptionStatusEnum::CANCELED);
            $entityManager->flush();

            return $this->json([
                'message' => 'Abonnement annulé avec succès'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/subscriptions/{id}', name: 'api_admin_subscription_delete', methods: ['DELETE'])]
    public function deleteUserSubscription(
        Subscription $subscription,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $entityManager->createQuery(
                'UPDATE App\Entity\Accounts a SET a.currentSubscription = NULL WHERE a.currentSubscription = :subscription'
            )
            ->setParameter('subscription', $subscription)
            ->execute();

            $entityManager->remove($subscription);
            $entityManager->flush();

            return $this->json([
                'message' => 'Abonnement supprimé avec succès'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/users/{id}/subscription', name: 'api_admin_user_subscription', methods: ['GET'])]
    public function getUserSubscription(
        int $id,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $userRepository = $entityManager->getRepository(\App\Entity\Accounts::class);
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $currentSubscription = $user->getCurrentSubscription();
        
        if (!$currentSubscription) {
            return $this->json(['message' => 'Cet utilisateur n\'a pas d\'abonnement actif'], Response::HTTP_NOT_FOUND);
        }

        $plan = $currentSubscription->getPlan();
        $subscriptionData = [
            'id' => $currentSubscription->getId(),
            'duration' => $currentSubscription->getDuration(),
            'status' => $currentSubscription->getStatus()->value,
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail()
            ],
            'plan' => [
                'id' => $plan->getId(),
                'name' => $plan->getName(),
                'price' => $plan->getPrice(),
                'description' => $plan->getDescription()
            ]
        ];

        return $this->json($subscriptionData, Response::HTTP_OK);
    }

    #[Route('/users', name: 'api_admin_users_list', methods: ['GET'])]
    public function getUsers(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $userRepository = $entityManager->getRepository(\App\Entity\Accounts::class);
            $users = $userRepository->findAll();

            $usersData = [];
            foreach ($users as $user) {
                $currentSubscription = $user->getCurrentSubscription();
                $usersData[] = [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'hasSubscription' => $currentSubscription !== null,
                    'subscriptionStatus' => $currentSubscription ? $currentSubscription->getStatus()->value : null,
                    'planName' => $currentSubscription && $currentSubscription->getPlan() ? $currentSubscription->getPlan()->getName() : null
                ];
            }

            return $this->json([
                'users' => $usersData,
                'total' => count($usersData),
                'debug' => 'Users endpoint working'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur serveur: ' . $e->getMessage(),
                'users' => [],
                'debug' => 'Error in users endpoint'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 