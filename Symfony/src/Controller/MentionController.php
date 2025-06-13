<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Mentions;
use App\Entity\Users;
use App\Entity\Messages;

#[Route('api/mention')]
class MentionController extends AbstractController
{
    #[Route('/add', name: 'mention_add', methods: ['POST'])]
    public function addMention(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['userId'], $data['messageId'])) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        $user = $em->getRepository(Users::class)->find($data['userId']);
        $message = $em->getRepository(Messages::class)->find($data['messageId']);

        if (!$user || !$message) {
            return $this->json(['error' => 'Utilisateur ou message introuvable'], Response::HTTP_NOT_FOUND);
        }

        $mention = new Mentions();
        $mention->setUser($user);
        $mention->setMessage($message);

        $em->persist($mention);
        $em->flush();

        return $this->json(['message' => 'Mention ajoutée avec succès']);
    }

    #[Route('/{id}', name: 'mention_get', methods: ['GET'])]
    public function getMention(int $id, EntityManagerInterface $em): Response
    {
        $mention = $em->getRepository(Mentions::class)->find($id);

        if (!$mention) {
            return $this->json(['error' => 'Mention introuvable'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $mention->getId(),
            'user' => [
                'id' => $mention->getUser()->getId(),
                'username' => $mention->getUser()->getUserName(),
            ],
            'message' => [
                'id' => $mention->getMessage()->getId(),
            ]
        ]);
    }
}

