<?php

namespace App\Controller;

use App\Entity\WorkspaceMembers;
use App\Entity\Workspaces;
use App\Entity\Users;
use App\Entity\Roles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class WorkspaceMembersController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em) 
    {
        $this->em = $em;
    }

    private function getPermissionsFromMember(WorkspaceMembers $member): array
    {
        $role = $member->getRole();
        return [
            'publish'  => $role?->canPublish() ?? $member->canPublish(),
            'moderate' => $role?->canModerate() ?? $member->canModerate(),
            'manage'   => $role?->canManage() ?? $member->canManage(),
        ];
    }

    #[Route('api/workspaces/{workspaceId}/members', name: 'workspace_members_index', methods: ['GET'])]
    public function listMembers(int $workspaceId): JsonResponse
    {
        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        if (!$workspace) {
            return $this->json(['message' => 'Workspace non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $members = $this->em->getRepository(WorkspaceMembers::class)->findBy(['workspace' => $workspace]);

        $data = array_map(function (WorkspaceMembers $member) {
            $permissions = $this->getPermissionsFromMember($member);
            return [
                'id'        => $member->getId(),
                'user_id'   => $member->getUser()->getId(),
                'user_name' => $member->getUser()->getUserName(),
                'role_id'   => $member->getRole()?->getId(),
                'publish'   => $permissions['publish'],
                'moderate'  => $permissions['moderate'],
                'manage'    => $permissions['manage'],
            ];
        }, $members);

        return $this->json($data);
    }

    #[Route('/api/workspaces/{workspaceId}/members', name: 'workspace_member_create', methods: ['POST'])]
    public function createMember(Request $request, int $workspaceId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof Users) {
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $currentUserId = $user->getId();

        $roleId = WorkspaceMembers::getUserRoleInWorkspace($workspaceId, $currentUserId, $this->em);



        if (!Roles::hasPermission($roleId, 'manage_members')) {
            return $this->json(['message' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        if (!$workspace) {
            return $this->json(['message' => 'Workspace non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE || !$data || !isset($data['user_id'], $data['role_id'])) {
            return $this->json(['message' => 'Format JSON invalide ou paramètres manquants'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->em->getRepository(Users::class)->find($data['user_id']);
        $role = $this->em->getRepository(Roles::class)->find($data['role_id']);

        if (!$user || !$role) {
            return $this->json(['message' => 'Utilisateur ou rôle non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $existingMember = $this->em->getRepository(WorkspaceMembers::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $user
        ]);

        if ($existingMember) {
            return $this->json(['message' => 'Cet utilisateur est déjà membre du workspace'], Response::HTTP_CONFLICT);
        }

        $workspaceMember = (new WorkspaceMembers())
            ->setWorkspace($workspace)
            ->setUser($user)
            ->setRole($role)
            ->setPublish($data['publish'] ?? false)
            ->setModerate($data['moderate'] ?? false)
            ->setManage($data['manage'] ?? false);

        $this->em->persist($workspaceMember);
        $this->em->flush();

        return $this->json([
            'id'       => $workspaceMember->getId(),
            'user_id'  => $workspaceMember->getUser()->getId(),
            'role_id'  => $workspaceMember->getRole()->getId(),
            'publish'  => $workspaceMember->canPublish(),
            'moderate' => $workspaceMember->canModerate(),
            'manage'   => $workspaceMember->canManage(),
        ], Response::HTTP_CREATED);
    }

    #[Route('api/workspaces/{workspaceId}/members/{memberId}', name: 'workspace_member_delete', methods: ['DELETE'])]
    public function deleteMember(int $workspaceId, int $memberId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof Users) {
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $currentUserId = $user->getId();

        $roleId = WorkspaceMembers::getUserRoleInWorkspace($workspaceId, $currentUserId, $this->em);

        if (!Roles::hasPermission($roleId, 'manage_members')) {
            return $this->json(['message' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        $member = $this->em->getRepository(WorkspaceMembers::class)->find($memberId);

        if (!$workspace || !$member || $member->getWorkspace()->getId() !== $workspaceId) {
            return $this->json(['message' => 'Membre non trouvé dans ce workspace'], Response::HTTP_NOT_FOUND);
        }

        if ($workspace->getCreator() === $member->getUser()) {
            return $this->json(['message' => 'Impossible de supprimer le créateur du workspace'], Response::HTTP_FORBIDDEN);
        }

        $this->em->remove($member);
        $this->em->flush();

        return $this->json(['message' => 'Membre supprimé avec succès']);
    }

    #[Route('/workspaces/{workspaceId}/members/{memberId}', name: 'workspace_member_update', methods: ['PUT'])]
    public function updateMember(Request $request, int $workspaceId, int $memberId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof Users) {
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $currentUserId = $user->getId();

        $roleId = WorkspaceMembers::getUserRoleInWorkspace($workspaceId, $currentUserId, $this->em);

        if (!Roles::hasPermission($roleId, 'manage_roles')) {
            return $this->json(['message' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        $member = $this->em->getRepository(WorkspaceMembers::class)->find($memberId);

        if (!$workspace || !$member || $member->getWorkspace()->getId() !== $workspaceId) {
            return $this->json(['message' => 'Membre non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Format JSON invalide'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['role_id'])) {
            $role = $this->em->getRepository(Roles::class)->find($data['role_id']);
            if (!$role) {
                return $this->json(['message' => 'Rôle non trouvé'], Response::HTTP_NOT_FOUND);
            }
            $member->setRole($role);
        }

        if (isset($data['publish'])) {
            $member->setPublish((bool)$data['publish']);
        }
        if (isset($data['moderate'])) {
            $member->setModerate((bool)$data['moderate']);
        }
        if (isset($data['manage'])) {
            $member->setManage((bool)$data['manage']);
        }

        $this->em->flush();

        $permissions = $this->getPermissionsFromMember($member);
        return $this->json([
            'id'       => $member->getId(),
            'user_id'  => $member->getUser()->getId(),
            'role_id'  => $member->getRole()?->getId(),
            'publish'  => $permissions['publish'],
            'moderate' => $permissions['moderate'],
            'manage'   => $permissions['manage'],
        ]);
    }

    #[Route('/workspaces/{workspaceId}/members/{memberId}', name: 'workspace_member_detail', methods: ['GET'])]
    public function getMember(int $workspaceId, int $memberId): JsonResponse
    {
        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        $member = $this->em->getRepository(WorkspaceMembers::class)->find($memberId);

        if (!$workspace || !$member || $member->getWorkspace()->getId() !== $workspaceId) {
            return $this->json(['message' => 'Membre non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $permissions = $this->getPermissionsFromMember($member);
        return $this->json([
            'id'       => $member->getId(),
            'user_id'  => $member->getUser()->getId(),
            'role_id'  => $member->getRole()?->getId(),
            'publish'  => $permissions['publish'],
            'moderate' => $permissions['moderate'],
            'manage'   => $permissions['manage'],
        ]);
    }
}
