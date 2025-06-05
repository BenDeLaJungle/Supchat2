<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use App\Repository\WorkspacesRepository;
use App\Repository\ChannelsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/api/search', name: 'api_global_search', methods: ['GET'])]
    public function globalSearch(
        Request $request,
        UsersRepository $usersRepo,
        WorkspacesRepository $workspacesRepo,
        ChannelsRepository $channelsRepo
    ): JsonResponse {
        $query = $request->query->get('query', '');
        if (empty($query)) {
            return new JsonResponse(['error' => 'Paramètre de recherche manquant'], 400);
        }

        $users = $usersRepo->findBySearchTerm($query);
        $workspaces = $workspacesRepo->findBySearchTerm($query);
        $channels = $channelsRepo->findBySearchTerm($query);

        return $this->json([
            'users' => array_map(fn($u) => [
                'id' => $u->getId(),
                'userName' => $u->getUserName(),
            ], $users),

            'workspaces' => array_map(fn($w) => [
                'id' => $w->getId(),
                'name' => $w->getName(),
            ], $workspaces),

            'channels' => array_map(fn($c) => [
                'id' => $c->getId(),
                'name' => $c->getName(),
            ], $channels),
        ]);
    }
}
