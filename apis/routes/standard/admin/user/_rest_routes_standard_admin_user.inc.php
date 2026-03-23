<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 Igor Mukhin <igor.mukhin@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenApi\Annotations as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\Standard\Admin\User\AdminUserRestController;
use OpenEMR\RestControllers\Config\RestConfig;
use Psr\Http\Message\ResponseInterface;

return [
    /**
     * @todo Migrate to deepObject / something like filter[username]
     *
     * @OA\Get(
     *     path="/api/admin/user",
     *     description="Retrieves a list of users",
     *     tags={
     *         "standard",
     *         "admin",
     *         "user",
     *     },
     *     security={{"openemr_auth":{}, "bearer":{}}},
     *     parameters={
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_title"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_fname"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_lname"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_mname"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_username"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_email"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_federaltaxid"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_federaldrugid"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_upin"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_facility_id"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_facility"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_npi"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_specialty"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_billname"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_url"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_assistant"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_organization"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_valedictory"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_street"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_streetb"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_city"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_state"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_zip"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_phone"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_fax"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_phonew1"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_phonecell"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_notes"),
     *         @OA\Parameter(ref="#/components/parameters/api_standard_user_state_license_number2")
     *     },
     *     @OA\Response(response="200", ref="#/components/responses/api_standard_user_get_all_response"),
     *     @OA\Response(response="400", ref="#/components/responses/badrequest"),
     *     @OA\Response(response="401", ref="#/components/responses/unauthorized")
     * )
     */
    'GET /api/admin/user' => static function (HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');

        return AdminUserRestController::getInstance()->getAll($request);
    },

    /**
     * @OA\Get(
     *     path="/api/admin/user/{uuid}",
     *     description="Retrieves a single user by uuid",
     *     tags={
     *         "standard",
     *         "admin",
     *         "user",
     *     },
     *     security={{"openemr_auth":{}, "bearer":{}}},
     *
     *     @OA\Parameter(name="uuid", in="path", description="The uuid of the user.", required=true, schema="string"),
     *     @OA\Response(response="200", ref="#/components/responses/api_standard_user_get_one_response"),
     *     @OA\Response(response="400", ref="#/components/responses/badrequest"),
     *     @OA\Response(response="401", ref="#/components/responses/unauthorized")
     * )
     */
    'GET /api/admin/user/:uuid' => static function (string $uuid, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');

        return AdminUserRestController::getInstance()->getOne($request, $uuid);
    },

    /**
     * @OA\Post(
     *     path="/api/admin/user",
     *     description="Creates a new user",
     *     tags={
     *         "standard",
     *         "admin",
     *         "user",
     *     },
     *     security={{"openemr_auth":{}, "bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/api_standard_user_post_request"))
     *     ),
     *     @OA\Response(response="201", ref="#/components/responses/api_standard_user_post_patch_response"),
     *     @OA\Response(response="400", ref="#/components/responses/badrequest"),
     *     @OA\Response(response="401", ref="#/components/responses/unauthorized")
     * )
     */
    'POST /api/admin/user' => static function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, 'admin', 'users');

        return AdminUserRestController::getInstance()->post(
            $request,
            (array) (json_decode(file_get_contents('php://input') ?: '')),
        );
    },

    /**
     * @OA\Patch(
     *     path="/api/admin/user/{uuid}",
     *     description="Update user",
     *     tags={
     *         "standard",
     *         "admin",
     *         "user",
     *     },
     *     security={{"openemr_auth":{}, "bearer":{}}},
     *
     *     @OA\Parameter(name="uuid", in="path", description="The uuid of the user.", required=true, schema="string"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/api_standard_user_patch_request"))
     *     ),
     *     @OA\Response(response="200", ref="#/components/responses/api_standard_user_post_patch_response"),
     *     @OA\Response(response="400", ref="#/components/responses/badrequest"),
     *     @OA\Response(response="401", ref="#/components/responses/unauthorized")
     * )
     */
    'PATCH /api/admin/user/:uuid' => static function (string $uuid, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, "admin", "users");

        return AdminUserRestController::getInstance()->patch(
            $request,
            $uuid,
            (array) (json_decode(file_get_contents('php://input') ?: '')),
        );
    },

    /**
     * @OA\Delete(
     *     path="/api/admin/user/{uuid}",
     *     description="Delete an user",
     *     tags={
     *         "standard",
     *         "admin",
     *         "user",
     *     },
     *     security={{"openemr_auth":{}, "bearer":{}}},
     *
     *     @OA\Parameter(name="uuid", in="path", description="The UUID of the user.", required=true, schema="string"),
     *     @OA\Response(response="200", ref="#/components/responses/standard"),
     *     @OA\Response(response="400", ref="#/components/responses/badrequest"),
     *     @OA\Response(response="401", ref="#/components/responses/unauthorized")
     * )
     */
    "DELETE /api/admin/user/:uuid" => static function (string $uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "users");

        return AdminUserRestController::getInstance()->delete($request, $uuid);
    },
];
