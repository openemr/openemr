<?php

declare(strict_types=1);

/**
 * Standard User API Routes
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
use OpenEMR\RestControllers\UserRestController;
use OpenEMR\Services\UserService;
use Psr\Http\Message\ResponseInterface;

return [
    /**
     *  @OA\Get(
     *      path="/api/user",
     *      description="Retrieves a list of users",
     *      tags={
     *          "standard",
     *          "user",
     *      },
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="The id for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="title",
     *          in="query",
     *          description="The title for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="fname",
     *          in="query",
     *          description="The first name for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="lname",
     *          in="query",
     *          description="The last name for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="mname",
     *          in="query",
     *          description="The middle name for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="federaltaxid",
     *          in="query",
     *          description="The federal tax id for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="federaldrugid",
     *          in="query",
     *          description="The federal drug id for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="upin",
     *          in="query",
     *          description="The upin for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="facility_id",
     *          in="query",
     *          description="The facility id for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="facility",
     *          in="query",
     *          description="The facility for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="npi",
     *          in="query",
     *          description="The npi for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="The email for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="specialty",
     *          in="query",
     *          description="The specialty for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="billname",
     *          in="query",
     *          description="The billname for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="url",
     *          in="query",
     *          description="The url for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="assistant",
     *          in="query",
     *          description="The assistant for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="organization",
     *          in="query",
     *          description="The organization for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="valedictory",
     *          in="query",
     *          description="The valedictory for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="street",
     *          in="query",
     *          description="The street for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="streetb",
     *          in="query",
     *          description="The street (line 2) for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="city",
     *          in="query",
     *          description="The city for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="state",
     *          in="query",
     *          description="The state for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="zip",
     *          in="query",
     *          description="The zip for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="phone",
     *          in="query",
     *          description="The phone for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="fax",
     *          in="query",
     *          description="The fax for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="phonew1",
     *          in="query",
     *          description="The phonew1 for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *         name="phonecell",
     *          in="query",
     *          description="The phonecell for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="notes",
     *          in="query",
     *          description="The notes for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="state_license_number2",
     *          in="query",
     *          description="The state license number for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="username",
     *          in="query",
     *          description="The username for the user.",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          ref="#/components/responses/badrequest"
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    'GET /api/user' => static function (HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');

        return (new UserRestController(new UserService()))->getAll($request, $_GET);
    },

    /**
     *  @OA\Get(
     *      path="/api/user/{uuid}",
     *      description="Retrieves a single user by their uuid",
     *      tags={
     *          "standard",
     *          "user",
     *      },
     *
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="The uuid for the user.",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response="200",
     *          ref="#/components/responses/standard"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          ref="#/components/responses/badrequest"
     *      ),
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    'GET /api/user/:uuid' => static function (string $uuid, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'admin', 'users');

        return (new UserRestController(new UserService()))->getOne($request, $uuid);
    },

    /**
     * Schema for the user request
     *
     *  @OA\Schema(
     *      schema="api_user_request",
     *
     *      @OA\Property(
     *          property="title",
     *          description="The title for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="fname",
     *          description="The first name for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="lname",
     *          description="The last name for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="mname",
     *          description="The middle name for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="federaltaxid",
     *          description="The federal tax id for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="federaldrugid",
     *          description="The federal drug id for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="upin",
     *          description="The upin for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="facility_id",
     *          description="The facility id for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="facility",
     *          description="The facility for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="npi",
     *          description="The npi for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="email",
     *          description="The email for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="specialty",
     *          description="The specialty for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="billname",
     *          description="The billname for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="url",
     *          description="The url for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="assistant",
     *          description="The assistant for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="organization",
     *          description="The organization for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="valedictory",
     *          description="The valedictory for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="street",
     *          description="The street for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="streetb",
     *          description="The street (line 2) for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="city",
     *          description="The city for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="state",
     *          description="The state for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="zip",
     *          description="The zip for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="phone",
     *          description="The phone for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="fax",
     *          description="The fax for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="phonew1",
     *          description="The phonew1 for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *         property="phonecell",
     *          description="The phonecell for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="notes",
     *          description="The notes for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="state_license_number2",
     *          description="The state license number for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="username",
     *          description="The username for the user.",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="password",
     *          description="The password for the user.",
     *          type="string"
     *      ),
     *      required={"fname", "lname", "username"},
     *      example={
     *          "title": "Mr",
     *          "fname": "Foo",
     *          "mname": "",
     *          "lname": "Bar",
     *          "username": "foobar",
     *          "password": "foobarpassword",
     *      }
     *  )
     */

    /**
     *  @OA\Post(
     *      path="/api/user",
     *      description="Creates a new user",
     *      tags={"standard"},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\MediaType(
     *              mediaType="application/json",
     *
     *              @OA\Schema(ref="#/components/schemas/api_user_request")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Standard response",
     *
     *          @OA\MediaType(
     *              mediaType="application/json",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(
     *                      property="validationErrors",
     *                      description="Validation errors.",
     *                      type="array",
     *
     *                      @OA\Items(
     *                          type="object",
     *                      ),
     *                  ),
     *
     *                  @OA\Property(
     *                      property="internalErrors",
     *                      description="Internal errors.",
     *                      type="array",
     *
     *                      @OA\Items(
     *                          type="object",
     *                      ),
     *                  ),
     *
     *                  @OA\Property(
     *                      property="data",
     *                      description="Returned data.",
     *                      type="array",
     *
     *                      @OA\Items(
     *
     *                          @OA\Property(
     *                              property="id",
     *                              description="User id",
     *                              type="integer",
     *                          ),
     *                          @OA\Property(
     *                              property="uuid",
     *                              description="UUID",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="password",
     *                              description="Password (autogenerated when not passed)",
     *                              type="string",
     *                          )
     *                      ),
     *                  ),
     *                  example={
     *                      "validationErrors": {},
     *                      "internalErrors": {},
     *                      "data": {
     *                          "id": 1
     *                          "uuid": "A00D22861FA74F8793AF450D6552932C",
     *                          "password": "55006c9b20ba100a"
     *                      }
     *                  }
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response="401",
     *          ref="#/components/responses/unauthorized"
     *      ),
     *      security={{"openemr_auth":{}}}
     *  )
     */
    'POST /api/user' => function (HttpRestRequest $request) {
        RestConfig::request_authorization_check($request, 'admin', 'users');
        $data = (array) (json_decode(file_get_contents('php://input')));

        return (new UserRestController(new UserService()))->post($data, $request);
    },
];
