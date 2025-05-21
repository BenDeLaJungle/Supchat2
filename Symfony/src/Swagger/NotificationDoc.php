<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

class NotificationDoc
{
    /**
     * @OA\Get(
     *     path="/api/notifications/unread",
     *     summary="Récupérer les notifications non lues de l'utilisateur connecté",
     *     tags={"Notifications"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des notifications non lues",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer", example=42),
     *             @OA\Property(property="message", type="string", example="Coucou, t'as un nouveau message !"),
     *             @OA\Property(property="read", type="boolean", example=false)
     *         ))
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function getUnreadNotificationsDoc() {}

    /**
     * @OA\Post(
     *     path="/api/notifications/create",
     *     summary="Créer une notification liée à un message pour un utilisateur",
     *     tags={"Notifications"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"userId", "messageId"},
     *             @OA\Property(property="userId", type="integer", example=1),
     *             @OA\Property(property="messageId", type="integer", example=12)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="id", type="integer", example=99)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Utilisateur ou message introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Utilisateur ou message introuvable")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function createNotificationDoc() {}
}
