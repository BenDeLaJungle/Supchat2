<?php

namespace App\Controller;

use App\Entity\Roles;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/roles')]
class RolesController extends AbstractController
{
    private function formatRoleResponse(Roles $role): array
    {
        return [
            'id'       => $role->getId(),
            'name'     => $role->getName(),
            'publish'  => $role->canPublish(),
            'moderate' => $role->canModerate(),
            'manage'   => $role->canManage(),
        ];
    }

    #[Route('', name: 'roles_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $roles = $em->getRepository(Roles::class)->findAll();
        return $this->json(array_map([$this, 'formatRoleResponse'], $roles));
    }

    #[Route('/{id}', name: 'roles_detail', methods: ['GET'])]
    public function detail(int $id, EntityManagerInterface $em): JsonResponse
    {
        $role = $em->getRepository(Roles::class)->find($id);
        if (!$role) {
            return $this->json(['error' => 'Rôle non trouvé'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($this->formatRoleResponse($role));
    }

    #[Route('', name: 'roles_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        if ($request->headers->get('Content-Type') !== 'application/json') {
            return $this->json(['error' => 'Content-Type invalide, JSON requis'], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }
        
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE || !$data || !isset($data['name'], $data['publish'], $data['moderate'], $data['manage'])) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        $role = new Roles();
        $role->setName($data['name'])
             ->setPublish((bool)$data['publish'])
             ->setModerate((bool)$data['moderate'])
             ->setManage((bool)$data['manage']);

        $em->persist($role);
        $em->flush();

        return $this->json([
            'message' => 'Rôle créé avec succès',
            'role'    => $this->formatRoleResponse($role)
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'roles_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $role = $em->getRepository(Roles::class)->find($id);
        if (!$role) {
            return $this->json(['error' => 'Rôle non trouvé'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE || !$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['name'])) {
            $role->setName($data['name']);
        }
        if (isset($data['publish'])) {
            $role->setPublish((bool)$data['publish']);
        }
        if (isset($data['moderate'])) {
            $role->setModerate((bool)$data['moderate']);
        }
        if (isset($data['manage'])) {
            $role->setManage((bool)$data['manage']);
        }

        $em->flush();

        return $this->json([
            'message' => 'Rôle mis à jour avec succès',
            'role'    => $this->formatRoleResponse($role)
        ]);
    }

    #[Route('/{id}', name: 'roles_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $role = $em->getRepository(Roles::class)->find($id);
        if (!$role) {
            return $this->json(['error' => 'Rôle non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $users = $em->getRepository(Users::class)->findBy(['role' => $role]);
        if (!empty($users)) {
            return $this->json(['error' => 'Impossible de supprimer ce rôle car il est encore attribué à des utilisateurs'], Response::HTTP_BAD_REQUEST);
        }

        $em->remove($role);
        $em->flush();

        return $this->json(['message' => 'Rôle supprimé avec succès']);
    }
}


