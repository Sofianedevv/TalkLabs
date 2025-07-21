<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\PaymentMethod;
use Stripe\Price;
use Stripe\Product;
use Stripe\Webhook;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class StripeService
{
    private string $secretKey;
    private string $publishableKey;
    private string $frontendUrl;
    private LoggerInterface $logger;

    public function __construct(
        ParameterBagInterface $params,
        LoggerInterface $logger
    ) {
        $this->secretKey = $params->get('stripe_secret_key');
        $this->publishableKey = $params->get('stripe_publishable_key');
        $this->frontendUrl = $params->get('app.frontend_url');
        $this->logger = $logger;
        
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Créer une session de checkout pour un abonnement
     */
    public function createCheckoutSession(
        string $priceId,
        string $customerId = null,
        string $successUrl = null,
        string $cancelUrl = null
    ): Session {
        try {
            $sessionData = [
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price' => $priceId,
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'subscription',
                'success_url' => $successUrl ?: $this->frontendUrl . '/my-subscription?success=true&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl ?: $this->frontendUrl . '/subscription/plans?canceled=true',
            ];

            // Si nous avons un customer ID, l'ajouter à la session
            if ($customerId) {
                $sessionData['customer'] = $customerId;
            }

            return Session::create($sessionData);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de la session Stripe', [
                'error' => $e->getMessage(),
                'priceId' => $priceId,
                'customerId' => $customerId
            ]);
            throw $e;
        }
    }

    /**
     * Créer ou récupérer un client Stripe
     */
    public function createOrGetCustomer(string $email, string $name = null): Customer
    {
        try {
            // Rechercher un client existant
            $customers = Customer::all(['email' => $email, 'limit' => 1]);
            
            if (count($customers->data) > 0) {
                return $customers->data[0];
            }

            // Créer un nouveau client
            return Customer::create([
                'email' => $email,
                'name' => $name,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création du client Stripe', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer les abonnements d'un client
     */
    public function getCustomerSubscriptions(string $customerId): array
    {
        try {
            $subscriptions = Subscription::all(['customer' => $customerId]);
            return $subscriptions->data;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des abonnements', [
                'error' => $e->getMessage(),
                'customerId' => $customerId
            ]);
            throw $e;
        }
    }

    /**
     * Annuler un abonnement
     */
    public function cancelSubscription(string $subscriptionId): Subscription
    {
        try {
            return Subscription::retrieve($subscriptionId)->cancel();
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'annulation de l\'abonnement', [
                'error' => $e->getMessage(),
                'subscriptionId' => $subscriptionId
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour un abonnement
     */
    public function updateSubscription(string $subscriptionId, string $newPriceId): Subscription
    {
        try {
            $subscription = Subscription::retrieve($subscriptionId);
            
            return Subscription::update($subscriptionId, [
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'price' => $newPriceId,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour de l\'abonnement', [
                'error' => $e->getMessage(),
                'subscriptionId' => $subscriptionId,
                'newPriceId' => $newPriceId
            ]);
            throw $e;
        }
    }

    /**
     * Créer un produit Stripe
     */
    public function createProduct(string $name, string $description = null): Product
    {
        try {
            return Product::create([
                'name' => $name,
                'description' => $description,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création du produit Stripe', [
                'error' => $e->getMessage(),
                'name' => $name
            ]);
            throw $e;
        }
    }

    /**
     * Créer un prix Stripe
     */
    public function createPrice(string $productId, int $amount, string $currency = 'eur'): Price
    {
        try {
            return Price::create([
                'product' => $productId,
                'unit_amount' => $amount, // En centimes
                'currency' => $currency,
                'recurring' => [
                    'interval' => 'month',
                ],
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création du prix Stripe', [
                'error' => $e->getMessage(),
                'productId' => $productId,
                'amount' => $amount
            ]);
            throw $e;
        }
    }

    /**
     * Synchroniser les plans locaux avec Stripe
     */
    public function syncPlansWithStripe(array $plans): array
    {
        $stripeData = [];
        
        foreach ($plans as $plan) {
            try {
                // Créer ou récupérer le produit
                $product = $this->createProduct($plan->getName(), $plan->getDescription());
                
                // Créer le prix
                $price = $this->createPrice(
                    $product->id,
                    (int)($plan->getPrice() * 100), // Convertir en centimes
                    'eur'
                );
                
                $stripeData[] = [
                    'plan_id' => $plan->getId(),
                    'stripe_product_id' => $product->id,
                    'stripe_price_id' => $price->id,
                    'plan' => $plan
                ];
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la synchronisation du plan', [
                    'error' => $e->getMessage(),
                    'plan_id' => $plan->getId()
                ]);
            }
        }
        
        return $stripeData;
    }

    /**
     * Récupérer la clé publique Stripe
     */
    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    /**
     * Traiter les webhooks Stripe
     */
    public function handleWebhook(string $payload, string $signature): array
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $_ENV['STRIPE_WEBHOOK_SECRET'] ?? ''
            );

            $this->logger->info('Webhook Stripe reçu', [
                'type' => $event->type,
                'id' => $event->id
            ]);

            return [
                'type' => $event->type,
                'data' => $event->data,
                'object' => $event->data->object
            ];
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du webhook Stripe', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 