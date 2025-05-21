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
        if ($role !== null) {
            return [
                'publish'  => $role->canPublish(),
                'moderate' => $role->canModerate(),
                'manage'   => $role->canManage(),
            ];
        }
        return [
            'publish'  => $member->canPublish(),
            'moderate' => $member->canModerate(),
            'manage'   => $member->canManage(),
        ];
    }

    #[Route('/workspaces/{workspaceId}/members', name: 'workspace_members_index', methods: ['GET'])]
    public function listMembers(int $workspaceId): JsonResponse
    {
        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        if (!$workspace) {
            return $this->json(['message' => 'Workspace non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $members = $this->em->getRepository(WorkspaceMembers::class)->findBy(['workspace' => $workspace]);

        $data = [];
        foreach ($members as $member) {
            $permissions = $this->getPermissionsFromMember($member);
            $data[] = [
                'id'        => $member->getId(),
                'user_id'   => $member->getUser()->getId(),
                'user_name' => $member->getUser()->getUserName(),
                'role_id'   => $member->getRole() ? $member->getRole()->getId() : null,
                'publish'   => $permissions['publish'],
                'moderate'  => $permissions['moderate'],
                'manage'    => $permissions['manage'],
            ];
        }

        return $this->json($data);
    }

    #[Route('/workspaces/{workspaceId}/members', name: 'workspace_member_create', methods: ['POST'])]
    public function createMember(Request $request, int $workspaceId): JsonResponse
    {
        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        if (!$workspace) {
            return $this->json(['message' => 'Workspace non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE || !$data || !isset($data['user_id'], $data['role_id'])) {
            return $this->json(['message' => 'Format JSON invalide ou paramètres manquants'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->em->getRepository(Users::class)->find($data['user_id']);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $role = $this->em->getRepository(Roles::class)->find($data['role_id']);
        if (!$role) {
            return $this->json(['message' => 'Rôle non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $existingMember = $this->em->getRepository(WorkspaceMembers::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $user
        ]);
        if ($existingMember) {
            return $this->json(['message' => 'Cet utilisateur est déjà membre du workspace'], Response::HTTP_CONFLICT);
        }

        $workspaceMember = new WorkspaceMembers();
        $workspaceMember->setWorkspace($workspace)
                        ->setUser($user)
                        ->setRole($role)
                        ->setPublish($data['publish'] ?? 0)
                        ->setModerate($data['moderate'] ?? 0)
                        ->setManage($data['manage'] ?? 0);

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

    #[Route('/workspaces/{workspaceId}/members/{memberId}', name: 'workspace_member_delete', methods: ['DELETE'])]
    public function deleteMember(int $workspaceId, int $memberId): JsonResponse
    {
        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        if (!$workspace) {
            return $this->json(['message' => 'Workspace non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $member = $this->em->getRepository(WorkspaceMembers::class)->find($memberId);
        if (!$member || $member->getWorkspace()->getId() !== $workspaceId) {
            return $this->json(['message' => 'Membre non trouvé dans ce workspace'], Response::HTTP_NOT_FOUND);
        }

        if ($workspace->getCreator() === $member->getUser()) {
            return $this->json(['message' => 'Impossible de supprimer le créateur du workspace'], Response::HTTP_FORBIDDEN);
        }

        $this->em->remove($member);
        $this->em->flush();

        return $this->json(['message' => 'Membre supprimé avec succès']);
    }
    #[Route('/workspaces/{workspaceId}/members/{memberId}', name: 'workspace_member_detail', methods: ['GET'])]
    public function getMember(int $workspaceId, int $memberId): JsonResponse
    {
        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        if (!$workspace) {
            return $this->json(['message' => 'Workspace non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $member = $this->em->getRepository(WorkspaceMembers::class)->find($memberId);
        if (!$member || $member->getWorkspace()->getId() !== $workspaceId) {
            return $this->json(['message' => 'Membre non trouvé dans ce workspace'], Response::HTTP_NOT_FOUND);
        }

        $permissions = $this->getPermissionsFromMember($member);
        return $this->json([
            'id'       => $member->getId(),
            'user_id'  => $member->getUser()->getId(),
            'role_id'  => $member->getRole() ? $member->getRole()->getId() : null,
            'publish'  => $permissions['publish'],
            'moderate' => $permissions['moderate'],
            'manage'   => $permissions['manage'],
        ]);
    }

    #[Route('/workspaces/{workspaceId}/members/{memberId}', name: 'workspace_member_update', methods: ['PUT'])]
    public function updateMember(Request $request, int $workspaceId, int $memberId): JsonResponse
    {
        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        if (!$workspace) {
            return $this->json(['message' => 'Workspace non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $member = $this->em->getRepository(WorkspaceMembers::class)->find($memberId);
        if (!$member || $member->getWorkspace()->getId() !== $workspaceId) {
            return $this->json(['message' => 'Membre non trouvé dans ce workspace'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE || !$data) {
            return $this->json(['message' => 'Format JSON invalide'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['user_id'])) {
            $user = $this->em->getRepository(Users::class)->find($data['user_id']);
            if (!$user) {
                return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
            }
            $member->setUser($user);
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
            'role_id'  => $member->getRole() ? $member->getRole()->getId() : null,
            'publish'  => $permissions['publish'],
            'moderate' => $permissions['moderate'],
            'manage'   => $permissions['manage'],
        ]);
    }
}




