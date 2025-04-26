<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\Channels;
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
    #[Route('/messages', name: 'create_message', methods: ['POST'])]
    public function createMessage(Request $request, EntityManagerInterface $em, ChannelsRepository $channelsRepo, UsersRepository $usersRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['channel_id'], $data['user_id'], $data['content'])) {
            return new JsonResponse(['error' => 'Données incomplètes'], 400);
        }

        $channel = $channelsRepo->find($data['channel_id']);
        $user = $usersRepo->find($data['user_id']);

        if (!$channel || !$user) {
            return new JsonResponse(['error' => 'Channel ou utilisateur non trouvé'], 404);
        }

        $message = new Messages();
        $message->setChannel($channel);
        $message->setUser($user);
        $message->setContent($data['content']);

        $em->persist($message);
        $em->flush();

        return new JsonResponse([
            'status' => 'Message créé',
            'id' => $message->getId(),
            'timestamp' => $message->getCreatedAt()->format('c')
        ], 201);
    }

    #[Route('/messages/{id}', name: 'get_message', methods: ['GET'])]
    public function getMessage(Messages $message): JsonResponse
    {
        return new JsonResponse([
            'id' => $message->getId(),
            'author' => $message->getUser()->getUsername(),
            'content' => $message->getContent(),
            'timestamp' => $message->getCreatedAt()->format('c')
        ]);
    }

    #[Route('/channels/{id}/messages', name: 'get_channel_messages', methods: ['GET'])]
    public function getMessagesForChannel(Channels $channel, MessagesRepository $repo, Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 20);
        $offset = $request->query->getInt('offset', 0);

        $messages = $repo->findBy(
            ['channel' => $channel],
            ['createdAt' => 'ASC'],
            $limit,
            $offset
        );

        $data = array_map(fn(Messages $m) => [
            'id' => $m->getId(),
            'author' => $m->getUser()->getUsername(),
            'content' => $m->getContent(),
            'timestamp' => $m->getCreatedAt()->format('c'),
        ], $messages);

        return new JsonResponse($data);
    }
}
