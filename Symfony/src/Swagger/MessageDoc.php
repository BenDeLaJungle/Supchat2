<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

class MessageDoc
{
    /**
     * @OA\Post(
     *     path="/messages",
     *     summary="Envoyer un message dans un canal",
     *     tags={"Messages"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"channel_id", "user_id", "content"},
     *             @OA\Property(property="channel_id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="content", type="string", example="Hello world !")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Message envoyé avec succès"),
     *     @OA\Response(response=400, description="Données incomplètes"),
     *     @OA\Response(response=404, description="Channel ou utilisateur non trouvé"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function createMessageDoc() {}

    /**
     * @OA\Get(
     *     path="/messages/{id}",
     *     summary="Récupérer un message par ID",
     *     tags={"Messages"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Message récupéré",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="timestamp", type="string", format="date-time"),
     *             @OA\Property(property="author", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="username", type="string")
     *             ),
     *             @OA\Property(property="channel_id", type="integer")
     *         )
     *     )
     * )
     */
    public function getMessageDoc() {}

    /**
     * @OA\Get(
     *     path="/channels/{id}/messages",
     *     summary="Récupérer les messages d’un canal (avec pagination)",
     *     tags={"Messages"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", default=20)),
     *     @OA\Parameter(name="before", in="query", @OA\Schema(type="string", format="date-time")),
     *     @OA\Parameter(name="before_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des messages paginés",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="timestamp", type="string"),
     *             @OA\Property(property="author", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="username", type="string")
     *             ),
     *             @OA\Property(property="channel_id", type="integer")
     *         ))
     *     ),
     *     @OA\Response(response=400, description="Date invalide"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function getMessagesForChannelDoc() {}

    /**
     * @OA\Put(
     *     path="/messages/{id}",
     *     summary="Modifier un message existant",
     *     tags={"Messages"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="Message édité")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Message modifié"),
     *     @OA\Response(response=400, description="Contenu manquant"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function updateMessageDoc() {}

    /**
     * @OA\Delete(
     *     path="/messages/{id}",
     *     summary="Supprimer un message",
     *     tags={"Messages"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Message supprimé"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function deleteMessageDoc() {}
}
