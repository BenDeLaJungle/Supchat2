<?php

namespace App\Controller;

use App\Entity\Notifications;
use App\Entity\Messages;
use App\Entity\Users;
use App\Repository\NotificationsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
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
                'message' => $notif->getMessage()->getContent(), 
                'read' => $notif->getAtRead(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createNotification(
        Request $request,
        EntityManagerInterface $em,
        UsersRepository $usersRepository,
        HttpClientInterface $httpClient
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = $usersRepository->find($data['userId']);
        $message = $em->getRepository(Messages::class)->find($data['messageId']);

        if (!$user || !$message) {
            return $this->json(['error' => 'Utilisateur ou message introuvable'], 400);
        }

        $notif = new Notifications();
        $notif->setUser($user);
        $notif->setMessage($message);
        $notif->setAtRead(false);

        $em->persist($notif);
        $em->flush();

      
        $httpClient->request('POST', 'http://localhost:3001/notify', [
            'json' => [
                'userId' => $user->getId(),
                'message' => $message->getContent(),
            ]
        ]);

        return $this->json(['success' => true, 'id' => $notif->getId()]);
    }


public function notifyNewMessage(Messages $message, Users $receiver, EntityManagerInterface $em): void
{
    $notification = new Notifications();
    $notification->setUser($receiver);
    $notification->setMessage($message);
    $notification->setAtRead(false);

    $em->persist($notification);
    $em->flush();
}

}