<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

class UserDoc
{
    /**
     * @OA\Get(
     *     path="/api/users/search",
     *     summary="Recherche d'utilisateurs",
     *     tags={"Utilisateurs"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         description="Terme à rechercher (nom, prénom, username...)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Résultats de la recherche",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="userName", type="string"),
     *                 @OA\Property(property="firstName", type="string"),
     *                 @OA\Property(property="lastName", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Paramètre de recherche manquant")
     * )
     * @return void
     */
    public function searchUsersDoc(): void {}

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Infos de l'utilisateur connecté",
     *     tags={"Utilisateurs"},
     *     @OA\Response(
     *         response=200,
     *         description="Détails de l'utilisateur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="theme", type="boolean"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="firstName", type="string"),
     *             @OA\Property(property="lastName", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Utilisateur non connecté"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function showUserInfoDoc(): void {}

    /**
     * @OA\Put(
     *     path="/api/user",
     *     summary="Mettre à jour les infos utilisateur",
     *     tags={"Utilisateurs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="firstName", type="string", example="Nicky"),
     *             @OA\Property(property="lastName", type="string", example="Chan"),
     *             @OA\Property(property="userName", type="string", example="nicky_chan"),
     *             @OA\Property(property="emailAddress", type="string", format="email", example="nicky@example.com"),
     *             @OA\Property(property="theme", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="online")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Utilisateur mis à jour"),
     *     @OA\Response(response=401, description="Utilisateur non connecté"),
     *     security={{"bearerAuth":{}}}
     * )
     * @return void
     */
    public function updateUserDoc(): void {}

    /**
     * @OA\Delete(
     *     path="/api/user",
     *     summary="Supprimer le compte utilisateur",
     *     tags={"Utilisateurs"},
     *     @OA\Response(response=200, description="Compte supprimé"),
     *     @OA\Response(response=401, description="Utilisateur non connecté"),
     *     security={{"bearerAuth":{}}}
     * )
     * @return void
     */
    public function deleteUserDoc(): void {}

    /**
     * @OA\Get(
     *     path="/api/admin/users",
     *     summary="Liste complète des utilisateurs (admin)",
     *     tags={"Utilisateurs"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste complète des utilisateurs",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="role", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="firstName", type="string"),
     *                 @OA\Property(property="lastName", type="string")
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     * @return void
     */
    public function showAllUsersDoc(): void {}

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Liste réduite des utilisateurs (id + username)",
     *     tags={"Utilisateurs"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="username", type="string")
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     * @return void
     */
    public function listUsersDoc(): void {}
}

