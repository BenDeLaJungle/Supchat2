<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\Channels;
use App\Entity\Notifications;
use App\Repository\MessagesRepository;
use App\Repository\ChannelsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    private function formatHashtag($hashtag): array
    {
        return [
            'tag' => $hashtag->getChannel()->getName(),
            'channel' => [
                'id' => $hashtag->getChannel()->getId(),
                'name' => $hashtag->getChannel()->getName(),
            ]
        ];
    }

    #[Route('/api/messages', name: 'create_message', methods: ['POST'])]
    public function createMessage(
        Request $request,
        EntityManagerInterface $em,
        ChannelsRepository $channelsRepo,
        UsersRepository $usersRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['channel_id'], $data['user_id'], $data['content'])) {
            return new JsonResponse(['error' => 'Données incomplètes'], 400);
        }

        $channel = $channelsRepo->find($data['channel_id']);
        $user = $usersRepo->find($data['user_id']);

        if (!$channel || !$user) {
            return new JsonResponse(['error' => 'Channel ou utilisateur non trouvé'], 404);
        }

        // Création du message
        $message = new Messages();
        $message->setChannel($channel);
        $message->setUser($user);
        $message->setContent($data['content']);

        $em->persist($message);

        // Création de la notification
        $notification = new Notifications();
        $notification->setUser($user); // ou destinataire différent si besoin
        $notification->setMessage($message);
        $notification->setAtRead(false); // Par défaut, non lue

        $em->persist($notification);

        // Enregistrement
        $em->flush();

        return $this->json([
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'timestamp' => $message->getCreatedAt()->format('c'),
            'author' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
            ],
            'channel_id' => $channel->getId(),
        ], 201);
    }

    #[Route('/api/messages/{id}', name: 'get_message', methods: ['GET'])]
    public function getMessage(Messages $message): JsonResponse
    {
        $hashtags = $message->getHashtags()->toArray();
        return $this->json([
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'timestamp' => $message->getCreatedAt()->format('c'),
            'author' => [
                'id' => $message->getUser()->getId(),
                'username' => $message->getUser()->getUsername(),
            ],
            'channel_id' => $message->getChannel()->getId(),
            'hashtags' => array_map(fn($h) => $this->formatHashtag($h), $hashtags)
        ]);
    }

    #[Route('/api/channels/{id}/messages', name: 'get_channel_messages', methods: ['GET'])]
    public function getChannelMessages(
        int $id,
        ChannelsRepository $channelsRepo,
        MessagesRepository $messagesRepo
    ): JsonResponse {
        $channel = $channelsRepo->find($id);

        if (!$channel) {
            return new JsonResponse(['error' => 'Canal non trouvé'], 404);
        }

        $messages = $messagesRepo->findBy(['channel' => $channel]);

        $data = array_map(function (Messages $message) {
            return [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'timestamp' => $message->getCreatedAt()->format('c'),
                'author' => [
                    'id' => $message->getUser()->getId(),
                    'username' => $message->getUser()->getUsername(),
                ],
                'channel_id' => $message->getChannel()->getId(),
            ];
        }, $messages);

        return $this->json($data);
    }
}
