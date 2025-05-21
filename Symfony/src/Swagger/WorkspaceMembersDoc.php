<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

class WorkspaceMembersDoc
{
    /**
     * @OA\Get(
     *     path="/workspaces/{workspaceId}/members",
     *     summary="Lister les membres d'un workspace",
     *     tags={"Membres de workspace"},
     *     @OA\Parameter(
     *         name="workspaceId",
     *         in="path",
     *         required=true,
     *         description="ID du workspace",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des membres",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="user_name", type="string"),
     *             @OA\Property(property="role_id", type="integer", nullable=true),
     *             @OA\Property(property="publish", type="boolean"),
     *             @OA\Property(property="moderate", type="boolean"),
     *             @OA\Property(property="manage", type="boolean")
     *         ))
     *     ),
     *     @OA\Response(response=404, description="Workspace non trouvé"),
     *     security={{"bearerAuth":{}}}
     * )
     * @return void
     */
    public function listMembersDoc(): void {}

    /**
     * @OA\Post(
     *     path="/workspaces/{workspaceId}/members",
     *     summary="Ajouter un membre à un workspace",
     *     tags={"Membres de workspace"},
     *     @OA\Parameter(name="workspaceId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "role_id"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="role_id", type="integer"),
     *             @OA\Property(property="publish", type="boolean", example=false),
     *             @OA\Property(property="moderate", type="boolean", example=false),
     *             @OA\Property(property="manage", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Membre ajouté avec succès"),
     *     @OA\Response(response=400, description="JSON invalide ou données manquantes"),
     *     @OA\Response(response=404, description="Workspace, utilisateur ou rôle non trouvé"),
     *     @OA\Response(response=409, description="Utilisateur déjà membre"),
     *     security={{"bearerAuth":{}}}
     * )
     * @return void
     */
    public function createMemberDoc(): void {}

    /**
     * @OA\Get(
     *     path="/workspaces/{workspaceId}/members/{memberId}",
     *     summary="Récupérer les infos d'un membre",
     *     tags={"Membres de workspace"},
     *     @OA\Parameter(name="workspaceId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="memberId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Infos du membre"),
     *     @OA\Response(response=404, description="Workspace ou membre non trouvé"),
     *     security={{"bearerAuth":{}}}
     * )
     * @return void
     */
    public function getMemberDoc(): void {}

    /**
     * @OA\Put(
     *     path="/workspaces/{workspaceId}/members/{memberId}",
     *     summary="Mettre à jour un membre d'un workspace",
     *     tags={"Membres de workspace"},
     *     @OA\Parameter(name="workspaceId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="memberId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="role_id", type="integer"),
     *             @OA\Property(property="publish", type="boolean"),
     *             @OA\Property(property="moderate", type="boolean"),
     *             @OA\Property(property="manage", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Membre mis à jour"),
     *     @OA\Response(response=400, description="JSON invalide"),
     *     @OA\Response(response=404, description="Membre ou ressource non trouvée"),
     *     security={{"bearerAuth":{}}}
     * )
     * @return void
     */
    public function updateMemberDoc(): void {}

    /**
     * @OA\Delete(
     *     path="/workspaces/{workspaceId}/members/{memberId}",
     *     summary="Supprimer un membre du workspace",
     *     tags={"Membres de workspace"},
     *     @OA\Parameter(name="workspaceId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="memberId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Membre supprimé"),
     *     @OA\Response(response=403, description="Impossible de supprimer le créateur"),
     *     @OA\Response(response=404, description="Membre ou workspace non trouvé"),
     *     security={{"bearerAuth":{}}}
     * )
     * @return void
     */
    public function deleteMemberDoc(): void {}
}

