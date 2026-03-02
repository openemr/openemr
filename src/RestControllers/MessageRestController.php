<?php

/**
 * MessageRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Services\MessageService;
use OpenEMR\RestControllers\RestControllerHelper;

#[OA\Schema(
    schema: 'api_message_request',
    description: 'Schema for the message request',
    required: ['body', 'groupname', 'from', 'to', 'title', 'message_status'],
    properties: [
        new OA\Property(property: 'body', description: 'The body of message.', type: 'string'),
        new OA\Property(property: 'groupname', description: "The group name (usually is 'Default').", type: 'string'),
        new OA\Property(property: 'from', description: 'The sender of the message.', type: 'string'),
        new OA\Property(property: 'to', description: 'The recipient of the message.', type: 'string'),
        new OA\Property(property: 'title', description: 'use an option from resource=/api/list/note_type', type: 'string'),
        new OA\Property(property: 'message_status', description: 'use an option from resource=/api/list/message_status', type: 'string'),
    ],
    example: [
        'body' => 'Test 456',
        'groupname' => 'Default',
        'from' => 'Matthew',
        'to' => 'admin',
        'title' => 'Other',
        'message_status' => 'New',
    ]
)]
class MessageRestController
{
    private $messageService;

    public function __construct()
    {
        $this->messageService = new MessageService();
    }

    /**
     * Edit a pnote message.
     */
    #[OA\Put(
        path: '/api/patient/{pid}/message/{mid}',
        description: 'Edit a pnote message',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The id for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'mid',
                in: 'path',
                description: 'The id for the pnote message.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/api_message_request')
            )
        ),
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function put($pid, $mid, $data)
    {
        $validationResult = $this->messageService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->messageService->update($pid, $mid, $data);
        return RestControllerHelper::responseHandler($serviceResult, ["mid" => $mid], 200);
    }

    /**
     * Submits a pnote message.
     */
    #[OA\Post(
        path: '/api/patient/{pid}/message',
        description: 'Submits a pnote message',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The id for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/api_message_request')
            )
        ),
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function post($pid, $data)
    {
        $validationResult = $this->messageService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->messageService->insert($pid, $data);
        return RestControllerHelper::responseHandler($serviceResult, ["mid" => $serviceResult], 201);
    }

    /**
     * Delete a pnote message.
     */
    #[OA\Delete(
        path: '/api/patient/{pid}/message/{mid}',
        description: 'Delete a pnote message',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'pid',
                in: 'path',
                description: 'The id for the patient.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'mid',
                in: 'path',
                description: 'The id for the pnote message.',
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
    public function delete($pid, $mid)
    {
        $serviceResult = $this->messageService->delete($pid, $mid);
        return RestControllerHelper::responseHandler($serviceResult, true, 200);
    }
}
