<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Reactions;
use App\Entity\Users;
use App\Entity\Messages;

#[Route('/reaction')]
class ReactionController extends AbstractController
{
    #[Route('/add', name: 'reaction_add', methods: ['POST'])]
    public function addReaction(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['emoji'], $data['messageId'], $data['userId'])) {
            return new JsonResponse(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Récupération de l'utilisateur et du message
        $user = $em->getRepository(Users::class)->find($data['userId']);
        $message = $em->getRepository(Messages::class)->find($data['messageId']);

        if (!$user || !$message) {
            return new JsonResponse(['error' => 'Utilisateur ou message introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Création de la réaction
        $reaction = new Reactions();
        $reaction->setEmojiCode($data['emoji']);  // Correction ici
        $reaction->setUser($user);
        $reaction->setMessage($message);

        $em->persist($reaction);
        $em->flush();

        return new JsonResponse([
            'message' => 'Réaction ajoutée',
            'reaction' => [
                'id' => $reaction->getId(),
                'emoji' => $reaction->getEmojiCode(), // Correction ici
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUserName(),
                ],
                'message' => [
                    'id' => $message->getId(),
                ],
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/remove/{id}', name: 'reaction_remove', methods: ['DELETE'])]
    public function removeReaction(int $id, EntityManagerInterface $em): Response
    {
        $reaction = $em->getRepository(Reactions::class)->find($id);

        if (!$reaction) {
            return new JsonResponse(['error' => 'Réaction non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($reaction);
        $em->flush();

        return new JsonResponse(['message' => 'Réaction supprimée']);
    }
}


