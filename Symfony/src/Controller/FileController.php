<?php

namespace App\Controller;

use App\Entity\Files;
use App\Service\FileUploader;
use App\Repository\MessagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FileController extends AbstractController
{
    #[Route('/api/files/upload', name: 'file_upload', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function upload(
        Request $request,
        FileUploader $fileUploader,
        MessagesRepository $messagesRepo,
        EntityManagerInterface $em
    ): Response {
        $file = $request->files->get('file');
        $messageId = $request->request->get('message_id');

        if (!$file || !$messageId) {
            return new JsonResponse(['error' => 'Fichier ou message_id manquant'], 400);
        }

        
        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['error' => 'Type de fichier interdit'], 400);
        }

        // Upload
        try {
            $filePath = $fileUploader->upload($file);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Échec de l\'upload'], 500);
        }

        // Lier au message
        $message = $messagesRepo->find($messageId);
        if (!$message) {
            return new JsonResponse(['error' => 'Message introuvable'], 404);
        }

        // Créer l’entrée Files
        $fileEntity = new Files();
        $fileEntity->setMessage($message);
        $fileEntity->setFilePath($filePath);

        $em->persist($fileEntity);
        $em->flush();

        return new JsonResponse(['success' => true, 'filePath' => $filePath], 201);
    }

	#[Route('/api/files/me', name: 'files_me', methods: ['GET'])]
	#[IsGranted('IS_AUTHENTICATED_FULLY')]
	public function filesMe(EntityManagerInterface $em): Response
	{
		$user = $this->getUser();

		// On passe par les messages de l'utilisateur pour retrouver ses fichiers
		$qb = $em->createQueryBuilder();
		$qb->select('f')
			->from(Files::class, 'f')
			->join('f.message', 'm')
			->where('m.user = :user')
			->setParameter('user', $user);

		$files = $qb->getQuery()->getResult();

		$data = array_map(fn($f) => [
			'id' => $f->getId(),
			'path' => $f->getFilePath(),
			'name' => explode('__', basename($f->getFilePath()))[1] ?? 'Fichier',
		], $files);

		return new JsonResponse($data);
	}
	
	#[Route('/api/files/shared', name: 'files_shared', methods: ['GET'])]
	#[IsGranted('IS_AUTHENTICATED_FULLY')]
	public function filesShared(EntityManagerInterface $em): Response
	{
		$user = $this->getUser();

		$qb = $em->createQueryBuilder();
		$qb->select('f', 'm', 'u')
			->from(Files::class, 'f')
			->join('f.message', 'm')
			->join('m.channel', 'c')
			->join('c.workspace', 'w')
			->join('App\Entity\WorkspaceMembers', 'wm', 'WITH', 'wm.workspace = w AND wm.user = :user')
			->join('m.user', 'u')
			->where('m.user != :user')
			->setParameter('user', $user);

		$files = $qb->getQuery()->getResult();

		$data = array_map(fn($f) => [
			'id' => $f->getId(),
			'path' => $f->getFilePath(),
			'name' => explode('__', basename($f->getFilePath()))[1] ?? 'Fichier',
			'author' => $f->getMessage()->getUser()?->getUsername() ?? 'Inconnu',
		], $files);

		return new JsonResponse($data);
	}
	
	#[Route('/api/files/{id}/generate-download-url', name: 'file_generate_download', methods: ['GET'])]
	#[IsGranted('IS_AUTHENTICATED_FULLY')]
	public function generateDownloadUrl(int $id, UserInterface $user): JsonResponse
	{
		/** @var \App\Entity\Users $user */
		$userId = $user->getId();
		$timestamp = time();
		$secret = $_ENV['APP_SECRET'];

		$data = "id=$id&user=$userId&ts=$timestamp";
		$hash = hash_hmac('sha256', $data, $secret);

		$signedUrl = $this->generateUrl('file_secure_download', [
			'id' => $id,
			'user' => $userId,
			'ts' => $timestamp,
			'_hash' => $hash
		], UrlGeneratorInterface::ABSOLUTE_URL);

		return new JsonResponse(['url' => $signedUrl]);
	}
    
	#[Route('/api/files/download', name: 'file_secure_download', methods: ['GET'])]
	public function secureDownload(Request $request, EntityManagerInterface $em): Response
	{
		$id = $request->query->get('id');
		$userId = $request->query->get('user');
		$timestamp = $request->query->get('ts');
		$hash = $request->query->get('_hash');

		if (!$id || !$userId || !$timestamp || !$hash) {
			return new JsonResponse(['error' => 'Paramètres manquants'], 400);
		}

		// Vérification de la validité du hash
		$secret = $_ENV['APP_SECRET'];
		$data = "id=$id&user=$userId&ts=$timestamp";
		$expectedHash = hash_hmac('sha256', $data, $secret);

		if (!hash_equals($expectedHash, $hash)) {
			return new JsonResponse(['error' => 'Signature invalide'], 403);
		}

		// Vérification de l'expiration (10min)
		if (abs(time() - (int)$timestamp) > 600) {
			return new JsonResponse(['error' => 'Lien expiré'], 403);
		}

		// Récupération du fichier
		$file = $em->getRepository(Files::class)->find($id);
		if (!$file) {
			return new JsonResponse(['error' => 'Fichier introuvable'], 404);
		}

		$filePath = $this->getParameter('upload_directory') . '/' . basename($file->getFilePath());

		if (!file_exists($filePath)) {
			return new JsonResponse(['error' => 'Fichier absent sur le disque'], 404);
		}

		return $this->file($filePath, basename($filePath));
	}


	

	#[Route('/api/files/{id}', name: 'file_delete', methods: ['DELETE'])]
	#[IsGranted('IS_AUTHENTICATED_FULLY')]
	public function delete(int $id, EntityManagerInterface $em, UserInterface $user): Response
	{
		$file = $em->getRepository(Files::class)->find($id);

		if (!$file) {
			return new JsonResponse(['error' => 'Fichier introuvable'], Response::HTTP_NOT_FOUND);
		}

		// Vérifie que l'utilisateur connecté est bien l'auteur du message
        /** @var \App\Entity\Users $user */
		if ($file->getMessage()->getUser()->getId() !== $user->getId()) {
			return new JsonResponse(['error' => 'Vous ne pouvez supprimer que vos fichiers.'], 403);
		}

		$filePath = $this->getParameter('upload_directory') . '/' . basename($file->getFilePath());

		if (file_exists($filePath)) {
			unlink($filePath);
		}

		$em->remove($file);
		$em->flush();

		return new JsonResponse(['message' => 'Fichier supprimé avec succès']);
	}

}
