<?php

namespace App\Controller;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="SupchatAPI",
 *     version="0.9",
 *     description="une api de chat créée par des étudiant"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class ApiDocController
{
    //héberge les annotations OpenAPI.laisser cet classe vide svp!
}