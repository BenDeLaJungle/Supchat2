<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use App\Repository\WorkspacesRepository;
use App\Repository\ChannelsRepository;
use App\Repository\FilesRepository;
use App\Repository\MessagesRepository;
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
        //WorkspacesRepository $workspacesRepo,
        ChannelsRepository $channelsRepo,
		FilesRepository $filesRepo,
		MessagesRepository $messagesRepo
		
    ): JsonResponse {
        $query = $request->query->get('query', '');
        if (empty($query)) {
            return new JsonResponse(['error' => 'Paramètre de recherche manquant'], 400);
        }
		
		$currentUser = $this->getUser();
		
        $users = $usersRepo->findBySearchTerm($query);
        //$workspaces = $workspacesRepo->findBySearchTerm($query,$currentUser);
        $channels = $channelsRepo->findBySearchTerm($query,$currentUser);
		$files = $filesRepo->findBySearchTerm($query,$currentUser);
		$messages = $messagesRepo->findBySearchTerm($query,$currentUser);

        return $this->json([
            'users' => array_map(fn($u) => [
                'id' => $u->getId(),
                'userName' => $u->getUserName(),
            ], $users),

           // 'workspaces' => array_map(fn($w) => [
           //     'id' => $w->getId(),
           //     'name' => $w->getName(),
           // ], $workspaces),

            'channels' => array_map(fn($c) => [
                'id' => $c->getId(),
                'name' => $c->getName(),
            ], $channels),
			
			'files' => array_map(fn($f) => [
				'id' => $f->getId(),
				'name' => basename($f->getFilePath()),
				'path' => $f->getFilePath(),
			], $files),

			'messages' => array_map(fn($m) => [
				'id' => $m->getId(),
				'content' => $m->getContent(),
				'preview' => mb_substr($m->getContent(), 0, 50),
				'channelId' => $m->getChannel()?->getId(),
			], $messages),
        ]);
    }
}
