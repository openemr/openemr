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
use OpenEMR\RestControllers\Standard\Admin\Acl\AdminAclSectionRestController;
use OpenEMR\RestControllers\Config\RestConfig;
use Psr\Http\Message\ResponseInterface;

/**
 * @OA\Response(
 *     response="api_admin_acl_section_response",
 *     description="Sections List Response",
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
 *                         property="parent_section",
 *                         description="Parent Section ID.",
 *                         type="integer",
 *                     ),
 *                     @OA\Property(
 *                         property="section_id",
 *                         description="Section ID.",
 *                         type="integer",
 *                     ),
 *                     @OA\Property(
 *                         property="section_identifier",
 *                         description="Section identifier (slug).",
 *                         type="string",
 *                     ),
 *                     @OA\Property(
 *                         property="section_name",
 *                         description="Section name.",
 *                         type="string",
 *                     ),
 *                     @OA\Property(
 *                         property="module_id",
 *                         description="Module ID.",
 *                         type="integer",
 *                     ),
 *                 ),
 *             ),
 *             example={
 *                 "validationErrors": {},
 *                 "internalErrors": {},
 *                 "data": {
 *                     "parent_section": 0,
 *                     "section_id": 1,
 *                     "section_identifier": "immunization",
 *                     "section_name": "Immunization",
 *                     "module_id": 0
 *                 }
 *             }
 *         )
 *     )
 * )
 */
return [
    /**
     * @OA\Get(
     *     path="/api/admin/acl/section",
     *     description="Retrieves a list of ACL Sections",
     *     tags={
     *         "standard",
     *         "admin",
     *         "acl",
     *         "section",
     *     },
     *
     *     @OA\Response(
     *         response="200",
     *         ref="#/components/responses/api_admin_acl_section_response"
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
     *
     */
    'GET /api/admin/acl/section' => static function (HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');

        return (new AdminAclSectionRestController())->getAll($request);
    },
];
