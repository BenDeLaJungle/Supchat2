<?php

namespace App\Controller;

use App\Entity\Workspaces;
use App\Entity\WorkspaceMembers;
use App\Entity\Channels;
use App\Entity\Roles;
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
        $workspaces = $workspacesRepository->findAll();
        $workspaceData = array_map(fn($workspace) => [
            'id' => $workspace->getId(),
            'name' => $workspace->getName(),
            'status' => $workspace->getStatus(),
            'creator' => [
                'id' => $workspace->getCreator()->getId(),
                'username' => $workspace->getCreator()->getUserName()
            ]
        ], $workspaces);
        return $this->json($workspaceData);
    }

    #[Route('/{id}', name: 'workspaces_show', methods: ['GET'])]
    public function show(Workspaces $workspace): JsonResponse
    {
        $workspaceData = [
            'id' => $workspace->getId(),
            'name' => $workspace->getName(),
            'status' => $workspace->getStatus(),
            'creator' => [
                'id' => $workspace->getCreator()->getId(),
                'username' => $workspace->getCreator()->getUserName()
            ]
        ];
        return $this->json($workspaceData);
    }

    #[Route('', name: 'workspaces_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['name'], $data['status'])) {
            return $this->json(['error' => 'Données manquantes.'], Response::HTTP_BAD_REQUEST);
        }

        $existingWorkspace = $entityManager->getRepository(Workspaces::class)->findOneBy(['name' => $data['name']]);
        if ($existingWorkspace) {
            return $this->json(['error' => 'Workspace déjà existant.'], Response::HTTP_CONFLICT);
        }

        /** @var \App\Entity\Users $user */
        $user = $this->getUser();

        // Création du workspace
        $workspace = new Workspaces();
        $workspace->setName($data['name']);
        $workspace->setStatus($data['status']);
        $workspace->setCreator($user);

        $entityManager->persist($workspace);
        $entityManager->flush();

        // Récupérer le rôle Admin (id = 3)
        $role = $entityManager->getRepository(Roles::class)->find(3);
        if (!$role) {
            return $this->json(['error' => 'Rôle admin introuvable.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Ajouter le créateur comme membre admin
        $workspaceMember = (new WorkspaceMembers())
            ->setWorkspace($workspace)
            ->setUser($user)
            ->setRole($role)
            ->setPublish(true)
            ->setModerate(true)
            ->setManage(true);

        $entityManager->persist($workspaceMember);
        $entityManager->flush();

        return $this->json([
            'id' => $workspace->getId(),
            'name' => $workspace->getName(),
            'status' => $workspace->getStatus(),
            'creator_id' => $user->getId(),
            'member_id' => $workspaceMember->getId()
        ], Response::HTTP_CREATED);
    }


    #[Route('/{id}', name: 'workspaces_delete', methods: ['DELETE'])]
    public function delete(Workspaces $workspace, EntityManagerInterface $em): JsonResponse
    {
        /** @var \App\Entity\Users $currentUser */
        $currentUser = $this->getUser();
        $currentUserId = $currentUser->getId();

        $roleId = WorkspaceMembers::getUserRoleInWorkspace($workspace->getId(), $currentUserId, $em);
        if (!Roles::hasPermission($roleId, 'delete_workspace')) {
            return $this->json(['error' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }

        $members = $em->getRepository(WorkspaceMembers::class)->count(['workspace' => $workspace]);
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
        /** @var \App\Entity\Users $currentUser */
        $currentUser = $this->getUser();
        $currentUserId = $currentUser->getId();

        $roleId = WorkspaceMembers::getUserRoleInWorkspace($workspace->getId(), $currentUserId, $em);
        if (!Roles::hasPermission($roleId, 'manage_roles')) {
            return $this->json(['error' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }

        $secret = 'mon_secret_partage';
        $expiry = (new \DateTime('+1 day'))->getTimestamp();
        $payload = $workspace->getId() . '|' . $expiry;
        $signature = hash_hmac('sha256', $payload, $secret);
        $token = base64_encode($payload . '|' . $signature);
        $inviteLink = sprintf('http://localhost:5173/invite/%s', urlencode($token));

        return $this->json(['invite_link' => $inviteLink]);
    }

    #[Route('/invite/{token}', name: 'accept_invite_link', methods: ['POST'])]
    public function acceptInvite(string $token): JsonResponse
    {
        $secret = 'mon_secret_partage';
        $decoded = base64_decode($token);
        list($workspaceId, $expiry, $signature) = explode('|', $decoded);

        $expectedSignature = hash_hmac('sha256', $workspaceId . '|' . $expiry, $secret);

        if (!hash_equals($expectedSignature, $signature) || $expiry < time()) {
            return $this->json(['error' => 'Lien invalide ou expiré'], 400);
        }

        return $this->json(['message' => 'Lien valide pour workspace ' . $workspaceId]);
    }
}
