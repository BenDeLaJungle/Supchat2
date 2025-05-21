<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Authentification",
 *     description="Gestion des utilisateurs : inscription, connexion, déconnexion, refresh"
 * )
 */
class AuthDoc
{
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Inscription d'un nouvel utilisateur",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"firstName", "lastName", "userName", "emailAddress", "password"},
     *             @OA\Property(property="firstName", type="string", example="Nicky"),
     *             @OA\Property(property="lastName", type="string", example="Chan"),
     *             @OA\Property(property="userName", type="string", example="nicky_chan"),
     *             @OA\Property(property="emailAddress", type="string", format="email", example="nicky@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="MotDePasse123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="userId", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Données incomplètes"),
     *     @OA\Response(response=409, description="Email déjà utilisé")
     * )
     */
    public function registerDoc() {}

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Connexion utilisateur",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"emailAddress", "password"},
     *             @OA\Property(property="emailAddress", type="string", example="nicky@example.com"),
     *             @OA\Property(property="password", type="string", example="MotDePasse123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="userName", type="string"),
     *                 @OA\Property(property="emailAddress", type="string"),
     *                 @OA\Property(property="role", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Données incomplètes"),
     *     @OA\Response(response=401, description="Identifiants invalides")
     * )
     */
    public function loginDoc() {}

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Déconnexion utilisateur",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     )
     * )
     */
    public function logoutDoc() {}

    /**
     * @OA\Get(
     *     path="/api/auth/refresh",
     *     summary="Rafraîchir un token JWT expiré",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token invalide ou manquant"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function refreshDoc() {}
}
