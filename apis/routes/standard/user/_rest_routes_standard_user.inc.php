<?php

/**
 * Standard User API Routes
 *
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 Igor Mukhin <igor.mukhin@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenApi\Annotations as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\Standard\User\UserRestController;
use Psr\Http\Message\ResponseInterface;
use OpenEMR\Common\Auth\AuthorizedUserRetriever;

return [
    /**
     * @OA\Get(
     *     path="/api/user/me",
     *     description="Retrieves single logged in user",
     *     tags={
     *         "standard",
     *         "user",
     *     },
     *     security={{"openemr_auth":{}, "bearer":{}}},
     *
     *     @OA\Response(response="200", ref="#/components/responses/api_standard_user_get_one_response"),
     *     @OA\Response(response="400", ref="#/components/responses/badrequest"),
     *     @OA\Response(response="401", ref="#/components/responses/unauthorized")
     * )
     */
    'GET /api/user/me' => static function (HttpRestRequest $request): ResponseInterface {
        return UserRestController::getInstance()->getOne(
            $request,
            AuthorizedUserRetriever::getInstance()->getAuthorizedUserUuidFromRequest($request),
        );
    },

    /**
     * @OA\Patch(
     *     path="/api/user/me",
     *     description="Update logged in user",
     *     tags={
     *         "standard",
     *         "user",
     *     },
     *     security={{"openemr_auth":{}, "bearer":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/api_standard_user_patch_request"))
     *     ),
     *     @OA\Response(response="200", ref="#/components/responses/api_standard_user_post_patch_response"),
     *     @OA\Response(response="400", ref="#/components/responses/badrequest"),
     *     @OA\Response(response="401", ref="#/components/responses/unauthorized")
     * )
     */
    'PATCH /api/user/me' => static function (HttpRestRequest $request): ResponseInterface {
        return UserRestController::getInstance()->patch(
            $request,
            AuthorizedUserRetriever::getInstance()->getAuthorizedUserUuidFromRequest($request),
            (array) (json_decode(file_get_contents('php://input') ?: '')),
        );
    },
];
