<?php

namespace App\Controller;

use App\Entity\Hashtags;
use App\Repository\ChannelsRepository;
use App\Repository\MessagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HashtagsController extends AbstractController
{
    #[Route('/api/hashtags', name: 'create_hashtags', methods: ['POST'])]
    public function create(
        Request $request,
        ChannelsRepository $channelsRepo,
        MessagesRepository $messagesRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $messageId = $data['message_id'] ?? null;
        $channels = $data['channels'] ?? [];

        if (!$messageId || empty($channels)) {
            return $this->json(['error' => 'Message ID et channels requis.'], 400);
        }

        $message = $messagesRepo->find($messageId);
        if (!$message) {
            return $this->json(['error' => 'Message non trouvé.'], 404);
        }

        $created = [];
        foreach ($channels as $channelName) {
            $channel = $channelsRepo->findOneBy(['name' => $channelName]);
            if (!$channel) continue;

            $hashtag = new Hashtags();
            $hashtag->setMessage($message);
            $hashtag->setChannel($channel);

            $em->persist($hashtag);
            $created[] = $channelName;
        }

        $em->flush();

        return $this->json(['message' => 'Hashtags créés.', 'channels' => $created]);
    }
}