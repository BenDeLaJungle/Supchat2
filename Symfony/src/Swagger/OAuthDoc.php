<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

class OAuthDoc
{
    /**
     * @OA\Get(
     *     path="/api/auth/google",
     *     summary="Redirection vers Google pour l'authentification",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=302,
     *         description="Redirection vers Google"
     *     )
     * )
     */
    public function redirectToGoogleDoc() {}

    /**
     * @OA\Get(
     *     path="/api/auth/google/check",
     *     summary="Callback après l'authentification Google",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Authentification Google réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Authentification Google réussie")
     *         )
     *     )
     * )
     */
    public function googleCheckDoc() {}

    /**
     * @OA\Get(
     *     path="/api/auth/facebook",
     *     summary="Redirection vers Facebook pour l'authentification",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=302,
     *         description="Redirection vers Facebook"
     *     )
     * )
     */
    public function redirectToFacebookDoc() {}

    /**
     * @OA\Get(
     *     path="/api/auth/facebook/check",
     *     summary="Callback après l'authentification Facebook",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Authentification Facebook réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Authentification Facebook réussie")
     *         )
     *     )
     * )
     */
    public function facebookCheckDoc() {}

    /**
     * @OA\Get(
     *     path="/api/auth/refresh",
     *     summary="Rafraîchir le token JWT",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Token JWT renouvelé",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJK... (JWT)")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token invalide ou manquant"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function refreshTokenDoc() {}
}
