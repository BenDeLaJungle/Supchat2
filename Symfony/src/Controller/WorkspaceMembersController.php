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
            'publish'  => $role?->canPublish()  ?? $member->canPublish(),
            'moderate' => $role?->canModerate() ?? $member->canModerate(),
            'manage'   => $role?->canManage()   ?? $member->canManage(),
        ];
    }

    #[Route('/api/workspaces/{workspaceId}/members', name: 'workspace_members_index', methods: ['GET'])]
    public function listMembers(int $workspaceId): JsonResponse
    {
        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        if (!$workspace) {
            return $this->json(['message' => 'Workspace non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $members = $this->em
            ->getRepository(WorkspaceMembers::class)
            ->findBy(['workspace' => $workspace]);

        $data = array_map(function (WorkspaceMembers $member) {
            $perm = $this->getPermissionsFromMember($member);
            return [
                'id'        => $member->getId(),
                'user_id'   => $member->getUser()->getId(),
                'user_name' => $member->getUser()->getUserName(),
                'role_id'   => $member->getRole()?->getId(),
                'publish'   => $perm['publish'],
                'moderate'  => $perm['moderate'],
                'manage'    => $perm['manage'],
            ];
        }, $members);

        return $this->json($data);
    }

    #[Route('/api/workspaces/{workspaceId}/members', name: 'workspace_member_create', methods: ['POST'])]
    public function createMember(Request $request, int $workspaceId): JsonResponse
    {
        /** @var Users $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser instanceof Users) {
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        //Vérifier que l’utilisateur courant peut gérer les membres
        $myRoleId = WorkspaceMembers::getUserRoleInWorkspace($workspaceId, $currentUser->getId(), $this->em);
        if (!Roles::hasPermission($myRoleId, 'manage_members')) {
            return $this->json(['message' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        //Charger le workspace
        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        if (!$workspace) {
            return $this->json(['message' => 'Workspace non trouvé'], Response::HTTP_NOT_FOUND);
        }

        //Récupérer les données du POST
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE 
            || empty($data['user_id']) 
            || empty($data['role_id'])) {
            return $this->json(['message' => 'Format JSON invalide ou paramètres manquants'], Response::HTTP_BAD_REQUEST);
        }

        //Charger user et role
        $userToAdd = $this->em->getRepository(Users::class)->find($data['user_id']);
        $role       = $this->em->getRepository(Roles::class)->find($data['role_id']);
        if (!$userToAdd || !$role) {
            return $this->json(['message' => 'Utilisateur ou rôle introuvable'], Response::HTTP_NOT_FOUND);
        }

        //S’assurer qu’il n’est pas déjà membre
        $exists = $this->em->getRepository(WorkspaceMembers::class)
            ->findOneBy(['workspace' => $workspace, 'user' => $userToAdd]);
        if ($exists) {
            return $this->json(['message' => 'Cet utilisateur est déjà membre'], Response::HTTP_CONFLICT);
        }

        //Créer WorkspaceMembers en reprenant les droits depuis le rôle
        $membership = (new WorkspaceMembers())
            ->setWorkspace($workspace)
            ->setUser($userToAdd)
            ->setRole($role)
            ->setPublish( $role->canPublish() )
            ->setModerate( $role->canModerate() )
            ->setManage( $role->canManage() );

        $this->em->persist($membership);
        $this->em->flush();

        return $this->json([
            'id'        => $membership->getId(),
            'user_id'   => $userToAdd->getId(),
            'role_id'   => $role->getId(),
            'publish'   => $membership->canPublish(),
            'moderate'  => $membership->canModerate(),
            'manage'    => $membership->canManage(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/workspaces/{workspaceId}/members/{memberId}', name: 'workspace_member_delete', methods: ['DELETE'])]
    public function deleteMember(int $workspaceId, int $memberId): JsonResponse
    {
        /** @var Users $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser instanceof Users) {
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $myRoleId = WorkspaceMembers::getUserRoleInWorkspace($workspaceId, $currentUser->getId(), $this->em);
        if (!Roles::hasPermission($myRoleId, 'manage_members')) {
            return $this->json(['message' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $workspace = $this->em->getRepository(Workspaces::class)->find($workspaceId);
        $member    = $this->em->getRepository(WorkspaceMembers::class)->find($memberId);
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
}
