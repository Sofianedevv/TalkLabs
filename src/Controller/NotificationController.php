<?php

namespace App\Controller;

use Exception;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'app_notification_')]
final class NotificationController extends AbstractController
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }

    #[Route('/notification', name:"get_notifications", methods: ['GET'])]
    public function getNotifications(): JsonResponse {
        try {
            $notifications = $this->notificationService->getAllNotifications();
            return $this->json($notifications);

        } catch(\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 401);
        }
    }

    #[Route('/notification/read/{id}', name:"mark_read")]
    public function markNotificationAsRead($id) : JsonResponse {
        try {
            $this->notificationService->markAsread($id);
            return $this->json(['message' => 'Notfication marquée comme lue']);
        } catch(\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/notification/read-all', name:"mark_read_all")]
    public function markNotificationAsAllRead() : JsonResponse {
        try {
            $this->notificationService->markAllAsRead();
            return $this->json(['message' => 'Toutes les notfication ont été marquées comme lues']);
        } catch(\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/notification/{id}', name:"delete")]
    public function deleteNotification($id) : JsonResponse {
        try {
            $this->notificationService->delete($id);
            return $this->json(['message' => 'Notification supprimée']);
        } catch(\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/notification/delete-all', name:"delete_all")]
    public function deleteNotifications() : JsonResponse {
        try {
            $this->notificationService->deleteAll();
            return $this->json(['message' => 'Toutes les notifcations ont été supprimées']);
        } catch(\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }






    



}
