<?php

namespace App\Controller\Api;

use App\Service\StripeService;
use App\Repository\PlanRepository;
use App\Repository\SubscriptionRepository;
use App\Entity\Accounts;
use App\Entity\Subscription;
use App\Entity\Payment;
use App\Enum\SubsciptionStatusEnum;
use App\Enum\PaymentStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/api/stripe')]
class StripeController extends AbstractController
{
    private string $frontendUrl;

    public function __construct(
        private StripeService $stripeService,
        private PlanRepository $planRepository,
        private SubscriptionRepository $subscriptionRepository,
        private EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    ) {
        $this->frontendUrl = $params->get('app.frontend_url');
    }

    #[Route('/create-checkout-session', name: 'stripe_create_checkout_session', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $planId = $data['plan_id'] ?? null;

            if (!$planId) {
                return $this->json(['error' => 'Plan ID requis'], Response::HTTP_BAD_REQUEST);
            }

            $plan = $this->planRepository->find($planId);
            if (!$plan) {
                return $this->json(['error' => 'Plan non trouvé'], Response::HTTP_NOT_FOUND);
            }

            /** @var Accounts $user */
            $user = $this->getUser();
            
            $customer = $this->stripeService->createOrGetCustomer(
                $user->getEmail(),
                $user->getName()
            );

            $session = $this->stripeService->createCheckoutSession(
                $this->getStripePriceId($plan), 
                $customer->id,
                $this->frontendUrl . '/my-subscription?success=true&session_id={CHECKOUT_SESSION_ID}',
                $this->frontendUrl . '/subscription/plans?canceled=true'
            );

            return $this->json([
                'checkout_url' => $session->url,
                'session_id' => $session->id
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la création de la session: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/config', name: 'stripe_config', methods: ['GET'])]
    public function getConfig(): JsonResponse
    {
        return $this->json([
            'publishableKey' => $this->stripeService->getPublishableKey()
        ]);
    }

    #[Route('/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('stripe-signature');

        try {
            $event = $this->stripeService->handleWebhook($payload, $signature);
            
            switch ($event['type']) {
                case 'checkout.session.completed':
                    $this->handleCheckoutSessionCompleted($event['object']);
                    break;
                    
                case 'invoice.payment_succeeded':
                    $this->handleInvoicePaymentSucceeded($event['object']);
                    break;
                    
                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($event['object']);
                    break;
                    
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event['object']);
                    break;
                    
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event['object']);
                    break;
            }

            return $this->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur webhook: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/sync-plans', name: 'stripe_sync_plans', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function syncPlans(): JsonResponse
    {
        try {
            $plans = $this->planRepository->findAll();
            $stripeData = $this->stripeService->syncPlansWithStripe($plans);
            
            foreach ($stripeData as $data) {
                $plan = $data['plan'];
                $plan->setStripeProductId($data['stripe_product_id']);
                $plan->setStripePriceId($data['stripe_price_id']);
                $this->entityManager->persist($plan);
            }
            
            $this->entityManager->flush();
            
            return $this->json([
                'message' => 'Plans synchronisés avec Stripe',
                'synced_plans' => count($stripeData)
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la synchronisation: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/sync-subscriptions', name: 'stripe_sync_subscriptions', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function syncSubscriptions(): JsonResponse
    {
        try {
            $syncedCount = 0;
            $errors = [];

            $stripeSubscriptions = \Stripe\Subscription::all(['limit' => 100]);

            foreach ($stripeSubscriptions->data as $stripeSubscription) {
                try {
                    $existingSubscription = $this->subscriptionRepository->findOneBy([
                        'stripeSubscriptionId' => $stripeSubscription->id
                    ]);

                    if ($existingSubscription) {
                        continue; 
                    }

                    $stripeCustomer = \Stripe\Customer::retrieve($stripeSubscription->customer);
                    
                    $user = $this->entityManager->getRepository(Accounts::class)
                        ->findOneBy(['email' => $stripeCustomer->email]);

                    if (!$user) {
                        $errors[] = "Utilisateur non trouvé pour l'email: " . $stripeCustomer->email;
                        continue;
                    }

                    $priceId = $stripeSubscription->items->data[0]->price->id;
                    $plan = $this->planRepository->findOneBy(['stripePriceId' => $priceId]);

                    if (!$plan) {
                        $errors[] = "Plan non trouvé pour le price ID: " . $priceId;
                        continue;
                    }

                    $subscription = new Subscription();
                    $subscription->setStatus(
                        $stripeSubscription->status === 'active' 
                            ? SubsciptionStatusEnum::ACTIVE 
                            : SubsciptionStatusEnum::CANCELED
                    );
                    $subscription->setDuration(30); 
                    $subscription->setPlan($plan);
                    $subscription->setStripeSubscriptionId($stripeSubscription->id);
                    $subscription->setStripeCustomerId($stripeSubscription->customer);
                    
                    $subscription->addUser($user);
                    $user->setCurrentSubscription($subscription);
                    
                    $this->entityManager->persist($subscription);
                    $this->entityManager->persist($user);
                    
                    $syncedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Erreur pour l'abonnement " . $stripeSubscription->id . ": " . $e->getMessage();
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'message' => "Synchronisation terminée",
                'synced_subscriptions' => $syncedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la synchronisation: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/check-user-subscription/{email}', name: 'stripe_check_user_subscription', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function checkUserSubscription(string $email): JsonResponse
    {
        try {
            $customers = \Stripe\Customer::all(['email' => $email, 'limit' => 1]);
            
            if (count($customers->data) === 0) {
                return $this->json(['message' => 'Aucun client Stripe trouvé pour cet email']);
            }

            $customer = $customers->data[0];
            $subscriptions = \Stripe\Subscription::all(['customer' => $customer->id]);

            $stripeData = [];
            foreach ($subscriptions->data as $sub) {
                $stripeData[] = [
                    'id' => $sub->id,
                    'status' => $sub->status,
                    'current_period_start' => date('Y-m-d H:i:s', $sub->current_period_start),
                    'current_period_end' => date('Y-m-d H:i:s', $sub->current_period_end),
                    'price_id' => $sub->items->data[0]->price->id ?? null
                ];
            }

            $user = $this->entityManager->getRepository(Accounts::class)
                ->findOneBy(['email' => $email]);

            $localData = null;
            if ($user && $user->getCurrentSubscription()) {
                $localSub = $user->getCurrentSubscription();
                $localData = [
                    'id' => $localSub->getId(),
                    'status' => $localSub->getStatus()->value,
                    'stripe_subscription_id' => $localSub->getStripeSubscriptionId(),
                    'plan_name' => $localSub->getPlan()->getName()
                ];
            }

            return $this->json([
                'email' => $email,
                'stripe_subscriptions' => $stripeData,
                'local_subscription' => $localData
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la vérification: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/debug-user-situation', name: 'stripe_debug_user', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function debugUserSituation(): JsonResponse
    {
        try {
            /** @var Accounts $user */
            $user = $this->getUser();
            
            $debugInfo = [
                'user_id' => $user->getId(),
                'user_email' => $user->getEmail(),
                'local_subscription' => null,
                'stripe_customer' => null,
                'stripe_subscriptions' => [],
                'local_plans' => [],
                'stripe_plans_created' => []
            ];

            $currentSubscription = $user->getCurrentSubscription();
            if ($currentSubscription) {
                $debugInfo['local_subscription'] = [
                    'id' => $currentSubscription->getId(),
                    'status' => $currentSubscription->getStatus()->value,
                    'stripe_subscription_id' => $currentSubscription->getStripeSubscriptionId(),
                    'plan_name' => $currentSubscription->getPlan()->getName()
                ];
            }

            $customers = \Stripe\Customer::all(['email' => $user->getEmail(), 'limit' => 1]);
            
            if (count($customers->data) > 0) {
                $customer = $customers->data[0];
                $debugInfo['stripe_customer'] = [
                    'id' => $customer->id,
                    'email' => $customer->email,
                    'created' => date('Y-m-d H:i:s', $customer->created)
                ];

                $subscriptions = \Stripe\Subscription::all(['customer' => $customer->id]);
                
                foreach ($subscriptions->data as $sub) {
                    $priceId = $sub->items->data[0]->price->id ?? null;
                    $productId = $sub->items->data[0]->price->product ?? null;
                    
                    $product = null;
                    if ($productId) {
                        try {
                            $product = \Stripe\Product::retrieve($productId);
                        } catch (\Exception $e) {
                        }
                    }

                    $debugInfo['stripe_subscriptions'][] = [
                        'id' => $sub->id,
                        'status' => $sub->status,
                        'price_id' => $priceId,
                        'product_id' => $productId,
                        'product_name' => $product ? $product->name : 'Unknown',
                        'amount' => $sub->items->data[0]->price->unit_amount ?? 0,
                        'currency' => $sub->items->data[0]->price->currency ?? 'eur',
                        'created' => date('Y-m-d H:i:s', $sub->created),
                        'current_period_start' => date('Y-m-d H:i:s', $sub->current_period_start),
                        'current_period_end' => date('Y-m-d H:i:s', $sub->current_period_end)
                    ];
                }
            }

            $plans = $this->planRepository->findAll();
            foreach ($plans as $plan) {
                $debugInfo['local_plans'][] = [
                    'id' => $plan->getId(),
                    'name' => $plan->getName(),
                    'price' => $plan->getPrice(),
                    'stripe_product_id' => $plan->getStripeProductId(),
                    'stripe_price_id' => $plan->getStripePriceId()
                ];
            }

            if (!empty($debugInfo['stripe_subscriptions']) && !$currentSubscription) {
                $debugInfo['recommendation'] = 'AUTO_ASSOCIATE';
                
                foreach ($debugInfo['stripe_subscriptions'] as $stripeSub) {
                    if ($stripeSub['status'] === 'active') {
                        $amount = $stripeSub['amount'] / 100; 
                        
                        foreach ($plans as $plan) {
                            if (abs(floatval($plan->getPrice()) - $amount) < 0.01) {
                                $subscription = new Subscription();
                                $subscription->setStatus(SubsciptionStatusEnum::ACTIVE);
                                $subscription->setDuration(30);
                                $subscription->setPlan($plan);
                                $subscription->setStripeSubscriptionId($stripeSub['id']);
                                $subscription->setStripeCustomerId($debugInfo['stripe_customer']['id']);
                                $subscription->addUser($user);
                                
                                $user->setCurrentSubscription($subscription);
                                
                                $this->entityManager->persist($subscription);
                                $this->entityManager->persist($user);
                                $this->entityManager->flush();
                                
                                $debugInfo['auto_association'] = [
                                    'created_subscription_id' => $subscription->getId(),
                                    'associated_plan' => $plan->getName(),
                                    'stripe_subscription_id' => $stripeSub['id']
                                ];
                                
                                break 2; 
                            }
                        }
                    }
                }
            }

            return $this->json($debugInfo);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors du debug: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/checkout-success', name: 'stripe_checkout_success', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function checkoutSuccess(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $sessionId = $data['session_id'] ?? null;

            if (!$sessionId) {
                return $this->json(['error' => 'Session ID requis'], Response::HTTP_BAD_REQUEST);
            }

            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            
            if ($session->payment_status !== 'paid') {
                return $this->json(['error' => 'Paiement non confirmé'], Response::HTTP_BAD_REQUEST);
            }

            /** @var Accounts $user */
            $user = $this->getUser();

            $existingPayment = $this->entityManager->getRepository(Payment::class)
                ->findOneBy(['stripePaymentIntentId' => $session->payment_intent]);

            if ($existingPayment) {
                return $this->json(['message' => 'Paiement déjà traité']);
            }

            $this->handleCheckoutSessionCompleted($session);

            $this->createPaymentFromSession($session);

            return $this->json(['message' => 'Paiement traité avec succès']);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors du traitement du paiement: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function handleCheckoutSessionCompleted($session): void
    {
        $subscriptionId = $session->subscription;
        $customerId = $session->customer;
        
        $customer = \Stripe\Customer::retrieve($customerId);
        $user = $this->entityManager->getRepository(Accounts::class)
            ->findOneBy(['email' => $customer->email]);
            
        if (!$user) {
            throw new \Exception('Utilisateur non trouvé');
        }

        $stripeSubscription = \Stripe\Subscription::retrieve($subscriptionId);
        $priceId = $stripeSubscription->items->data[0]->price->id;
        
        $plan = $this->planRepository->findOneBy(['stripePriceId' => $priceId]);
        if (!$plan) {
            throw new \Exception('Plan non trouvé');
        }

        $subscription = new Subscription();
        $subscription->setStatus(SubsciptionStatusEnum::ACTIVE);
        $subscription->setDuration(30); 
        $subscription->setPlan($plan);
        $subscription->setStripeSubscriptionId($subscriptionId);
        $subscription->setStripeCustomerId($customerId);
        
        $subscription->addUser($user);
        $user->setCurrentSubscription($subscription);
        
        $this->entityManager->persist($subscription);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function handleInvoicePaymentSucceeded($invoice): void
    {
        $payment = new Payment();
        $payment->setAmount($invoice->amount_paid / 100); 
        $payment->setStatus(PaymentStatusEnum::COMPLETED);
        $payment->setPaymentMethod('stripe');
        $payment->setStripePaymentIntentId($invoice->payment_intent);
        $payment->setCreatedAt(new \DateTimeImmutable());
        
        $subscription = $this->subscriptionRepository->findOneBy([
            'stripeSubscriptionId' => $invoice->subscription
        ]);
        
        if ($subscription) {
            $payment->setSubscription($subscription);
        }
        
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
    }

    private function handleInvoicePaymentFailed($invoice): void
    {
        $subscription = $this->subscriptionRepository->findOneBy([
            'stripeSubscriptionId' => $invoice->subscription
        ]);
        
        if ($subscription) {
            $subscription->setStatus(SubsciptionStatusEnum::EXPIRED);
            $this->entityManager->persist($subscription);
            $this->entityManager->flush();
        }
    }

    private function handleSubscriptionUpdated($subscription): void
    {
        $localSubscription = $this->subscriptionRepository->findOneBy([
            'stripeSubscriptionId' => $subscription->id
        ]);
        
        if ($localSubscription) {
            $status = match($subscription->status) {
                'active' => SubsciptionStatusEnum::ACTIVE,
                'canceled' => SubsciptionStatusEnum::CANCELED,
                'incomplete_expired' => SubsciptionStatusEnum::EXPIRED,
                default => SubsciptionStatusEnum::ACTIVE
            };
            
            $localSubscription->setStatus($status);
            $this->entityManager->persist($localSubscription);
            $this->entityManager->flush();
        }
    }

    private function handleSubscriptionDeleted($subscription): void
    {
        $localSubscription = $this->subscriptionRepository->findOneBy([
            'stripeSubscriptionId' => $subscription->id
        ]);
        
        if ($localSubscription) {
            $localSubscription->setStatus(SubsciptionStatusEnum::CANCELED);
            $this->entityManager->persist($localSubscription);
            $this->entityManager->flush();
        }
    }

    private function createPaymentFromSession($session): void
    {
        $subscription = $this->subscriptionRepository->findOneBy([
            'stripeSubscriptionId' => $session->subscription
        ]);

        if (!$subscription) {
            throw new \Exception('Abonnement non trouvé pour la session: ' . $session->id);
        }

        $payment = new Payment();
        $payment->setAmount($session->amount_total / 100); 
        $payment->setStatus(PaymentStatusEnum::COMPLETED);
        $payment->setPaymentMethod('stripe');
        $payment->setStripePaymentIntentId($session->payment_intent);
        $payment->setCreatedAt(new \DateTimeImmutable());
        $payment->setSubscription($subscription);

        $this->entityManager->persist($payment);
        $this->entityManager->flush();
    }

    private function getStripePriceId($plan): string
    {
        if ($plan->getStripePriceId()) {
            return $plan->getStripePriceId();
        }
        
        $product = $this->stripeService->createProduct($plan->getName(), $plan->getDescription());
        $price = $this->stripeService->createPrice($product->id, (int)($plan->getPrice() * 100));
        
        $plan->setStripeProductId($product->id);
        $plan->setStripePriceId($price->id);
        $this->entityManager->persist($plan);
        $this->entityManager->flush();
        
        return $price->id;
    }
} 