<?php

namespace App\Controller;

use App\Repository\NotificationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/notifications', name: 'api_notifications_')]
class NotificationController extends AbstractController
{
    #[Route('/unread', name: 'unread', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getUnreadNotifications(NotificationsRepository $notificationsRepository): JsonResponse
    {
        $user = $this->getUser();

        $notifications = $notificationsRepository->findBy([
            'user' => $user,
            'atRead' => false
        ]);

        $data = [];

        foreach ($notifications as $notif) {
            $data[] = [
                'id' => $notif->getId(),
                'message' => $notif->getMessage()->getContent(), // adapte si getContent() n'existe pas
                'read' => $notif->getAtRead(),
            ];
        }

        return $this->json($data);
    }
}
