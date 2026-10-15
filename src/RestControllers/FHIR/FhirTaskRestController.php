<?php

/**
 * REST routes for the FHIR Task resource: read and search, through the
 * generic FHIR controller, with their API documentation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FHIR\FhirTaskService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FhirTaskRestController
{
    public function __construct(private readonly HttpRestRequest $request, private readonly OEGlobalsBag $globalsBag)
    {
    }

    private function controller(): FhirGenericRestController
    {
        $controller = new FhirGenericRestController($this->request, new FhirTaskService(), $this->globalsBag);
        $controller->addAclRestrictions('patients', 'med');
        return $controller;
    }

    #[OA\Get(
        path: '/fhir/Task/{uuid}',
        description: 'Returns a single Task resource.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the Task resource.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Standard Response',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'json object',
                                description: 'FHIR Json object.',
                                type: 'object'
                            ),
                        ],
                        example: [
                            'id' => '9f1c2a5e-3b7d-4c8e-a1f0-2d6b8e4c7a19',
                            'meta' => [
                                'versionId' => '1',
                                'lastUpdated' => '2026-10-15T14:00:00+00:00',
                            ],
                            'resourceType' => 'Task',
                            'status' => 'requested',
                            'intent' => 'order',
                            'code' => [
                                'coding' => [
                                    [
                                        'system' => 'http://hl7.org/fhir/CodeSystem/task-code',
                                        'code' => 'fulfill',
                                        'display' => 'fulfill',
                                    ],
                                ],
                            ],
                            'focus' => [
                                'reference' => 'ServiceRequest/95e9d3fb-fe7b-448a-aa60-d40b11b486a5',
                                'type' => 'ServiceRequest',
                            ],
                            'for' => [
                                'reference' => 'Patient/95e8d830-3068-48cf-930a-2fefb18c2bcf',
                                'type' => 'Patient',
                            ],
                            'authoredOn' => '2026-10-15T14:00:00+00:00',
                            'lastModified' => '2026-10-15T14:00:00+00:00',
                            'requester' => [
                                'reference' => 'Practitioner/9b6a5d3f-2c1e-4f8a-b7d0-1e3c5a7f9b2d',
                                'type' => 'Practitioner',
                            ],
                            'owner' => [
                                'reference' => 'Organization/95f0a1c2-7d3e-4b5f-8a9c-0e1d2f3a4b5c',
                                'type' => 'Organization',
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
            new OA\Response(response: '404', ref: '#/components/responses/uuidnotfound'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne(string $uuid): Response
    {
        return $this->controller()->getOne($uuid);
    }

    #[OA\Get(
        path: '/fhir/Task',
        description: 'Returns a list of Task resources.',
        tags: ['fhir'],
        parameters: [
            new OA\Parameter(
                name: '_id',
                in: 'query',
                description: 'The uuid for the Task resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: '_lastUpdated',
                in: 'query',
                description: 'Allows filtering resources by the _lastUpdated field. A FHIR Instant value in the format YYYY-MM-DDThh:mm:ss.sss+zz:zz.  See FHIR date/time modifiers for filtering options (ge,gt,le, etc)',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'patient',
                in: 'query',
                description: 'The uuid for the patient the task is for.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'The status of the Task resource.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'code',
                in: 'query',
                description: 'The code of the Task resource, from http://hl7.org/fhir/CodeSystem/task-code.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'focus',
                in: 'query',
                description: 'The resource the task acts on, such as ServiceRequest/{uuid}.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'owner',
                in: 'query',
                description: 'Who is responsible for the task, such as Organization/{uuid}.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'requester',
                in: 'query',
                description: 'Who asked for the task, such as Practitioner/{uuid}.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'part-of',
                in: 'query',
                description: 'The parent task, such as Task/{uuid}.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'authored-on',
                in: 'query',
                description: 'When the task was created.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Standard Response',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'json object',
                                description: 'FHIR Json object.',
                                type: 'object'
                            ),
                        ],
                        example: [
                            'meta' => [
                                'lastUpdated' => '2026-10-15T14:00:00',
                            ],
                            'resourceType' => 'Bundle',
                            'type' => 'collection',
                            'total' => 0,
                            'link' => [
                                [
                                    'relation' => 'self',
                                    'url' => 'https://localhost:9300/apis/default/fhir/Task',
                                ],
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAll(): JsonResponse|Response
    {
        return $this->controller()->getAll();
    }
}
