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
use OpenEMR\RestControllers\Standard\Admin\Acl\AdminAclGroupSettingRestController;
use OpenEMR\RestControllers\Config\RestConfig;
use Psr\Http\Message\ResponseInterface;

/**
 * @OA\Response(
 *     response="api_admin_acl_group_setting_response",
 *     description="ACL Group Setting List Response",
 *     @OA\MediaType(
 *         mediaType="application/json",
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="validationErrors",
 *                 description="Validation errors.",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                 ),
 *             ),
 *             @OA\Property(
 *                 property="internalErrors",
 *                 description="Internal errors.",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                 ),
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 description="Returned data.",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                 ),
 *
 *                 @OA\Items(
 *                     @OA\Property(
 *                         property="group_id",
 *                         description="Group ID.",
 *                         type="integer",
 *                     ),
 *                     @OA\Property(
 *                         property="section_id",
 *                         description="Section ID.",
 *                         type="integer",
 *                     ),
 *                     @OA\Property(
 *                         property="allowed",
 *                         description="Allowed?",
 *                         type="integer",
 *                     ),
 *                 ),
 *             ),
 *             example={
 *                 "validationErrors": {},
 *                 "internalErrors": {},
 *                 "data": {
 *                     "group_id": 11,
 *                     "section_id": 1,
 *                     "allowed": 1
 *                 }
 *             }
 *         )
 *     )
 * )
 */
return [
    /**
     * @OA\Get(
     *     path="/api/admin/acl/group/setting",
     *     description="Retrieves a list of all ACL Group Settings",
     *     tags={
     *         "standard",
     *         "admin",
     *         "acl",
     *         "group",
     *         "setting",
     *     },
     *
     *     @OA\Response(
     *         response="200",
     *         ref="#/components/responses/api_admin_acl_group_setting_response"
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
    'GET /api/admin/acl/group/setting' => static function (HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');

        return AdminAclGroupSettingRestController::getInstance()->getAll($request);
    },

    /**
     * @OA\Get(
     *     path="/api/admin/acl/group/setting/{sectionId}",
     *     description="Retrieves a list of ACL Group Settings by Section ID",
     *     tags={
     *         "standard",
     *         "admin",
     *         "acl",
     *         "group",
     *         "setting",
     *     },
     *
     *     @OA\Parameter(
     *         name="sectionId",
     *         in="path",
     *         description="Section ID.",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         ref="#/components/responses/api_admin_acl_group_setting_response"
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
    'GET /api/admin/acl/group/setting/:sectionId' => static function (int $sectionId, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');

        return AdminAclGroupSettingRestController::getInstance()->getBySection($request, $sectionId);
    },
];
