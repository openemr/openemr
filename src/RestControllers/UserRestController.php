<?php

/**
 * UserRestController - REST API for user related operations
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\PractitionerService;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;

class UserRestController
{
    /**
     * @var UserService $userService
     */
    private $userService;

    /**
     * White list of practitioner search fields
     */
    private const WHITELISTED_FIELDS = [
        "id",
        "title",
        "fname",
        "lname",
        "mname",
        "federaltaxid",
        "federaldrugid",
        "upin",
        "facility_id",
        "facility",
        "npi",
        "email",
        "specialty",
        "billname",
        "url",
        "assistant",
        "organization",
        "valedictory",
        "street",
        "streetb",
        "city",
        "state",
        "zip",
        "phone",
        "fax",
        "phonew1",
        "phonecell",
        "notes",
        "state_license_number",
        "username"
    ];

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Fetches a single user resource by id.
     * @param $uuid- The user uuid identifier in string format.
     */
    #[OA\Get(
        path: '/api/user/{uuid}',
        description: 'Retrieves a single user by their uuid',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the user.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne($uuid)
    {
        $processingResult = new ProcessingResult();
        $user = $this->userService->getUserByUUID($uuid);
        if (!empty($user)) {
            $processingResult->setData([$user]);
        }

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns user resources which match an optional search criteria.
     */
    #[OA\Get(
        path: '/api/user',
        description: 'Retrieves a list of users',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'query',
                description: 'The id for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'title',
                in: 'query',
                description: 'The title for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'fname',
                in: 'query',
                description: 'The first name for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'lname',
                in: 'query',
                description: 'The last name for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'mname',
                in: 'query',
                description: 'The middle name for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'federaltaxid',
                in: 'query',
                description: 'The federal tax id for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'federaldrugid',
                in: 'query',
                description: 'The federal drug id for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'upin',
                in: 'query',
                description: 'The upin for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'facility_id',
                in: 'query',
                description: 'The facility id for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'facility',
                in: 'query',
                description: 'The facility for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'npi',
                in: 'query',
                description: 'The npi for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'email',
                in: 'query',
                description: 'The email for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'specialty',
                in: 'query',
                description: 'The specialty for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'billname',
                in: 'query',
                description: 'The billname for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'url',
                in: 'query',
                description: 'The url for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'assistant',
                in: 'query',
                description: 'The assistant for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'organization',
                in: 'query',
                description: 'The organization for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'valedictory',
                in: 'query',
                description: 'The valedictory for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'street',
                in: 'query',
                description: 'The street for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'streetb',
                in: 'query',
                description: 'The street (line 2) for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'city',
                in: 'query',
                description: 'The city for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'state',
                in: 'query',
                description: 'The state for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'zip',
                in: 'query',
                description: 'The zip for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'phone',
                in: 'query',
                description: 'The phone for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'fax',
                in: 'query',
                description: 'The fax for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'phonew1',
                in: 'query',
                description: 'The phonew1 for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'phonecell',
                in: 'query',
                description: 'The phonecell for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'notes',
                in: 'query',
                description: 'The notes for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'state_license_number2',
                in: 'query',
                description: 'The state license number for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'username',
                in: 'query',
                description: 'The username for the user.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAll($search = [])
    {
        $validKeys = array_combine(self::WHITELISTED_FIELDS, self::WHITELISTED_FIELDS);
        $validSearchFields = array_intersect_key($search, $validKeys);
        $result = $this->userService->search($validSearchFields, true);
        return RestControllerHelper::handleProcessingResult($result, 200, true);
    }
}
