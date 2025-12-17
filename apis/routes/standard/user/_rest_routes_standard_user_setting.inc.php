<?php

/**
 * Standard Admin ACL User Setting API Routes
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
use OpenEMR\RestControllers\Standard\User\Setting\UserSettingRestController;
use OpenEMR\RestControllers\Standard\User\Setting\UserSettingSectionRestController;
use Psr\Http\Message\ResponseInterface;
use OpenEMR\RestControllers\Config\RestConfig;

// @todo Move initialization to classes
require_once(__DIR__ . "/../../../../library/globals.inc.php"); // As we need section names

return [
    /**
     * @OA\Get(
     *     path="/api/user/setting/section",
     *     description="Retrieves a list of all Authorized User's Setting Sections",
     *     tags={
     *         "standard",
     *         "user",
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
    'GET /api/user/setting/section' => static function(HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return (new UserSettingSectionRestController())->getUserSpecificSections($request);
    },

    /**
     * @OA\Get(
     *     path="/api/user/setting",
     *     description="Retrieves a list of all Authorized User's Settings",
     *     tags={
     *         "standard",
     *         "user",
     *         "setting",
     *     },
     *
     *     @OA\Response(
     *         response="200",
     *         ref="#/components/responses/api_standard_setting_response"
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
    'GET /api/user/setting' => static function(HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return (new UserSettingRestController())->getAll(
            $request,
            $request->getSession()->get("authUserID"),
        );
    },

    /**
     * @OA\Get(
     *     path="/api/user/setting/{section}",
     *     description="Retrieves a list of Authorized User's Settings by Section",
     *     tags={
     *         "standard",
     *         "admin",
     *         "acl",
     *         "user",
     *         "setting",
     *     },
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
     *     @OA\Response(
     *         response="200",
     *         ref="#/components/responses/api_standard_setting_response"
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
    'GET /api/user/setting/:section' => static function(string $section, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return (new UserSettingRestController())->getBySectionSlug(
            $request,
            $request->getSession()->get("authUserID"),
            $section,
        );
    },

    /**
     * @OA\Get(
     *     path="/api/admin/global-setting/{section}/{key}",
     *     description="Returns Authorized User's Setting Value by Key",
     *     tags={
     *         "standard",
     *         "admin",
     *         "user",
     *         "setting",
     *     },
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
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         description="Setting Key.",
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
    'GET /api/user/setting/:section/:key' => static function(string $section, string $key, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return (new UserSettingRestController())->getOneBySettingKey(
            $request,
            $request->getSession()->get("authUserID"),
            $section,
            $key,
        );
    },

    /**
     * @OA\Put(
     *     path="/api/user/setting/{section}",
     *     description="Set Authorized User Section's Settings to given values",
     *     tags={
     *         "standard",
     *         "user",
     *         "setting",
     *     },
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
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/api_standard_setting_put_request")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         ref="#/components/responses/api_standard_setting_response"
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
    'PUT /api/user/setting/:section' => static function(string $section, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');

        return (new UserSettingRestController())->putBySectionSlug(
            $request,
            $request->getSession()->get("authUserID"),
            $section,
            file_get_contents('php://input'),
        );
    },

    /**
     * @OA\Post(
     *     path="/api/user/setting/{section}/reset",
     *     description="Resets All Authorized User's Settings to Defaults",
     *     tags={
     *         "standard",
     *         "user",
     *         "setting",
     *     },
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
     *     @OA\Response(
     *         response="200",
     *         ref="#/components/responses/api_standard_setting_response"
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
    'POST /api/user/setting/:section/reset' => static function(string $section, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return (new UserSettingRestController())->resetBySectionSlug(
            $request,
            $request->getSession()->get("authUserID"),
            $section,
        );
    },

    /**
     * @OA\Post(
     *     path="/api/user/setting/{section}/{key}/reset",
     *     description="Resets Authorized User's Setting to Default Value by Setting Key",
     *     tags={
     *         "standard",
     *         "user",
     *         "setting",
     *     },
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
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         description="Setting Key.",
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
    'POST /api/user/setting/:section/:key/reset' => static function(string $section, string $key, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return (new UserSettingRestController())->resetOneBySettingKey(
            $request,
            $request->getSession()->get("authUserID"),
            $section,
            $key,
        );
    },
];
