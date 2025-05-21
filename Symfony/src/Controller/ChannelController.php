<?php

namespace App\Controller;

use App\Entity\Channels;
use App\Entity\Workspaces;
use App\Entity\WorkspaceMembers;
use App\Repository\ChannelsRepository;
use App\Repository\WorkspaceMembersRepository;
use App\Repository\WorkspacesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChannelController extends AbstractController
{
    #[Route('/channels/{id}', name: 'get_channel', methods: ['GET'])]
    public function getChannel(Channels $channel): JsonResponse
    {
        return new JsonResponse([
            'id' => $channel->getId(),
            'name' => $channel->getName(),
            'status' => $channel->getStatus(),
            'workspace' => $channel->getWorkspace()->getId()
        ]);
    }

    #[Route('/workspaces/{id}/channels', name: 'get_channels_by_workspace', methods: ['GET'])]
    public function getChannelsByWorkspace(int $id, ChannelsRepository $repo): JsonResponse
    {
        $channels = $repo->findBy(['workspace' => $id]);

        $data = array_map(fn(Channels $c) => [
            'id' => $c->getId(),
            'name' => $c->getName(),
            'status' => $c->getStatus(),
        ], $channels);

        return new JsonResponse($data);
    }

    #[Route('/api/channels', name: 'channels_create', methods: ['POST'])]
    public function createChannel(Request $request, EntityManagerInterface $em, WorkspacesRepository $workspaceRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['workspace_id'], $data['status'])) {
            return new JsonResponse(['error' => 'Données incomplètes'], 400);
        }

        $workspace = $workspaceRepo->find($data['workspace_id']);
        if (!$workspace) {
            return new JsonResponse(['error' => 'Workspace introuvable'], 404);
        }

        $channel = new Channels();
        $channel->setName($data['name']);
        $channel->setStatus($data['status']);
        $channel->setWorkspace($workspace);

        $em->persist($channel);
        $em->flush();

        return new JsonResponse(['status' => 'Canal créé', 'id' => $channel->getId()], 201);
    }

    #[Route('/channels/{id}', name: 'update_channel', methods: ['PUT'])]
    public function updateChannel(Request $request, Channels $channel, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $channel->setName($data['name']);
        }

        if (isset($data['status'])) {
            $channel->setStatus($data['status']);
        }

        $em->flush();

        return new JsonResponse(['status' => 'Canal mis à jour']);
    }

    #[Route('/channels/{id}', name: 'delete_channel', methods: ['DELETE'])]
    public function deleteChannel(Channels $channel, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($channel);
        $em->flush();

        return new JsonResponse(['status' => 'Canal supprimé']);
    }

    #[Route('/channels/{id}/privilege', name: 'get_channel_privilege', methods: ['POST'])]
    public function getChannelPrivilege(
        Channels $channel,
        Request $request,
        WorkspaceMembersRepository $workspaceMembersRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['user_id'])) {
            return new JsonResponse(['error' => 'L\'ID utilisateur est requis.'], 400);
        }

        $userId = (int) $data['user_id'];
        $workspace = $channel->getWorkspace();

        //Chercher le membre du workspace
        $workspaceMember = $workspaceMembersRepo->findOneBy([
            'workspace' => $workspace,
            'user' => $userId
        ]);

        if (!$workspaceMember) {
            //Si le user n'est même pas membre ➔ aucun droit
            return $this->json([
                'is_admin' => false,
                'can_moderate' => false,
                'can_manage' => false
            ]);
        }

        //Vérifier admin
        $isAdmin = $workspaceMember->getUser()->getRole() === 'ROLE_ADMIN';

        //Vérifier les droits (moderate + manage)
        $role = $workspaceMember->getRole();

        if ($role) {
            $canModerate = $role->canModerate();
            $canManage = $role->canManage();    
        } else {
            $canModerate = $workspaceMember->canModerate(); 
            $canManage = $workspaceMember->canManage();     
        }

        return $this->json([
            'is_admin' => $isAdmin,
            'can_moderate' => $canModerate,
            'can_manage' => $canManage
        ]);
    }
}

