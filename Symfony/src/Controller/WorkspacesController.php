<?php
namespace App\Controller;
use App\Entity\Workspaces;
use App\Repository\WorkspacesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\WorkspaceMembers;
use App\Entity\Channels;


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
        if (json_last_error() !== JSON_ERROR_NONE || !$data || !isset($data['name'], $data['status'])) {
            return $this->json(['error' => 'Format JSON invalide ou données manquantes.'], Response::HTTP_BAD_REQUEST);
        }
        
        $existingWorkspace = $entityManager->getRepository(Workspaces::class)->findOneBy(['name' => $data['name']]);
        if ($existingWorkspace) {
            return $this->json(['error' => 'Un workspace avec ce nom existe déjà.'], Response::HTTP_CONFLICT);
        }
        
        $user = $this->getUser();
        //if (!$user instanceof \App\Entity\Users) {
        //    throw new \LogicException('L\'utilisateur authentifié n\'est pas une instance de App\Entity\Users.');
        //}
        
        $workspace = new Workspaces();
        $workspace->setName($data['name']);
        $workspace->setStatus($data['status']);
        $workspace->setCreator($user);

        $entityManager->persist($workspace);
        $entityManager->flush();

        return $this->json($workspace, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'workspaces_delete', methods: ['DELETE'])]
    public function delete(Workspaces $workspace, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || $workspace->getCreator() !== $user) {
            return $this->json(['error' => 'Seul le créateur peut supprimer ce workspace.'], Response::HTTP_FORBIDDEN);
        }
        
        // Vérifier si le workspace contient des membres ou des canaux
        $memberCount = $entityManager->getRepository(WorkspaceMembers::class)
            ->count(['workspace' => $workspace]);
        $channelCount = $entityManager->getRepository(Channels::class)
            ->count(['workspace' => $workspace]);

        if ($memberCount > 0 || $channelCount > 0) {
            return $this->json(['error' => 'Impossible de supprimer un workspace contenant encore des membres ou des canaux.'], Response::HTTP_BAD_REQUEST);
        }
        
        $entityManager->remove($workspace);
        $entityManager->flush();
        
        return $this->json(['message' => 'Workspace supprimé avec succès.']);
    }
}



