<?php

namespace App\Controller;

use App\Entity\Channels;
use App\Entity\Workspaces;
use App\Entity\WorkspaceMembers;
use App\Entity\Roles;
use App\Repository\ChannelsRepository;
use App\Repository\WorkspaceMembersRepository;
use App\Repository\WorkspacesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChannelController extends AbstractController
{
    #[Route('/api/channels/{id}', name: 'get_channel', methods: ['GET'])]
    public function getChannel(Channels $channel, EntityManagerInterface $em): JsonResponse
    {
        /** @var \App\Entity\Users $currentUser */
        $currentUser = $this->getUser();
        $workspace = $channel->getWorkspace();

        $workspaceMember = $em->getRepository(WorkspaceMembers::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $currentUser
        ]);

        if (!$workspaceMember) {
            return new JsonResponse(['error' => 'Non membre du workspace'], 403);
        }

        // Si canal privé, vérifier le rôle minimum requis
        if ($channel->getStatus() === false && $workspaceMember->getRole()->getId() < $channel->getMinRole()) {
            return new JsonResponse(['error' => 'Accès interdit à ce canal privé'], 403);
        }

        return new JsonResponse([
            'id' => $channel->getId(),
            'name' => $channel->getName(),
            'status' => $channel->getStatus(),
            'workspace' => $channel->getWorkspace()->getId()
        ]);
    }

    #[Route('/api/channels/by-name/{name}', name: 'get_channel_by_name', methods: ['GET'])]
    public function getChannelByName(string $name, ChannelsRepository $repo): JsonResponse
    {
        $channel = $repo->findOneBy(['name' => $name]);

        if (!$channel) {
            return new JsonResponse(['error' => 'Channel non trouvé'], 404);
        }

        return new JsonResponse([
            'id' => $channel->getId(),
            'name' => $channel->getName(),
            'status' => $channel->getStatus(),
            'workspace' => $channel->getWorkspace()->getId()
        ]);
    }

    #[Route('/api/workspaces/{id}/channels', name: 'get_channels_by_workspace', methods: ['GET'])]
    public function getChannelsByWorkspace(int $id, ChannelsRepository $repo): JsonResponse
    {
        $channels = $repo->findBy(['workspace' => $id]);

        $data = array_map(function (Channels $channel) {
            return [
                'id'       => $channel->getId(),
                'name'     => $channel->getName(),
                'status'   => $channel->getStatus(),
                'minRole'  => $channel->getMinRole(),
            ];
        }, $channels);

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

        /** @var \App\Entity\Users $currentUser */
        $currentUser = $this->getUser();
        $currentUserId = $currentUser->getId();

        $roleId = WorkspaceMembers::getUserRoleInWorkspace($workspace->getId(), $currentUserId, $em);
        if (!Roles::hasPermission($roleId, 'create_channel')) {
            return new JsonResponse(['error' => 'Accès refusé.'], 403);
        }

        $channel = new Channels();
        $channel->setName($data['name']);
        $channel->setStatus($data['status']);
        $channel->setWorkspace($workspace);

        // Valeur par défaut du rôle requis = 1 (membre)
        $minRole = isset($data['min_role']) ? (int) $data['min_role'] : 1;
        $channel->setMinRole($minRole);

        $em->persist($channel);
        $em->flush();

        return new JsonResponse(['status' => 'Canal créé', 'id' => $channel->getId()], 201);
    }

    #[Route('/api/channels/{id}', name: 'update_channel', methods: ['PUT'])]
    public function updateChannel(Request $request, Channels $channel, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $channel->setName($data['name']);
        }

        if (isset($data['status'])) {
            $channel->setStatus($data['status']);
        }

        if (isset($data['min_role'])) {
            $channel->setMinRole((int) $data['min_role']);
        }

        $em->flush();

        return new JsonResponse(['status' => 'Canal mis à jour']);
    }

    #[Route('/api/channels/{id}', name: 'delete_channel', methods: ['DELETE'])]
    public function deleteChannel(Channels $channel, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($channel);
        $em->flush();

        return new JsonResponse(['status' => 'Canal supprimé']);
    }

    #[Route('/api/channels/{id}/privilege', name: 'get_channel_privilege', methods: ['POST'])]
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

        $workspaceMember = $workspaceMembersRepo->findOneBy([
            'workspace' => $workspace,
            'user' => $userId
        ]);

        if (!$workspaceMember) {
            return $this->json([
                'is_admin' => false,
                'can_moderate' => false,
                'can_manage' => false
            ]);
        }

        $role = $workspaceMember->getRole();
        $canModerate = $role?->canModerate() ?? false;
        $canManage = $role?->canManage() ?? false;

        return $this->json([
            'is_admin' => $role && $role->getId() === 3,
            'can_moderate' => $canModerate,
            'can_manage' => $canManage
        ]);
    }

    #[Route('/api/workspaces/{workspaceId}/channels', name: 'workspace_channels_index', methods: ['GET'])]
    public function listChannels(int $workspaceId, EntityManagerInterface $em): JsonResponse
    {
        $workspace = $em->getRepository(Workspaces::class)->find($workspaceId);

        if (!$workspace) {
            return $this->json(['message' => 'Workspace non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $channels = $em->getRepository(Channels::class)->findBy(['workspace' => $workspace]);

        $data = array_map(function (Channels $channel) {
            return [
                'id'     => $channel->getId(),
                'name'   => $channel->getName(),
                'status' => $channel->getStatus(),
            ];
        }, $channels);

        return $this->json($data);
    }
}
