<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Files;

#[Route('/file')]
class FileController extends AbstractController
{
    #[Route('/upload', name: 'file_upload', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $em): Response
    {
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'Aucun fichier reçu'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier la validité du fichier
        $allowedExtensions = ['jpg', 'png', 'pdf', 'docx'];
        $extension = $file->guessExtension();

        if (!in_array($extension, $allowedExtensions)) {
            return new JsonResponse(['error' => 'Format de fichier non autorisé'], Response::HTTP_BAD_REQUEST);
        }

        // Générer un nom unique pour éviter les conflits
        $safeFileName = md5(uniqid()) . '.' . $extension;
        $uploadDir = $this->getParameter('uploads_directory');

        // Déplacer le fichier dans le répertoire sécurisé
        $file->move($uploadDir, $safeFileName);

        // Créer une nouvelle instance de l'entité `Files`
        $fileEntity = new Files();
        $fileEntity->setFilePath('/uploads/' . $safeFileName);

        $em->persist($fileEntity);
        $em->flush();

        return new JsonResponse([
            'message' => 'Fichier uploadé avec succès',
            'file' => [
                'id' => $fileEntity->getId(),
                'path' => $fileEntity->getFilePath(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'file_get', methods: ['GET'])]
    public function getFile(int $id, EntityManagerInterface $em): Response
    {
        $file = $em->getRepository(Files::class)->find($id);

        if (!$file) {
            return new JsonResponse(['error' => 'Fichier introuvable'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $file->getId(),
            'path' => $file->getFilePath()
        ]);
    }

    #[Route('/{id}', name: 'file_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $file = $em->getRepository(Files::class)->find($id);

        if (!$file) {
            return new JsonResponse(['error' => 'Fichier introuvable'], Response::HTTP_NOT_FOUND);
        }

        $filePath = $this->getParameter('uploads_directory') . '/' . basename($file->getFilePath());

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $em->remove($file);
        $em->flush();

        return new JsonResponse(['message' => 'Fichier supprimé avec succès']);
    }
}
