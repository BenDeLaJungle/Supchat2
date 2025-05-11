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

        $message = new Messages();
        $message->setChannel($channel);
        $message->setUser($user);
        $message->setContent($data['content']);

        $em->persist($message);
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

    #[Route('/messages/{id}', name: 'get_message', methods: ['GET'])]
    public function getMessage(Messages $message): JsonResponse
    {
        return $this->json([
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'timestamp' => $message->getCreatedAt()->format('c'),
            'author' => [
                'id' => $message->getUser()->getId(),
                'username' => $message->getUser()->getUsername(),
            ],
            'channel_id' => $message->getChannel()->getId(),
        ]);
    }

    #[Route('/channels/{id}/messages', name: 'get_channel_messages', methods: ['GET'])]
    public function getMessagesForChannel(
        Channels $channel,
        MessagesRepository $repo,
        Request $request
    ): JsonResponse {
        $limit = $request->query->getInt('limit', 20);
        $before = $request->query->get('before');
        $beforeId = $request->query->getInt('before_id', 0);

        $qb = $repo->createQueryBuilder('m')
            ->where('m.channel = :channel')
            ->setParameter('channel', $channel)
            ->orderBy('m.createdAt', 'DESC')
            ->addOrderBy('m.id', 'DESC')
            ->setMaxResults($limit);

        if ($before) {
            try {
                $beforeDate = new \DateTime($before);

                if ($beforeId > 0) {
                    $qb->andWhere('(m.createdAt < :before) OR (m.createdAt = :before AND m.id < :beforeId)')
                        ->setParameter('before', $beforeDate)
                        ->setParameter('beforeId', $beforeId);
                } else {
                    $qb->andWhere('m.createdAt < :before')
                        ->setParameter('before', $beforeDate);
                }
            } catch (\Exception $e) {
                return $this->json(['error' => 'Format de date invalide pour "before"'], 400);
            }
        }

        $messages = $qb->getQuery()->getResult();

        $data = array_map(fn(Messages $m) => [
            'id' => $m->getId(),
            'content' => $m->getContent(),
            'timestamp' => $m->getCreatedAt()->format('c'),
            'author' => [
                'id' => $m->getUser()->getId(),
                'username' => $m->getUser()->getUsername(),
            ],
            'channel_id' => $m->getChannel()->getId(),
        ], $messages);

        // On remet en ordre croissant pour affichage correct
        return $this->json(array_reverse($data));
    }
    
    #[Route('/messages/{id}', name: 'update_message', methods: ['PUT'])]
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

    #[Route('/messages/{id}', name: 'delete_message', methods: ['DELETE'])]
    public function deleteMessage(Messages $message, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($message);
        $em->flush();

        return new JsonResponse(['status' => 'Message supprimé']);
    }
}

