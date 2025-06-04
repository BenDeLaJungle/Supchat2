<?php

namespace App\Controller;

use App\Entity\Workspaces;
use App\Entity\WorkspaceMembers;
use App\Entity\Channels;
use App\Entity\Roles;
use App\Entity\Users;
use App\Repository\WorkspacesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/workspaces')]
final class WorkspacesController extends AbstractController
{
    #[Route('', name: 'workspaces_index', methods: ['GET'])]
    public function index(WorkspacesRepository $workspacesRepository): JsonResponse
    {
        /** @var Users $user */
        $user = $this->getUser();

        $workspaces = $workspacesRepository->findByUser($user);

        $workspaceData = array_map(fn(Workspaces $workspace) => [
            'id'      => $workspace->getId(),
            'name'    => $workspace->getName(),
            'status'  => $workspace->getStatus(),
            'creator' => [
                'id'       => $workspace->getCreator()->getId(),
                'username' => $workspace->getCreator()->getUserName(),
            ],
        ], $workspaces);

        return $this->json($workspaceData);
    }

    #[Route('/{id}', name: 'workspaces_show', methods: ['GET'])]
    public function show(Workspaces $workspace): JsonResponse
    {
        return $this->json([
            'id'      => $workspace->getId(),
            'name'    => $workspace->getName(),
            'status'  => $workspace->getStatus(),
            'creator' => [
                'id'       => $workspace->getCreator()->getId(),
                'username' => $workspace->getCreator()->getUserName(),
            ],
        ]);
    }

    #[Route('', name: 'workspaces_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['name'], $data['status'])) {
            return $this->json(['error' => 'Données manquantes.'], Response::HTTP_BAD_REQUEST);
        }

        $existingWorkspace = $em->getRepository(Workspaces::class)->findOneBy(['name' => $data['name']]);
        if ($existingWorkspace) {
            return $this->json(['error' => 'Workspace déjà existant.'], Response::HTTP_CONFLICT);
        }

        /** @var Users $user */
        $user = $this->getUser();

        $workspace = new Workspaces();
        $workspace->setName($data['name']);
        $workspace->setStatus($data['status']);
        $workspace->setCreator($user);

        $em->persist($workspace);
        $em->flush();

        // ✅ Le créateur devient membre admin (role 3)
        $adminRole = $em->getRepository(Roles::class)->find(3); // ID 3 = admin
        if (!$adminRole) {
            return $this->json(['error' => 'Rôle admin introuvable.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $workspaceMember = (new WorkspaceMembers())
            ->setWorkspace($workspace)
            ->setUser($user)
            ->setRole($adminRole)
            ->setPublish(true)
            ->setModerate(true)
            ->setManage(true);

        $em->persist($workspaceMember);
        $em->flush();

        return $this->json([
            'id'         => $workspace->getId(),
            'name'       => $workspace->getName(),
            'status'     => $workspace->getStatus(),
            'creator_id' => $user->getId(),
            'member_id'  => $workspaceMember->getId(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'workspaces_delete', methods: ['DELETE'])]
    public function delete(Workspaces $workspace, EntityManagerInterface $em): JsonResponse
    {
        /** @var Users $currentUser */
        $currentUser = $this->getUser();

        $roleId = WorkspaceMembers::getUserRoleInWorkspace(
            $workspace->getId(),
            $currentUser->getId(),
            $em
        );
        if (!Roles::hasPermission($roleId, 'delete_workspace')) {
            return $this->json(['error' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }

        $members  = $em->getRepository(WorkspaceMembers::class)->count(['workspace' => $workspace]);
        $channels = $em->getRepository(Channels::class)->count(['workspace' => $workspace]);

        if ($members > 0 || $channels > 0) {
            return $this->json(['error' => 'Workspace non vide.'], Response::HTTP_BAD_REQUEST);
        }

        $em->remove($workspace);
        $em->flush();

        return $this->json(['message' => 'Workspace supprimé avec succès.']);
    }

    #[Route('/{id}/generate-invite', name: 'generate_invite_link', methods: ['GET'])]
    public function generateInviteLink(Workspaces $workspace, EntityManagerInterface $em): JsonResponse
    {
        /** @var Users $currentUser */
        $currentUser = $this->getUser();

        $roleId = WorkspaceMembers::getUserRoleInWorkspace(
            $workspace->getId(),
            $currentUser->getId(),
            $em
        );
        if (!Roles::hasPermission($roleId, 'manage_roles')) {
            return $this->json(['error' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }

        $secret     = 'mon_secret_partage';
        $expiry     = (new \DateTime('+1 day'))->getTimestamp();
        $payload    = $workspace->getId() . '|' . $expiry;
        $signature  = hash_hmac('sha256', $payload, $secret);
        $token      = base64_encode($payload . '|' . $signature);
        $inviteLink = sprintf('http://localhost:5173/invite/%s', urlencode($token));

        return $this->json(['invite_link' => $inviteLink]);
    }

    #[Route('/invite/{token}', name: 'accept_invite_link', methods: ['POST'])]
    public function acceptInvite(string $token, EntityManagerInterface $em): JsonResponse
    {
        $secret  = 'mon_secret_partage';
        $decoded = base64_decode($token);
        if (!$decoded) {
            return $this->json(['error' => 'Token invalide'], Response::HTTP_BAD_REQUEST);
        }

        list($workspaceId, $expiry, $signature) = explode('|', $decoded);
        $expectedSignature = hash_hmac('sha256', "$workspaceId|$expiry", $secret);

        if (!hash_equals($expectedSignature, $signature) || (int)$expiry < time()) {
            return $this->json(['error' => 'Lien invalide ou expiré'], Response::HTTP_BAD_REQUEST);
        }

        $workspace = $em->getRepository(Workspaces::class)->find($workspaceId);
        if (!$workspace) {
            return $this->json(['error' => 'Workspace introuvable'], Response::HTTP_NOT_FOUND);
        }

        /** @var Users $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
        }

        $existing = $em->getRepository(WorkspaceMembers::class)
                       ->findOneBy(['workspace' => $workspace, 'user' => $user]);
        if ($existing) {
            return $this->json(['message' => 'Vous êtes déjà membre'], Response::HTTP_CONFLICT);
        }

        $role = $em->getRepository(Roles::class)->find(1); // 1 = membre
        if (!$role) {
            return $this->json(['error' => 'Rôle membre introuvable'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $membership = (new WorkspaceMembers())
            ->setWorkspace($workspace)
            ->setUser($user)
            ->setRole($role)
            ->setPublish(true)
            ->setModerate(false)
            ->setManage(false);

        $em->persist($membership);
        $em->flush();

        return $this->json([
            'message'      => 'Ajout au workspace réussi',
            'workspace_id' => $workspaceId,
            'member_id'    => $membership->getId()
        ], Response::HTTP_CREATED);
    }
}
