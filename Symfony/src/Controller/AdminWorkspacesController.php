<?php

namespace App\Controller;

use App\Entity\Workspaces;
use App\Repository\WorkspacesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/api/admin/workspaces')]
#[IsGranted('ROLE_ADMIN')]
class AdminWorkspacesController extends AbstractController
{
    #[Route('', name: 'admin_workspaces_list', methods: ['GET'])]
    public function listAll(WorkspacesRepository $workspaceRepository): JsonResponse
    {
        $workspaces = $workspaceRepository->findAll();

        $data = array_map(function ($workspace) {
            return [
                'id' => $workspace->getId(),
                'name' => $workspace->getName(),
                'status' => $workspace->getStatus(),
            ];
        }, $workspaces);

        return new JsonResponse($data);
    }

    #[Route('/{id}', name: 'admin_workspace_soft_delete', methods: ['DELETE'])]
    public function softDelete(int $id, WorkspacesRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $workspace = $repo->find($id);
        if (!$workspace) {
            return new JsonResponse(['error' => 'Workspace non trouvé'], 404);
        }

        $workspace->setStatus(false);
        $em->flush();

        return new JsonResponse(['message' => 'Workspace désactivé avec succès']);
    }
	#[Route('/{id}', name: 'admin_workspace_update', methods: ['PUT'])]
	public function update(int $id, Request $request, WorkspacesRepository $repo, EntityManagerInterface $em): JsonResponse
	{
		$workspace = $repo->find($id);
		if (!$workspace) {
			return new JsonResponse(['error' => 'Workspace non trouvé'], 404);
		}

		$data = json_decode($request->getContent(), true);
		if (!$data) {
			return new JsonResponse(['error' => 'Requête invalide'], 400);
		}

		if (isset($data['name'])) {
			$workspace->setName($data['name']);
		}

		if (isset($data['status'])) {
			$workspace->setStatus((bool) $data['status']);
		}

		$em->flush();

		return new JsonResponse([
			'message' => 'Workspace mis à jour avec succès',
			'id'      => $workspace->getId(),
			'name'    => $workspace->getName(),
			'status'  => $workspace->getStatus(),
		]);
	}

}
