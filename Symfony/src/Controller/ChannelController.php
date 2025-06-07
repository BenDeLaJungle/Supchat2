<?php

namespace App\Controller;

use App\Entity\Channels;
use App\Entity\Workspaces;
use App\Entity\WorkspaceMembers;
use App\Entity\Roles;
use App\Repository\ChannelsRepository;
use App\Repository\WorkspaceMembersRepository;
use App\Repository\WorkspacesRepository;
use App\Repository\UsersRepository;
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
        $currentUserId = $currentUser->getId();

        $workspaceMember = $em->getRepository(WorkspaceMembers::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $currentUser
        ]);

        if (!$workspaceMember) {
            return new JsonResponse(['error' => 'Non membre du workspace'], 403);
        }

        // restriction spécifique aux conversations privées dans workspace 1
        if ($channel->getStatus() === false && $workspace->getId() === 1) {
            $name = $channel->getName();
            if (!str_contains($name, "_$currentUserId") && !str_contains($name, "$currentUserId")) {
                return new JsonResponse(['error' => 'Accès interdit à ce canal privé'], 403);
            }
        }

        // restriction par rôle dans les autres cas
        if ($channel->getStatus() === false && $workspace->getId() !== 1 &&
            $workspaceMember->getRole()->getId() < $channel->getMinRole()) {
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
    public function getChannelsByWorkspace(int $id, ChannelsRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $channels = $repo->findBy(['workspace' => $id]);

        /** @var \App\Entity\Users $user */
        $user = $this->getUser();
        $currentUserId = $user->getId();

        if ($id === 1) {
        // Filtrage pour n'afficher que les canaux privés auxquels l'utilisateur participe
        $channels = array_filter($channels, function (Channels $channel) use ($currentUserId) {
            $name = $channel->getName();
            return str_contains($name, "_" . $currentUserId) || str_contains($name, $currentUserId . "_");
        });
    }

    $data = [];

    foreach ($channels as $channel) {
        $data[] = [
            'id'       => $channel->getId(),
            'name'     => $channel->getName(),
            'status'   => $channel->getStatus(),
            'minRole'  => $channel->getMinRole(),
        ];
    }

    return new JsonResponse($data);

}


#[Route('/api/channels', name: 'channels_create', methods: ['POST'])]
public function createChannel(Request $request, EntityManagerInterface $em, WorkspacesRepository $workspaceRepo): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    if (!isset($data['workspace_id'], $data['status'])) {
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

    if ($workspace->getId() === 1) {
        if ($data['status'] !== false) {
            return new JsonResponse(['error' => 'Les conversations privées doivent être privées.'], 400);
        }

        if (!isset($data['participants']) || !is_array($data['participants']) || count($data['participants']) !== 2) {
            return new JsonResponse(['error' => 'Une conversation privée doit inclure exactement 2 utilisateurs.'], 400);
        }

        sort($data['participants']);
        $data['name'] = 'priv_' . implode('_', $data['participants']);
    }

    $channel = new Channels();
    $channel->setName($data['name']);
    $channel->setStatus($data['status']);
    $channel->setWorkspace($workspace);
    $channel->setMinRole(isset($data['min_role']) ? (int)$data['min_role'] : 1);

    $em->persist($channel);
    $em->flush();

    return new JsonResponse(['status' => 'Canal créé', 'id' => $channel->getId()], 201);
}



    #[Route('/api/channels/simple', name: 'channels_create_simple', methods: ['POST'])]
    public function createSimplePrivateChannel(Request $request, EntityManagerInterface $em, WorkspacesRepository $workspaceRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['workspace_id'], $data['status'], $data['participants'])) {
            return new JsonResponse(['error' => 'Données incomplètes'], 400);
        }

        $workspace = $workspaceRepo->find($data['workspace_id']);
        if (!$workspace) {
            return new JsonResponse(['error' => 'Workspace introuvable'], 404);
        }

        // Vérifie que deux utilisateurs exactement sont fournis
        if (!is_array($data['participants']) || count($data['participants']) !== 2) {
            return new JsonResponse(['error' => 'Une conversation privée doit inclure exactement 2 utilisateurs.'], 400);
        }

        // Génère le nom du canal sous forme priv_1_2
        sort($data['participants']);
        $channelName = 'priv_' . implode('_', $data['participants']);

        // Vérifie si un canal entre ces deux utilisateurs existe déjà
        $existing = $em->getRepository(Channels::class)->findOneBy([
            'name' => $channelName,
            'workspace' => $workspace,
            'status' => false
        ]);

        if ($existing) {
            return new JsonResponse(['error' => 'Un canal entre ces deux utilisateurs existe déjà.'], 409);
        }

        $channel = new Channels();
        $channel->setName($channelName);
        $channel->setStatus(false); // toujours privé
        $channel->setWorkspace($workspace);
        $channel->setMinRole(1);
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
        WorkspaceMembersRepository $workspaceMembersRepo,
        UsersRepository $usersRepo 
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['user_id'])) {
            return new JsonResponse(['error' => 'L\'ID utilisateur est requis.'], 400);
        }

        $userId = (int) $data['user_id'];
        $workspace = $channel->getWorkspace();

        $user = $usersRepo->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur introuvable.'], 404);
        }

        
        $isAdmin = $user->getRole() === 'ROLE_ADMIN';

        $workspaceMember = $workspaceMembersRepo->findOneBy([
            'workspace' => $workspace,
            'user' => $user
        ]);

        if (!$workspaceMember) {
            return new JsonResponse([
                'is_admin' => $isAdmin,
                'can_moderate' => false,
                'can_manage' => false
            ]);
        }

        $role = $workspaceMember->getRole();
        $canModerate = $role?->canModerate() ?? false;
        $canManage = $role?->canManage() ?? false;
        return new JsonResponse([
            'is_admin' => $isAdmin,
            'can_moderate' => $canModerate,
            'can_manage' => $canManage
        ]);
    }
}

