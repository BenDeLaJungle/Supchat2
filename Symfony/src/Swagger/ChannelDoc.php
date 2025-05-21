<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

class ChannelDoc
{
    /**
     * @OA\Get(
     *     path="/channels/{id}",
     *     summary="Obtenir les infos d’un canal",
     *     tags={"Canaux"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Infos du canal",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="workspace", type="integer")
     *         )
     *     )
     * )
     */
    public function getChannelDoc() {}

    /**
     * @OA\Get(
     *     path="/workspaces/{id}/channels",
     *     summary="Lister les canaux d’un workspace",
     *     tags={"Canaux"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des canaux",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="status", type="string")
     *         ))
     *     )
     * )
     */
    public function getChannelsByWorkspaceDoc() {}

    /**
     * @OA\Post(
     *     path="/api/channels",
     *     summary="Créer un nouveau canal",
     *     tags={"Canaux"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "workspace_id", "status"},
     *             @OA\Property(property="name", type="string", example="Général"),
     *             @OA\Property(property="workspace_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", example="public")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Canal créé avec succès"),
     *     @OA\Response(response=400, description="Données incomplètes"),
     *     @OA\Response(response=404, description="Workspace introuvable"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function createChannelDoc() {}

    /**
     * @OA\Put(
     *     path="/channels/{id}",
     *     summary="Mettre à jour un canal",
     *     tags={"Canaux"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Canal mis à jour"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function updateChannelDoc() {}

    /**
     * @OA\Delete(
     *     path="/channels/{id}",
     *     summary="Supprimer un canal",
     *     tags={"Canaux"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Canal supprimé"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function deleteChannelDoc() {}

    /**
     * @OA\Post(
     *     path="/channels/{id}/privilege",
     *     summary="Vérifier les privilèges d’un utilisateur sur un canal",
     *     tags={"Canaux"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer", example=12)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Privilèges renvoyés",
     *         @OA\JsonContent(
     *             @OA\Property(property="is_admin", type="boolean"),
     *             @OA\Property(property="can_moderate", type="boolean"),
     *             @OA\Property(property="can_manage", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=400, description="ID utilisateur requis"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function getChannelPrivilegeDoc() {}
}
