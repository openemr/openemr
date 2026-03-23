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
use OpenEMR\RestControllers\Standard\Admin\Acl\AdminAclGroupMemberRestController;
use OpenEMR\RestControllers\Config\RestConfig;
use Psr\Http\Message\ResponseInterface;

/**
 * @OA\Schema(
 *     schema="api_standard_admin_acl_group_member_data",
 *     type="object",
 *
 *     @OA\Property(property="id", description="User ID.", type="integer"),
 *     @OA\Property(property="uuid", description="User UUID.", type="string"),
 *     @OA\Property(property="fname", description="First name.", type="string"),
 *     @OA\Property(property="mname", description="Middle name.", type="string"),
 *     @OA\Property(property="lname", description="Last name.", type="string"),
 *     @OA\Property(property="email", description="Email.", type="string"),
 *     @OA\Property(property="username", description="Username.", type="string"),
 *
 *     example={
 *         "id": 1,
 *         "uuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
 *         "fname": "Administrator",
 *         "mname": "",
 *         "lname": "Administrator",
 *         "email": "admin@example.com",
 *         "username": "admin"
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="api_standard_admin_acl_group_member_request",
 *     type="object",
 *
 *     @OA\Property(property="order", description="Member sort order. Optional. 0 by default.", type="integer"),
 *     @OA\Property(property="hidden", description="Is Member hidden? Optional. False by default.", type="boolean"),
 *
 *     example={
 *         "order": 0,
 *         "hidden": false
 *     }
 * )
 *
 * @OA\Response(
 *     response="api_standard_admin_acl_group_member_get_all_response",
 *     description="ACL Group Members List Response",
 *     @OA\MediaType(
 *         mediaType="application/json",
 *         @OA\Schema(
 *             @OA\Property(property="validationErrors", description="Validation errors.", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="internalErrors", description="Internal errors.", type="array", @OA\Items(type="object")),
 *             @OA\Property(
 *                 property="data",
 *                 description="Returned data.",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/api_standard_admin_acl_group_member_data")
 *             ),
 *             example={
 *                 "validationErrors": {},
 *                 "internalErrors": {},
 *                 "data": {{
 *                     "id": 1,
 *                     "uuid": "90cde167-7b9b-4ed1-bd55-533925cb2605",
 *                     "fname": "Administrator",
 *                     "mname": "",
 *                     "lname": "Administrator",
 *                     "email": "admin@example.com",
 *                     "username": "admin"
 *                 }}
 *             }
 *         )
 *     )
 * )
 */
return [
    /**
     * @OA\Get(
     *     path="/api/admin/acl/group/{groupId}/member",
     *     description="Retrieves a list of Group Members (Users) by Group ID",
     *     tags={
     *         "standard",
     *         "admin",
     *         "ACL",
     *     },
     *     security={{"openemr_auth":{}, "bearer":{}}},
     *
     *     @OA\Parameter(name="groupId", in="path", description="Group ID.", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response="200", ref="#/components/responses/api_standard_admin_acl_group_member_get_all_response"),
     *     @OA\Response(response="400", ref="#/components/responses/badrequest"),
     *     @OA\Response(response="401", ref="#/components/responses/unauthorized")
     * )
     */
    'GET /api/admin/acl/group/:groupId/member' => static function (string $groupId, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'groups');

        return AdminAclGroupMemberRestController::getInstance()->getAll((int) $groupId, $request);
    },

    /**
     * @OA\Post(
     *     path="/api/admin/acl/group/{groupId}/member/{uuid}",
     *     description="Add Member (User) to Group by Group ID and User UUID",
     *     tags={
     *         "standard",
     *         "admin",
     *         "ACL",
     *     },
     *     security={{"openemr_auth":{}, "bearer":{}}},
     *
     *     @OA\Parameter(name="groupId", in="path", description="Group ID.", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="uuid", in="path", description="User UUID.", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/api_standard_admin_acl_group_member_request"))
     *     ),
     *     @OA\Response(response="201", ref="#/components/responses/standard"),
     *     @OA\Response(response="400", ref="#/components/responses/badrequest"),
     *     @OA\Response(response="401", ref="#/components/responses/unauthorized")
     * )
     */
    'POST /api/admin/acl/group/:groupId/member/:uuid' => static function (string $groupId, string $uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, 'admin', 'groups');
        $data = (array) (json_decode(file_get_contents('php://input') ?: ''));

        return AdminAclGroupMemberRestController::getInstance()->post((int) $groupId, $uuid, $data, $request);
    },

    /**
     * @OA\Delete(
     *     path="/api/admin/acl/group/{groupId}/member/{uuid}",
     *     description="Delete a Group Member (User) from Group by Group ID and User UUID",
     *     tags={
     *         "standard",
     *         "admin",
     *         "ACL",
     *     },
     *     security={{"openemr_auth":{}, "bearer":{}}},
     *
     *     @OA\Parameter(name="groupId", in="path", description="Group ID.", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="uuid", in="path", description="User UUID.", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response="200", ref="#/components/responses/standard"),
     *     @OA\Response(response="400", ref="#/components/responses/badrequest"),
     *     @OA\Response(response="401", ref="#/components/responses/unauthorized")
     * )
     */
    "DELETE /api/admin/acl/group/:groupId/member/:uuid" => static function (string $groupId, string $uuid, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "groups");

        return AdminAclGroupMemberRestController::getInstance()->delete($request, $groupId, $uuid);
    },
];
