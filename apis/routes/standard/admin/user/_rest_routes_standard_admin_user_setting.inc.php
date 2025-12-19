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
use Psr\Http\Message\ResponseInterface;
use OpenEMR\RestControllers\Standard\Admin\User\Setting\AdminUserSettingSectionRestController;
use OpenEMR\RestControllers\Standard\Admin\User\Setting\AdminUserSettingRestController;
use OpenEMR\RestControllers\Config\RestConfig;

require_once(__DIR__ . "/../../../../../library/globals.inc.php"); // As we need section names

return [
    /**
     * @OA\Get(
     *     path="/api/admin/user/setting/section",
     *     description="Retrieves a list of User-specific Setting Sections",
     *     tags={
     *         "standard",
     *         "admin",
     *         "user",
     *         "setting",
     *         "setting-section",
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
    'GET /api/admin/user/setting/section' => static function(HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return AdminUserSettingSectionRestController::getInstance()->getUserSpecificSections($request);
    },

    /**
     * @OA\Get(
     *     path="/api/admin/user/{userId}/setting",
     *     description="Retrieves a list of all Given User's Settings",
     *     tags={
     *         "standard",
     *         "user",
     *         "setting",
     *     },
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
    'GET /api/admin/user/:userId/setting' => static function (string $userId, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return AdminUserSettingRestController::getInstance()->getAll($request, $userId);
    },

    /**
     * @OA\Get(
     *     path="/api/admin/user/{userId}/setting/{section}",
     *     description="Retrieves a list of Given User's Settings by Section",
     *     tags={
     *         "standard",
     *         "admin",
     *         "acl",
     *         "user",
     *         "setting",
     *     },
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
    'GET /api/admin/user/:userId/setting/:section' => static function(string $userId, string $section, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return AdminUserSettingRestController::getInstance()->getBySectionSlug($request, $userId, $section);
    },

    /**
     * @OA\Get(
     *     path="/api/admin/user/{userId}/setting/{section}/{key}",
     *     description="Returns Given User's Setting Value by Key",
     *     tags={
     *         "standard",
     *         "admin",
     *         "user",
     *         "setting",
     *     },
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
    'GET /api/admin/user/:userId/setting/:section/:key' => static function(string $userId, string $section, string $key, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return AdminUserSettingRestController::getInstance()->getOneBySettingKey($request, $userId, $section, $key);
    },

    /**
     * @OA\Put(
     *     path="/api/admin/user/{userId}/setting/{section}",
     *     description="Set Given User Section's Settings to given values",
     *     tags={
     *         "standard",
     *         "admin",
     *         "user",
     *         "setting",
     *     },
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
    'PUT /api/admin/user/:userId/setting/:section' => static function(string $userId, string $section, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');

        return AdminUserSettingRestController::getInstance()->putBySectionSlug(
            $request,
            $userId,
            $section,
            file_get_contents('php://input'),
        );
    },

    /**
     * @OA\Post(
     *     path="/api/admin/user/{userId}/setting/{section}/reset",
     *     description="Resets All Given User's Settings to Defaults",
     *     tags={
     *         "standard",
     *         "admin",
     *         "user",
     *         "setting",
     *     },
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
    'POST /api/admin/user/:userId/setting/:section/reset' => static function(string $userId, string $section, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return AdminUserSettingRestController::getInstance()->resetBySectionSlug($request, $userId, $section);
    },

    /**
     * @OA\Post(
     *     path="/api/admin/user/{userId}/setting/{section}/{key}/reset",
     *     description="Resets Given User's Setting to Default Value by Setting Key",
     *     tags={
     *         "standard",
     *         "admin",
     *         "user",
     *         "setting",
     *     },
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
    'POST /api/admin/user/:userId/setting/:section/:key/reset' => static function(string $userId, string $section, string $key, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        return AdminUserSettingRestController::getInstance()->resetOneBySettingKey($request, $userId, $section, $key);
    },
];
