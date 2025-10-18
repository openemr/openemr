<?php

/**
 * Standard Group API Routes
 *
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
use OpenEMR\RestControllers\AclGroupRestController;
use OpenEMR\Services\Acl\AclGroupService;
use Psr\Http\Message\ResponseInterface;

return [
    /**
     * @OA\Get(
     *     path="/api/admin/group",
     *     description="Retrieves a list of groups",
     *     tags={
     *         "standard",
     *         "admin",
     *         "ACL",
     *     },
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
    'GET /api/admin/group' => static function (HttpRestRequest $request): ResponseInterface {
//        RestConfig::request_authorization_check($request, 'admin', 'groups');

        return (new AclGroupRestController())->getAll($request);
    },

    /**
     * @OA\Get(
     *     path="/api/admin/group/{id}",
     *     description="Retrieves a single Group by ID",
     *     tags={
     *         "standard",
     *         "admin",
     *         "ACL",
     *     },
     *
     *     @OA\Parameter(
     *         name="id",
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
    'GET /api/admin/group/:uuid' => static function (string $uuid, HttpRestRequest $request): ResponseInterface {
//        RestConfig::request_authorization_check($request, 'admin', 'groups');

        return (new AclGroupRestController(new AclGroupService()))->getOne($request, $uuid);
    },

    /**
     * Schema for the group request
     *
     * @OA\Schema(
     *     schema="api_group_request",
     *
     *     @OA\Property(
     *         property="parent_id",
     *         description="Parent Group ID. Fallbacks to Root Group ID if not provided.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         description="Group Name.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="value",
     *         description="Group Value.",
     *         type="string"
     *     ),
     *     required={"name", "value"},
     *     example={
     *         "parent_id": 10,
     *         "name": "Testers",
     *         "value": "testers",
     *     }
     *  )
     */

    /**
     * @OA\Post(
     *     path="/api/admin/group",
     *     description="Creates a new group",
     *     tags={
     *         "standard",
     *         "admin",
     *         "ACL",
     *     },
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/api_group_request")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Standard response",
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="validationErrors",
     *                     description="Validation errors.",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *                     ),
     *                 ),
     *
     *                 @OA\Property(
     *                     property="internalErrors",
     *                     description="Internal errors.",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *                     ),
     *                 ),
     *
     *                 @OA\Property(
     *                     property="data",
     *                     description="Returned data.",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         @OA\Property(
     *                             property="parent_id",
     *                             description="Parent Group ID",
     *                             type="integer",
     *                         ),
     *                         @OA\Property(
     *                             property="name",
     *                             description="Group Name",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="value",
     *                             description="Group Value",
     *                             type="string",
     *                         )
     *                     ),
     *                 ),
     *                 example={
     *                     "validationErrors": {},
     *                     "internalErrors": {},
     *                     "data": {
     *                         "parent_id": 10,
     *                         "name": "Testers",
     *                         "value": "testers"
     *                     }
     *                 }
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="401",
     *         ref="#/components/responses/unauthorized"
     *     ),
     *     security={{"openemr_auth":{}}}
     *  )
     */
    'POST /api/admin/group' => static function (HttpRestRequest $request) {
//        RestConfig::request_authorization_check($request, 'admin', 'groups');
        $data = (array) (json_decode(file_get_contents('php://input')));

        return (new AclGroupRestController())->post($data, $request);
    },

    /**
     * @OA\Delete(
     *     path="/api/admin/group/{id}",
     *     description="Delete a Group by ID",
     *     tags={
     *         "standard",
     *         "admin",
     *         "ACL",
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Group ID.",
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
    "DELETE /api/admin/group/:uuid" => static function (string $id, HttpRestRequest $request) {
        // RestConfig::request_authorization_check($request, "admin", "groups");
        return (new AclGroupRestController())->delete($request, $id);
    },
];
