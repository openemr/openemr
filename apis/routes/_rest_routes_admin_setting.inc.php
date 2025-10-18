<?php

/**
 * Standard Admin Setting API Routes
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
use OpenEMR\RestControllers\Config\RestConfig;
use OpenEMR\RestControllers\AdminSettingRestController;
use OpenEMR\Services\UserService;
use Psr\Http\Message\ResponseInterface;

return [
    /**
     * @OA\Get(
     *     path="/api/admin/setting",
     *     description="Retrieves a list of Settings",
     *     tags={
     *         "standard",
     *         "admin",
     *         "setting",
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
    'GET /api/admin/setting' => static function (HttpRestRequest $request): ResponseInterface {
//        RestConfig::request_authorization_check($request, 'admin', 'users');

        return (new AdminSettingRestController())->getAll($request);
    },

    /**
     * @OA\Get(
     *     path="/api/admin/setting/{section}",
     *     description="Retrieves a list of Settings",
     *     tags={
     *         "standard",
     *         "admin",
     *         "setting",
     *     },
     *
     *     @OA\Parameter(
     *         name="section",
     *         in="path",
     *         description="The setting section.",
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
    'GET /api/admin/setting/:section' => static function (HttpRestRequest $request, string $section): ResponseInterface {
//        RestConfig::request_authorization_check($request, 'admin', 'users');

        return (new AdminSettingRestController())->getBySection($request, $section);
    },

    /**
     * Schema for the user request
     *
     * @OA\Schema(
     *     schema="api_setting_section_request",
     *
     *     @OA\Property(
     *         property="title",
     *         description="The title for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="fname",
     *         description="The first name for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="lname",
     *         description="The last name for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="mname",
     *         description="The middle name for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="federaltaxid",
     *         description="The federal tax id for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="federaldrugid",
     *         description="The federal drug id for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="upin",
     *         description="The upin for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="facility_id",
     *         description="The facility id for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="facility",
     *         description="The facility for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="npi",
     *         description="The npi for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         description="The email for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="specialty",
     *         description="The specialty for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="billname",
     *         description="The billname for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="url",
     *         description="The url for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="assistant",
     *         description="The assistant for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="organization",
     *         description="The organization for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="valedictory",
     *         description="The valedictory for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="street",
     *         description="The street for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="streetb",
     *         description="The street (line 2) for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="city",
     *         description="The city for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         description="The state for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="zip",
     *         description="The zip for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="phone",
     *         description="The phone for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="fax",
     *         description="The fax for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="phonew1",
     *         description="The phonew1 for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *        property="phonecell",
     *         description="The phonecell for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="notes",
     *         description="The notes for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="state_license_number2",
     *         description="The state license number for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="username",
     *         description="The username for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="password",
     *         description="The password for the user.",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="acl_group_ids",
     *         description="ACL groups IDs to add user to",
     *         type="string"
     *     ),
     *     required={"fname", "lname", "username"},
     *     example={
     *         "title": "Mr",
     *         "fname": "Foo",
     *         "mname": "",
     *         "lname": "Bar",
     *         "username": "foobar",
     *         "password": "foobarpassword",
     *     }
     *  )
     */

    /**
     * @OA\Put(
     *     path="/api/admin/section/{section}",
     *     description="Update settings",
     *     tags={
     *         "standard",
     *         "admin",
     *         "setting",
     *     },
     *
     *     @OA\Parameter(
     *         name="section",
     *         in="path",
     *         description="Section.",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/api_setting_section_request")
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
    'PUT /api/admin/setting/:section' => static function (string $section, HttpRestRequest $request): ResponseInterface {
//        RestConfig::request_authorization_check($request, 'admin', 'users');

        $data = (array) (json_decode(file_get_contents('php://input')));

        return (new AdminSettingRestController())->put($request, $section, $data);
    },

    /**
     * @OA\Patch(
     *     path="/api/admin/setting/{section}/{key}",
     *     description="Creates a new user",
     *     tags={
     *         "standard",
     *         "admin",
     *         "setting",
     *     },
     *
     *     @OA\Parameter(
     *         name="section",
     *         in="path",
     *         description="Section.",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         description="Section key.",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/api_setting_section_request")
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
     *
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
     *
     *                         @OA\Property(
     *                             property="id",
     *                             description="User id",
     *                             type="integer",
     *                         ),
     *                         @OA\Property(
     *                             property="uuid",
     *                             description="UUID",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="password",
     *                             description="Password (autogenerated when not passed)",
     *                             type="string",
     *                         )
     *                     ),
     *                 ),
     *                 example={
     *                     "validationErrors": {},
     *                     "internalErrors": {},
     *                     "data": {
     *                         "id": 1,
     *                         "uuid": "A00D22861FA74F8793AF450D6552932C",
     *                         "password": "55006c9b20ba100a"
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
    'PATCH /api/admin/user' => static function (HttpRestRequest $request) {
//        RestConfig::request_authorization_check($request, 'admin', 'users');
        $data = (array) (json_decode(file_get_contents('php://input')));

        return (new AdminSettingRestController())->patch($data, $request);
    },
];
