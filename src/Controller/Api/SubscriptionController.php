<?php

namespace App\Controller\Api;

use App\Entity\Accounts;
use App\Entity\Plan;
use App\Entity\Subscription;
use App\Enum\SubsciptionStatusEnum;
use App\Repository\PlanRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/subscription')]
class SubscriptionController extends AbstractController
{
    #[Route('/plans', name: 'api_subscription_plans', methods: ['GET'])]
    public function getPlans(PlanRepository $planRepository): JsonResponse
    {
        try {
            $plans = $planRepository->findAll();
            
            $plansData = [];
            foreach ($plans as $plan) {
                $plansData[] = [
                    'id' => $plan->getId(),
                    'name' => $plan->getName(),
                    'price' => $plan->getPrice(),
                    'description' => $plan->getDescription()
                ];
            }
            
            return $this->json($plansData);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la récupération des plans',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/current', name: 'api_subscription_current', methods: ['GET'])]
    public function getCurrentSubscription(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user instanceof Accounts) {
                return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
            }

            $currentSubscription = $user->getCurrentSubscription();
            if (!$currentSubscription) {
                return $this->json(['message' => 'Aucun abonnement trouvé'], Response::HTTP_NOT_FOUND);
            }

            $plan = $currentSubscription->getPlan();
            return $this->json([
                'id' => $currentSubscription->getId(),
                'status' => $currentSubscription->getStatus()->value,
                'duration' => $currentSubscription->getDuration(),
                'plan' => [
                    'id' => $plan->getId(),
                    'name' => $plan->getName(),
                    'price' => $plan->getPrice(),
                    'description' => $plan->getDescription()
                ],
                'startAt' => (new \DateTime())->format('Y-m-d'),
                'endAt' => (new \DateTime('+' . $currentSubscription->getDuration() . ' days'))->format('Y-m-d')
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la récupération de l\'abonnement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/debug', name: 'api_subscription_debug', methods: ['GET'])]
    public function debug(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user instanceof Accounts) {
                return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
            }

            $currentSubscription = $user->getCurrentSubscription();
            
            return $this->json([
                'user_id' => $user->getId(),
                'user_email' => $user->getEmail(),
                'has_subscription' => $currentSubscription !== null,
                'subscription_id' => $currentSubscription ? $currentSubscription->getId() : null,
                'subscription_status' => $currentSubscription ? $currentSubscription->getStatus()->value : null,
                'debug' => 'User subscription debug info'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors du debug',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/subscribe/{planId}', name: 'api_subscription_subscribe', methods: ['POST'])]
    public function subscribe(
        int $planId,
        PlanRepository $planRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $user = $this->getUser();
            if (!$user instanceof Accounts) {
                return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
            }

            $plan = $planRepository->find($planId);
            if (!$plan) {
                return $this->json(['message' => 'Plan non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $existingSubscription = $user->getCurrentSubscription();
            if ($existingSubscription && $existingSubscription->getStatus() === SubsciptionStatusEnum::ACTIVE) {
                return $this->json(['message' => 'Vous avez déjà un abonnement actif'], Response::HTTP_BAD_REQUEST);
            }

            $subscription = new Subscription();
            $subscription->setPlan($plan);
            $subscription->setStatus(SubsciptionStatusEnum::ACTIVE);
            $subscription->setDuration(30); 
            $subscription->addUser($user);

            $entityManager->persist($subscription);
            
            $user->setCurrentSubscription($subscription);
            $entityManager->persist($user);
            
            $entityManager->flush();

            return $this->json([
                'message' => 'Abonnement créé avec succès',
                'subscription' => [
                    'id' => $subscription->getId(),
                    'status' => $subscription->getStatus()->value,
                    'plan' => [
                        'name' => $plan->getName(),
                        'price' => $plan->getPrice()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la création de l\'abonnement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/change/{planId}', name: 'api_subscription_change', methods: ['POST'])]
    public function changeSubscription(
        int $planId,
        PlanRepository $planRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $user = $this->getUser();
            if (!$user instanceof Accounts) {
                return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
            }

            $newPlan = $planRepository->find($planId);
            if (!$newPlan) {
                return $this->json(['message' => 'Plan non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $currentSubscription = $user->getCurrentSubscription();
            if (!$currentSubscription) {
                return $this->json(['message' => 'Aucun abonnement actif trouvé'], Response::HTTP_NOT_FOUND);
            }

            $currentSubscription->setStatus(SubsciptionStatusEnum::CANCELED);
            $entityManager->persist($currentSubscription);

            $newSubscription = new Subscription();
            $newSubscription->setPlan($newPlan);
            $newSubscription->setStatus(SubsciptionStatusEnum::ACTIVE);
            $newSubscription->setDuration(30);
            $newSubscription->addUser($user);

            $entityManager->persist($newSubscription);
            
            $user->setCurrentSubscription($newSubscription);
            $entityManager->persist($user);
            
            $entityManager->flush();

            return $this->json([
                'message' => 'Abonnement modifié avec succès',
                'subscription' => [
                    'id' => $newSubscription->getId(),
                    'status' => $newSubscription->getStatus()->value,
                    'plan' => [
                        'name' => $newPlan->getName(),
                        'price' => $newPlan->getPrice()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors du changement d\'abonnement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/cancel', name: 'api_subscription_cancel', methods: ['POST'])]
    public function cancelSubscription(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user instanceof Accounts) {
                return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
            }

            $currentSubscription = $user->getCurrentSubscription();
            if (!$currentSubscription) {
                return $this->json(['message' => 'Aucun abonnement actif trouvé'], Response::HTTP_NOT_FOUND);
            }

            if ($currentSubscription->getStatus() === SubsciptionStatusEnum::CANCELED) {
                return $this->json(['message' => 'Abonnement déjà annulé'], Response::HTTP_BAD_REQUEST);
            }

            $currentSubscription->setStatus(SubsciptionStatusEnum::CANCELED);
            $entityManager->persist($currentSubscription);
            
            
            $entityManager->flush();

            return $this->json([
                'message' => 'Abonnement résilié avec succès. Vous conservez l\'accès jusqu\'à la fin de votre période.'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de l\'annulation de l\'abonnement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/history', name: 'api_subscription_history', methods: ['GET'])]
    public function getSubscriptionHistory(SubscriptionRepository $subscriptionRepository): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user instanceof Accounts) {
                return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
            }

            $qb = $subscriptionRepository->createQueryBuilder('s')
                ->join('s.users', 'u')
                ->where('u.id = :userId')
                ->setParameter('userId', $user->getId())
                ->orderBy('s.id', 'DESC');
            
            $subscriptions = $qb->getQuery()->getResult();

            $history = [];
            foreach ($subscriptions as $subscription) {
                $plan = $subscription->getPlan();
                $history[] = [
                    'subscription' => [
                        'id' => $subscription->getId(),
                        'plan' => [
                            'id' => $plan->getId(),
                            'name' => $plan->getName(),
                            'price' => $plan->getPrice()
                        ]
                    ],
                    'startAt' => (new \DateTime())->format('Y-m-d'),
                    'endAt' => (new \DateTime('+30 days'))->format('Y-m-d')
                ];
            }

            return $this->json($history);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la récupération de l\'historique',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/invoices', name: 'api_subscription_invoices', methods: ['GET'])]
    public function getInvoices(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user instanceof Accounts) {
                return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
            }

            return $this->json([]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la récupération des factures',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 