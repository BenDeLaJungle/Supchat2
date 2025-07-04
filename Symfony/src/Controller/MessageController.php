<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\Channels;
use App\Entity\Notifications;
use App\Repository\MessagesRepository;
use App\Repository\ChannelsRepository;
use App\Repository\UsersRepository;
use App\Repository\FilesRepository;

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
        $notification->setUser($user);
        $notification->setMessage($message);
        $notification->setAtRead(false);

        $em->persist($notification);
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
    public function getMessage(Messages $message,FilesRepository $filesRepo): JsonResponse
    {
        $hashtags = $message->getHashtags()->toArray();
        $files = $filesRepo->findBy(['message' => $message]);
        return $this->json([
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'timestamp' => $message->getCreatedAt()->format('c'),
            'author' => [
                'id' => $message->getUser()->getId(),
                'username' => $message->getUser()->getUsername(),
            ],
            'channel_id' => $message->getChannel()->getId(),
            'hashtags' => array_map(fn($h) => $this->formatHashtag($h), $hashtags),
			'files' => array_map(fn($f) => [
				'id' => $f->getId(),
				'name' => basename($f->getFilePath()),
				'path' => $f->getFilePath()
			], $files)
		]);
	}

    #[Route('/api/channels/{id}/messages', name: 'get_channel_messages', methods: ['GET'])]
    public function getChannelMessages(
        int $id,
        ChannelsRepository $channelsRepo,
        MessagesRepository $messagesRepo,
		FilesRepository $filesRepo 
    ): JsonResponse {
        $channel = $channelsRepo->find($id);

        if (!$channel) {
            return new JsonResponse(['error' => 'Canal non trouvé'], 404);
        }

        $messages = $messagesRepo->findBy(['channel' => $channel]);

        $data = array_map(function (Messages $message) use ($filesRepo) {
			
			$files = $filesRepo->findBy(['message' => $message]);
			
            return [
				'id' => $message->getId(),
				'content' => $message->getContent(),
				'timestamp' => $message->getCreatedAt()->format('c'),
				'author' => [
					'id' => $message->getUser()->getId(),
					'username' => $message->getUser()->getUsername(),
				],
				'channel_id' => $message->getChannel()->getId(),
				'hashtags' => array_map(fn($h) => $this->formatHashtag($h), $message->getHashtags()->toArray()),
				'files' => array_map(fn($f) => [
								'id' => $f->getId(),
								'name' => basename($f->getFilePath()),
								'download_url' => "/api/files/{$f->getId()}/generate-download-url"
				], $files),
			];
		}, $messages);

    return $this->json($data);
}


    #[Route('/api/messages/{id}', name: 'update_message', methods: ['PUT'])]
    public function updateMessage(Messages $message, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['content']) || empty(trim($data['content']))) {
            return new JsonResponse(['error' => 'Contenu manquant'], 400);
        }
        $message->setContent($data['content']);
        $em->flush();
        return $this->json([
            'status' => 'Message modifié',
            'id' => $message->getId(),
            'new_content' => $message->getContent(),
        ]);
    }

    #[Route('/api/messages/{id}', name: 'delete_message', methods: ['DELETE'])]
    public function deleteMessage(Messages $message, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($message);
        $em->flush();

        return new JsonResponse(['status' => 'Message supprimé']);
    }
}

