<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

class RolesDoc
{
    /**
     * @OA\Get(
     *     path="/roles",
     *     summary="Liste de tous les rôles",
     *     tags={"Rôles"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des rôles",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="publish", type="boolean"),
     *                 @OA\Property(property="moderate", type="boolean"),
     *                 @OA\Property(property="manage", type="boolean")
     *             )
     *         )
     *     )
     * )
     */
    public function listRolesDoc() {}

    /**
     * @OA\Get(
     *     path="/roles/{id}",
     *     summary="Afficher un rôle spécifique",
     *     tags={"Rôles"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Détails du rôle"),
     *     @OA\Response(response=404, description="Rôle non trouvé")
     * )
     */
    public function detailRoleDoc() {}

    /**
     * @OA\Post(
     *     path="/roles",
     *     summary="Créer un nouveau rôle",
     *     tags={"Rôles"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "publish", "moderate", "manage"},
     *             @OA\Property(property="name", type="string", example="Admin"),
     *             @OA\Property(property="publish", type="boolean", example=true),
     *             @OA\Property(property="moderate", type="boolean", example=true),
     *             @OA\Property(property="manage", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Rôle créé avec succès"),
     *     @OA\Response(response=400, description="Données invalides ou format incorrect"),
     *     @OA\Response(response=415, description="Content-Type invalide")
     * )
     */
    public function createRoleDoc() {}

    /**
     * @OA\Put(
     *     path="/roles/{id}",
     *     summary="Mettre à jour un rôle",
     *     tags={"Rôles"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="publish", type="boolean"),
     *             @OA\Property(property="moderate", type="boolean"),
     *             @OA\Property(property="manage", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Rôle mis à jour avec succès"),
     *     @OA\Response(response=400, description="Données invalides"),
     *     @OA\Response(response=404, description="Rôle non trouvé")
     * )
     */
    public function updateRoleDoc() {}

    /**
     * @OA\Delete(
     *     path="/roles/{id}",
     *     summary="Supprimer un rôle",
     *     tags={"Rôles"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Rôle supprimé avec succès"),
     *     @OA\Response(response=400, description="Le rôle est encore attribué à des utilisateurs"),
     *     @OA\Response(response=404, description="Rôle non trouvé")
     * )
     */
    public function deleteRoleDoc() {}
}
