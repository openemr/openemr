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

return [
    /**
     * @OA\Get(
     *     path="/api/admin/acl/group/{groupId}/member",
     *     description="Retrieves a list of Group Members - Users",
     *     tags={
     *         "standard",
     *         "admin",
     *         "ACL",
     *     },
     *
     *     @OA\Parameter(
     *         name="groupId",
     *         in="path",
     *         description="Group ID.",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         ref="#/components/responses/standard"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         ref="#/components/responses/badrequest"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         ref="#/components/responses/unauthorized"
     *     ),
     *     security={{"openemr_auth":{}}}
     *  )
     */
    'GET /api/admin/acl/group/:groupId/member' => static function (string $groupId, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'groups');

        return AdminAclGroupMemberRestController::getInstance()->getAll((int) $groupId, $request);
    },

    /**
     * Schema for the group request
     *
     * @OA\Schema(
     *     schema="api_group_member_request",
     *
     *     @OA\Property(
     *         property="order",
     *         description="Member sort order. Optional. 0 by default.",
     *         type="integer"
     *     ),
     *     @OA\Property(
     *         property="hidden",
     *         description="Is Member hidden? Optional. False by defailt.",
     *         type="boolean"
     *     ),
     *     required={},
     *     example={
     *         "order": 0,
     *         "hidden": false
     *     }
     *  )
     */

    /**
     * @OA\Post(
     *     path="/api/admin/acl/group/{groupId}/member",
     *     description="Add Member (User) to Group",
     *     tags={
     *         "standard",
     *         "admin",
     *         "ACL",
     *     },
     *
     *     @OA\Parameter(
     *         name="groupId",
     *         in="path",
     *         description="Group ID.",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         description="User ID.",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/api_group_member_request")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         ref="#/components/responses/standard"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         ref="#/components/responses/unauthorized"
     *     ),
     *     security={{"openemr_auth":{}}}
     *  )
     */
    'POST /api/admin/acl/group/:groupId/member/:userId' => static function (string $groupId, string $userId, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, 'admin', 'groups');
        $data = (array) (json_decode(file_get_contents('php://input')));

        return AdminAclGroupMemberRestController::getInstance()->post((int) $groupId, (int) $userId, $data, $request);
    },

    /**
     * @OA\Delete(
     *     path="/api/admin/acl/group/{groupId}/member/{userId}",
     *     description="Delete a Group Member (User) by ID",
     *     tags={
     *         "standard",
     *         "admin",
     *         "ACL",
     *     },
     *     @OA\Parameter(
     *         name="groupId",
     *         in="path",
     *         description="Group ID.",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         description="User ID.",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         ref="#/components/responses/standard"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         ref="#/components/responses/badrequest"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         ref="#/components/responses/unauthorized"
     *     ),
     *     security={{"openemr_auth":{}}}
     *  )
     */
    "DELETE /api/admin/acl/group/:groupId/member/:userId" => static function (string $groupId, string $userId, HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, "admin", "groups");

        return AdminAclGroupMemberRestController::getInstance()->delete($request, (int) $groupId, (int) $userId);
    },
];
