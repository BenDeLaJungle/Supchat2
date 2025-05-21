<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

class WorkspacesDoc
{
    /**
     * @OA\Get(
     *     path="/api/workspaces",
     *     summary="Lister tous les workspaces",
     *     tags={"Workspaces"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des workspaces",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="creator", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="username", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function indexDoc() {}

    /**
     * @OA\Get(
     *     path="/api/workspaces/{id}",
     *     summary="Afficher un workspace spécifique",
     *     tags={"Workspaces"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du workspace",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Workspace trouvé")
     * )
     */
    public function showDoc() {}

    /**
     * @OA\Post(
     *     path="/api/workspaces",
     *     summary="Créer un nouveau workspace",
     *     tags={"Workspaces"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "status"},
     *             @OA\Property(property="name", type="string", example="Espace de travail Nicky"),
     *             @OA\Property(property="status", type="string", example="public")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Workspace créé"),
     *     @OA\Response(response=400, description="Données manquantes"),
     *     @OA\Response(response=409, description="Workspace déjà existant"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function createDoc() {}

    /**
     * @OA\Delete(
     *     path="/api/workspaces/{id}",
     *     summary="Supprimer un workspace",
     *     tags={"Workspaces"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du workspace",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Workspace supprimé"),
     *     @OA\Response(response=403, description="Pas autorisé à supprimer ce workspace"),
     *     @OA\Response(response=400, description="Workspace non vide"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function deleteDoc() {}

    /**
     * @OA\Get(
     *     path="/api/workspaces/{id}/generate-invite",
     *     summary="Générer un lien d'invitation pour un workspace",
     *     tags={"Workspaces"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du workspace",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lien généré",
     *         @OA\JsonContent(
     *             @OA\Property(property="invite_link", type="string", example="http://localhost:5173/invite/...")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function generateInviteLinkDoc() {}

    /**
     * @OA\Post(
     *     path="/api/workspaces/invite/{token}",
     *     summary="Accepter un lien d'invitation",
     *     tags={"Workspaces"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         description="Token du lien d'invitation",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Lien valide"),
     *     @OA\Response(response=400, description="Lien invalide ou expiré")
     * )
     */
    public function acceptInviteDoc() {}
}
